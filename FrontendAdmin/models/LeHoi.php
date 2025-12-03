<?php
/**
 * Model Lễ Hội
 * Quản lý lễ hội Khmer
 * 
 * @author Lâm Nhật Hào
 * @version 1.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseModel.php';

class LeHoi extends BaseModel {
    protected $table = 'le_hoi';
    protected $primaryKey = 'ma_le_hoi';
    protected $timestamps = true; // Bảng có ngay_tao và ngay_cap_nhat
    
    protected $fillable = [
        'ten_le_hoi', 'ten_le_hoi_khmer', 'slug', 'mo_ta', 
        'ngay_bat_dau', 'ngay_ket_thuc', 'ngay_dien_ra',
        'dia_diem', 'anh_dai_dien', 'thu_vien_anh',
        'y_nghia', 'nguon_goc', 'trang_thai',
        'ma_nguoi_tao'
    ];
    
    // Trạng thái
    const STATUS_VISIBLE = 'hien_thi';
    const STATUS_HIDDEN = 'an';
    
    /**
     * Validate dữ liệu
     */
    protected function validate($data, $id = null) {
        $errors = [];
        
        if (isset($data['ten_le_hoi'])) {
            if (empty($data['ten_le_hoi'])) {
                $errors[] = 'Tên lễ hội không được để trống';
            } elseif (mb_strlen($data['ten_le_hoi']) < 3) {
                $errors[] = 'Tên lễ hội phải có ít nhất 3 ký tự';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Tạo lễ hội mới
     */
    public function create($data) {
        // Tự động tạo slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['ten_le_hoi']);
        }
        
        // Set người tạo
        $data['ma_nguoi_tao'] = $_SESSION['admin_id'] ?? null;
        
        $id = parent::create($data);
        
        if ($id) {
            $this->logActivity('create', $id, "Tạo lễ hội: {$data['ten_le_hoi']}");
        }
        
        return $id;
    }
    
    /**
     * Cập nhật lễ hội
     */
    public function update($id, $data) {
        // Cập nhật slug nếu tên thay đổi
        if (isset($data['ten_le_hoi']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['ten_le_hoi']);
        }
        
        $result = parent::update($id, $data);
        
        if ($result) {
            $this->logActivity('update', $id, "Cập nhật lễ hội ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Lấy tất cả với filter
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? 'ngay_tao';
        
        $sql = "SELECT l.*, q.ho_ten as nguoi_tao
                FROM `{$this->table}` l
                LEFT JOIN `quan_tri_vien` q ON l.ma_nguoi_tao = q.ma_qtv
                ORDER BY l.{$orderBy} {$orderDir}
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy lễ hội sắp diễn ra
     */
    public function getUpcoming($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `ngay_bat_dau` >= CURDATE() 
                AND `trang_thai` = ? 
                ORDER BY `ngay_bat_dau` ASC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_VISIBLE, $limit]) ?: [];
    }
    
    /**
     * Lấy lễ hội đang diễn ra
     */
    public function getOngoing($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `ngay_bat_dau` <= CURDATE() 
                AND `ngay_ket_thuc` >= CURDATE() 
                AND `trang_thai` = ? 
                ORDER BY `ngay_bat_dau` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_VISIBLE, $limit]) ?: [];
    }
    
    /**
     * Tăng lượt xem
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `luot_xem` = `luot_xem` + 1 WHERE `{$this->primaryKey}` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm theo trạng thái
     */
    public function countByStatus($status) {
        return $this->count("`trang_thai` = ?", [$status]);
    }
}
