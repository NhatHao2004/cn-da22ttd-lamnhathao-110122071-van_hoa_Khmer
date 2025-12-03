<?php
/**
 * Base Model - Lớp Model cơ sở
 * Chứa các phương thức chung cho tất cả Models
 * 
 * @author Lâm Nhật Hào
 * @version 2.0
 */

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['mat_khau', 'password'];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả bản ghi
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? ($this->timestamps ? 'ngay_tao' : $this->primaryKey);
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$orderDir} LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy một bản ghi theo ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $result = $this->db->querySingle($sql, [$id]);
        return $result ? $this->hideFields($result) : null;
    }
    
    /**
     * Tạo bản ghi mới
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['ngay_tao'] = date('Y-m-d H:i:s');
        }
        
        // Validate
        $this->validate($data);
        
        // Prepare SQL
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $fields) . "`) 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        if ($this->db->execute($sql, array_values($data))) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật bản ghi
     */
    public function update($id, $data) {
        // Debug data trước khi filter
        if ($this->table === 'le_hoi') {
            error_log("=== LEHOI UPDATE DEBUG ===");
            error_log("ID: " . $id);
            error_log("Data before filter: " . print_r($data, true));
            error_log("Fillable: " . print_r($this->fillable, true));
        }
        
        $data = $this->filterFillable($data);
        
        // Debug data sau khi filter
        if ($this->table === 'le_hoi') {
            error_log("Data after filter: " . print_r($data, true));
        }
        
        if ($this->timestamps) {
            $data['ngay_cap_nhat'] = date('Y-m-d H:i:s');
        }
        
        // Validate
        $this->validate($data, $id);
        
        // Prepare SQL
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        
        if (empty($fields)) {
            if ($this->table === 'le_hoi') {
                error_log("ERROR: No fields to update!");
                error_log("=== END LEHOI DEBUG ===");
            }
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $fields) . " WHERE `{$this->primaryKey}` = ?";
        
        if ($this->table === 'le_hoi') {
            error_log("SQL: " . $sql);
            error_log("Values: " . print_r($values, true));
        }
        
        $result = $this->db->execute($sql, $values);
        
        if ($this->table === 'le_hoi') {
            error_log("Result: " . var_export($result, true));
            error_log("=== END LEHOI DEBUG ===");
        }
        
        return $result;
    }
    
    /**
     * Xóa bản ghi
     */
    public function delete($id) {
        // Kiểm tra có soft delete không
        if (in_array('deleted_at', $this->fillable)) {
            return $this->softDelete($id);
        }
        
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Soft Delete
     */
    public function softDelete($id) {
        $sql = "UPDATE `{$this->table}` SET `deleted_at` = NOW() WHERE `{$this->primaryKey}` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Khôi phục bản ghi đã xóa mềm
     */
    public function restore($id) {
        $sql = "UPDATE `{$this->table}` SET `deleted_at` = NULL WHERE `{$this->primaryKey}` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm số lượng bản ghi
     */
    public function count($where = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Tìm kiếm
     */
    public function search($keyword, $fields = [], $limit = 50) {
        if (empty($fields)) {
            return [];
        }
        
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "`{$field}` LIKE ?";
            $params[] = "%{$keyword}%";
        }
        
        $params[] = $limit;
        
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE " . implode(' OR ', $conditions) . " 
                LIMIT ?";
        
        return $this->db->query($sql, $params) ?: [];
    }
    
    /**
     * Lọc chỉ các trường fillable
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Ẩn các trường nhạy cảm
     */
    protected function hideFields($data) {
        if (is_array($data)) {
            foreach ($this->hidden as $field) {
                unset($data[$field]);
            }
        }
        return $data;
    }
    
    /**
     * Tạo slug từ chuỗi tiếng Việt
     */
    protected function generateSlug($text, $unique = true) {
        $text = mb_strtolower($text, 'UTF-8');
        
        // Bảng chuyển đổi
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ', 'đ'
        ];
        
        $latin = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y', 'd'
        ];
        
        $slug = str_replace($vietnamese, $latin, $text);
        $slug = preg_replace('/[^a-z0-9\-\s]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Đảm bảo unique
        if ($unique && $this->slugExists($slug)) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
    
    /**
     * Kiểm tra slug đã tồn tại
     */
    protected function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND `{$this->primaryKey}` != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Bắt đầu transaction
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * Validate dữ liệu - Override trong class con
     */
    protected function validate($data, $id = null) {
        // Override trong class con
        return true;
    }
    
    /**
     * Log hoạt động
     */
    protected function logActivity($action, $objectId = null, $description = '') {
        if (!isset($_SESSION['admin_id'])) {
            return;
        }
        
        $sql = "INSERT INTO `nhat_ky_hoat_dong` 
                (`ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `loai_doi_tuong`, `ma_doi_tuong`, `mo_ta`, `ip_address`, `user_agent`) 
                VALUES (?, 'quan_tri', ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_SESSION['admin_id'],
            $action,
            $this->table,
            $objectId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $this->db->execute($sql, $params);
    }
}
