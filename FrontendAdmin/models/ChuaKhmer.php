<?php
/**
 * Model Chùa Khmer
 * Quản lý thông tin chùa Khmer Nam Bộ
 * 
 * @author Lâm Nhật Hào
 * @version 2.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseModel.php';

class ChuaKhmer extends BaseModel {
    protected $table = 'chua_khmer';
    protected $primaryKey = 'ma_chua';
    
    protected $fillable = [
        'ten_chua', 'ten_tieng_khmer', 'slug', 'dia_chi', 'tinh_thanh', 'quan_huyen',
        'loai_chua', 'so_dien_thoai', 'email', 'website',
        'mo_ta_ngan', 'lich_su', 'hinh_anh_chinh', 'thu_vien_anh',
        'nam_thanh_lap', 'so_nha_su', 'trang_thai', 'ma_nguoi_tao',
        'kinh_do', 'vi_do'
    ];
    
    // Loại chùa
    const TYPE_THERAVADA = 'Theravada';
    const TYPE_MAHAYANA = 'Mahayana';
    const TYPE_VAJRAYANA = 'Vajrayana';
    
    // Trạng thái
    const STATUS_ACTIVE = 'hoat_dong';
    const STATUS_INACTIVE = 'ngung_hoat_dong';
    const STATUS_UNDER_CONSTRUCTION = 'dang_xay_dung';
    
    /**
     * Validate dữ liệu
     */
    protected function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['ten_chua'])) {
            $errors[] = 'Tên chùa không được để trống';
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        
        if (!empty($data['so_dien_thoai']) && !preg_match('/^[0-9]{10,11}$/', $data['so_dien_thoai'])) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Tạo chùa mới
     */
    public function create($data) {
        // Tự động tạo slug unique
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['ten_chua'], true);
        }
        
        // Set người tạo
        $data['ma_nguoi_tao'] = $_SESSION['admin_id'] ?? null;
        
        // Encode thư viện ảnh nếu là array
        if (isset($data['thu_vien_anh']) && is_array($data['thu_vien_anh'])) {
            $data['thu_vien_anh'] = json_encode($data['thu_vien_anh']);
        }
        
        $id = parent::create($data);
        
        if ($id) {
            $this->logActivity('create', $id, "Tạo chùa: {$data['ten_chua']}");
        }
        
        return $id;
    }
    
    /**
     * Cập nhật chùa
     */
    public function update($id, $data) {
        // Cập nhật slug nếu tên thay đổi
        if (isset($data['ten_chua']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['ten_chua'], true);
        }
        
        // Encode thư viện ảnh nếu là array
        if (isset($data['thu_vien_anh']) && is_array($data['thu_vien_anh'])) {
            $data['thu_vien_anh'] = json_encode($data['thu_vien_anh']);
        }
        
        $result = parent::update($id, $data);
        
        if ($result) {
            $this->logActivity('update', $id, "Cập nhật chùa ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Lấy tất cả (override BaseModel)
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? 'ngay_tao';
        
        $sql = "SELECT * FROM `{$this->table}` 
                ORDER BY `{$orderBy}` {$orderDir} 
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy tất cả với filter nâng cao
     */
    public function getAllWithFilters($limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];
        
        // Filter theo tỉnh thành
        if (!empty($filters['tinh_thanh'])) {
            $sql .= " AND `tinh_thanh` = ?";
            $params[] = $filters['tinh_thanh'];
        }
        
        // Filter theo loại chùa
        if (!empty($filters['loai_chua'])) {
            $sql .= " AND `loai_chua` = ?";
            $params[] = $filters['loai_chua'];
        }
        
        // Filter theo trạng thái
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND `trang_thai` = ?";
            $params[] = $filters['trang_thai'];
        }
        
        // Filter theo tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (`ten_chua` LIKE ? OR `ten_tieng_khmer` LIKE ? OR `dia_chi` LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $orderBy = $filters['orderBy'] ?? 'ngay_tao';
        $orderDir = $filters['orderDir'] ?? 'DESC';
        
        $sql .= " ORDER BY `{$orderBy}` {$orderDir} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params) ?: [];
    }
    
    /**
     * Lấy danh sách tỉnh thành
     */
    public function getProvinces() {
        $sql = "SELECT DISTINCT `tinh_thanh` 
                FROM `{$this->table}` 
                WHERE `tinh_thanh` IS NOT NULL AND `tinh_thanh` != ''
                ORDER BY `tinh_thanh`";
        $results = $this->db->query($sql);
        return $results ? array_column($results, 'tinh_thanh') : [];
    }
    
    /**
     * Lấy chùa theo tỉnh
     */
    public function getByProvince($province, $limit = 50) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `tinh_thanh` = ? AND `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$province, self::STATUS_ACTIVE, $limit]) ?: [];
    }
    
    /**
     * Lấy chùa theo loại
     */
    public function getByType($type, $limit = 50) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `loai_chua` = ? AND `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$type, self::STATUS_ACTIVE, $limit]) ?: [];
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
    
    /**
     * Đếm theo tỉnh
     */
    public function countByProvince($province) {
        return $this->count("`tinh_thanh` = ?", [$province]);
    }
    
    /**
     * Lấy chùa nổi bật
     */
    public function getFeatured($limit = 5) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_ACTIVE, $limit]) ?: [];
    }
    
    /**
     * Lấy chùa gần đây
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `ngay_tao` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_ACTIVE, $limit]) ?: [];
    }
    
    /**
     * Tìm kiếm chùa (override BaseModel)
     */
    public function search($keyword, $fields = [], $limit = 50) {
        // Nếu không truyền fields, dùng mặc định
        if (empty($fields)) {
            $fields = ['ten_chua', 'ten_tieng_khmer', 'dia_chi', 'tinh_thanh'];
        }
        return parent::search($keyword, $fields, $limit);
    }
    
    /**
     * Lấy thống kê
     */
    public function getStatistics() {
        $stats = [];
        
        // Tổng số chùa
        $stats['total'] = $this->count();
        
        // Theo trạng thái
        $stats['active'] = $this->countByStatus(self::STATUS_ACTIVE);
        $stats['inactive'] = $this->countByStatus(self::STATUS_INACTIVE);
        
        // Theo loại
        $sql = "SELECT `loai_chua`, COUNT(*) as count 
                FROM `{$this->table}` 
                GROUP BY `loai_chua`";
        $stats['by_type'] = $this->db->query($sql) ?: [];
        
        // Theo tỉnh
        $sql = "SELECT `tinh_thanh`, COUNT(*) as count 
                FROM `{$this->table}` 
                WHERE `tinh_thanh` IS NOT NULL 
                GROUP BY `tinh_thanh` 
                ORDER BY count DESC 
                LIMIT 10";
        $stats['by_province'] = $this->db->query($sql) ?: [];
        
        // Tổng lượt xem
        $sql = "SELECT SUM(`luot_xem`) as total FROM `{$this->table}`";
        $result = $this->db->querySingle($sql);
        $stats['total_views'] = $result ? (int)$result['total'] : 0;
        
        return $stats;
    }
}
