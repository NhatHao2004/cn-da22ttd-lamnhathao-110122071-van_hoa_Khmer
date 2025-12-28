<?php
/**
 * Model Truyện Dân Gian
 * Quản lý truyện dân gian Khmer
 * 
 * @author Lâm Nhật Hào
 * @version 1.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseModel.php';

class TruyenDanGian extends BaseModel {
    protected $table = 'truyen_dan_gian';
    protected $primaryKey = 'ma_truyen';
    
    protected $fillable = [
        'tieu_de', 'tieu_de_khmer', 'slug', 'tom_tat', 'noi_dung',
        'anh_dai_dien', 'ma_danh_muc', 'tac_gia', 'nguon',
        'trang_thai', 'ma_nguoi_tao'
    ];
    
    // Trạng thái
    const STATUS_VISIBLE = 'hien_thi';
    const STATUS_HIDDEN = 'an';
    
    // Thể loại
    const TYPE_FAIRY_TALE = 'truyen_co_tich';
    const TYPE_LEGEND = 'truyen_truyen_thuyet';
    const TYPE_FOLK = 'truyen_dan_gian';
    const TYPE_FABLE = 'truyen_ngụ_ngon';
    
    /**
     * Validate dữ liệu
     */
    protected function validate($data, $id = null) {
        $errors = [];
        
        // Validate tiêu đề
        if (isset($data['tieu_de'])) {
            if (empty($data['tieu_de'])) {
                $errors[] = 'Tiêu đề không được để trống';
            } elseif (mb_strlen($data['tieu_de']) < 5) {
                $errors[] = 'Tiêu đề phải có ít nhất 5 ký tự';
            }
        }
        
        // Validate nội dung - chỉ validate nếu có trong data
        if (isset($data['noi_dung'])) {
            if (empty($data['noi_dung'])) {
                $errors[] = 'Nội dung không được để trống';
            } elseif (mb_strlen($data['noi_dung']) < 50) {
                $errors[] = 'Nội dung phải có ít nhất 50 ký tự';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Tạo truyện mới
     */
    public function create($data) {
        // Tự động tạo slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['tieu_de']);
        }
        
        // Set người tạo
        $data['ma_nguoi_tao'] = $_SESSION['admin_id'] ?? null;
        
        $id = parent::create($data);
        
        if ($id) {
            $this->logActivity('create', $id, "Tạo truyện: {$data['tieu_de']}");
        }
        
        return $id;
    }
    
    /**
     * Cập nhật truyện
     */
    public function update($id, $data) {
        // Cập nhật slug nếu tiêu đề thay đổi
        if (isset($data['tieu_de']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['tieu_de']);
        }
        
        // Không set ma_nguoi_cap_nhat vì cột này chưa có trong database
        
        $result = parent::update($id, $data);
        
        if ($result) {
            $this->logActivity('update', $id, "Cập nhật truyện ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Lấy tất cả với filter
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? 'ngay_tao';
        
        $sql = "SELECT t.*, q.ho_ten as nguoi_tao
                FROM `{$this->table}` t
                LEFT JOIN `quan_tri_vien` q ON t.ma_nguoi_tao = q.ma_qtv
                ORDER BY t.{$orderBy} {$orderDir}
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy truyện theo thể loại
     */
    public function getByType($type, $limit = 20) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `the_loai` = ? AND `trang_thai` = ? 
                ORDER BY `ngay_tao` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$type, self::STATUS_VISIBLE, $limit]) ?: [];
    }
    
    /**
     * Lấy truyện phổ biến
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_VISIBLE, $limit]) ?: [];
    }
    
    /**
     * Lấy truyện mới nhất
     */
    public function getLatest($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `ngay_tao` DESC 
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
    
    /**
     * Đếm theo thể loại
     */
    public function countByType($type) {
        return $this->count("`the_loai` = ?", [$type]);
    }
    
    /**
     * Tìm kiếm truyện
     */
    public function search($keyword, $fields = [], $limit = 50) {
        if (empty($fields)) {
            $fields = ['tieu_de', 'tieu_de_khmer', 'tom_tat', 'noi_dung'];
        }
        return parent::search($keyword, $fields, $limit);
    }
    
    /**
     * Lấy truyện liên quan
     */
    public function getRelated($id, $type, $limit = 5) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `the_loai` = ? 
                AND `{$this->primaryKey}` != ? 
                AND `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$type, $id, self::STATUS_VISIBLE, $limit]) ?: [];
    }
    
    /**
     * Lấy danh sách thể loại
     */
    public function getTypes() {
        return [
            self::TYPE_FAIRY_TALE => 'Truyện cổ tích',
            self::TYPE_LEGEND => 'Truyền thuyết',
            self::TYPE_FOLK => 'Truyện dân gian',
            self::TYPE_FABLE => 'Truyện ngụ ngôn'
        ];
    }
}
