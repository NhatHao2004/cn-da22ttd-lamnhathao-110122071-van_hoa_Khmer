<?php
/**
 * Model Văn Hóa
 * Quản lý bài viết văn hóa Khmer
 * 
 * @author Lâm Nhật Hào
 * @version 2.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseModel.php';

class VanHoa extends BaseModel {
    protected $table = 'van_hoa';
    protected $primaryKey = 'ma_van_hoa';
    
    protected $fillable = [
        'tieu_de', 'slug', 'mo_ta_ngan', 'noi_dung', 'hinh_anh',
        'ma_danh_muc', 'tac_gia', 'trang_thai', 'noi_bat',
        'ma_nguoi_tao', 'ngay_xuat_ban'
        // 'ma_nguoi_cap_nhat' - Cột này chưa có trong database
    ];
    
    // Trạng thái
    const STATUS_DRAFT = 'nhap';
    const STATUS_PUBLISHED = 'xuat_ban';
    const STATUS_ARCHIVED = 'luu_tru';
    
    /**
     * Validate dữ liệu
     */
    protected function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['tieu_de'])) {
            $errors[] = 'Tiêu đề không được để trống';
        } elseif (mb_strlen($data['tieu_de']) < 10) {
            $errors[] = 'Tiêu đề phải có ít nhất 10 ký tự';
        }
        
        if (empty($data['noi_dung'])) {
            $errors[] = 'Nội dung không được để trống';
        } elseif (mb_strlen($data['noi_dung']) < 50) {
            $errors[] = 'Nội dung phải có ít nhất 50 ký tự';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Tạo bài viết mới
     */
    public function create($data) {
        // Tự động tạo slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['tieu_de']);
        }
        
        // Set người tạo
        $data['ma_nguoi_tao'] = $_SESSION['admin_id'] ?? null;
        
        // Set ngày xuất bản nếu trạng thái là xuất bản
        if (($data['trang_thai'] ?? '') === self::STATUS_PUBLISHED && empty($data['ngay_xuat_ban'])) {
            $data['ngay_xuat_ban'] = date('Y-m-d H:i:s');
        }
        
        $id = parent::create($data);
        
        if ($id) {
            $this->logActivity('create', $id, "Tạo bài viết: {$data['tieu_de']}");
        }
        
        return $id;
    }
    
    /**
     * Cập nhật bài viết
     */
    public function update($id, $data) {
        // Cập nhật slug nếu tiêu đề thay đổi
        if (isset($data['tieu_de']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['tieu_de']);
        }
        
        // Không set ma_nguoi_cap_nhat vì cột này chưa có trong database
        
        // Set ngày xuất bản nếu chuyển sang trạng thái xuất bản
        $current = $this->getById($id);
        if ($current && $current['trang_thai'] !== self::STATUS_PUBLISHED 
            && ($data['trang_thai'] ?? '') === self::STATUS_PUBLISHED) {
            $data['ngay_xuat_ban'] = date('Y-m-d H:i:s');
        }
        
        $result = parent::update($id, $data);
        
        if ($result) {
            $this->logActivity('update', $id, "Cập nhật bài viết ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Lấy tất cả với filter (override BaseModel)
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? 'ngay_tao';
        
        $sql = "SELECT v.*, d.ten_danh_muc, q.ho_ten as nguoi_tao
                FROM `{$this->table}` v
                LEFT JOIN `danh_muc` d ON v.ma_danh_muc = d.ma_danh_muc
                LEFT JOIN `quan_tri_vien` q ON v.ma_nguoi_tao = q.ma_qtv
                ORDER BY v.{$orderBy} {$orderDir}
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy tất cả với filter nâng cao
     */
    public function getAllWithFilters($limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT v.*, d.ten_danh_muc, q.ho_ten as nguoi_tao
                FROM `{$this->table}` v
                LEFT JOIN `danh_muc` d ON v.ma_danh_muc = d.ma_danh_muc
                LEFT JOIN `quan_tri_vien` q ON v.ma_nguoi_tao = q.ma_qtv
                WHERE 1=1";
        
        $params = [];
        
        // Filter theo tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (v.tieu_de LIKE ? OR v.mo_ta_ngan LIKE ? OR v.noi_dung LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filter theo danh mục
        if (!empty($filters['danh_muc'])) {
            $sql .= " AND v.ma_danh_muc = ?";
            $params[] = $filters['danh_muc'];
        }
        
        // Filter theo trạng thái
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND v.trang_thai = ?";
            $params[] = $filters['trang_thai'];
        }
        
        // Filter theo nổi bật
        if (isset($filters['noi_bat'])) {
            $sql .= " AND v.noi_bat = ?";
            $params[] = $filters['noi_bat'];
        }
        
        $orderBy = $filters['orderBy'] ?? 'ngay_tao';
        $orderDir = $filters['orderDir'] ?? 'DESC';
        
        $sql .= " ORDER BY v.{$orderBy} {$orderDir} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params) ?: [];
    }
    
    /**
     * Lấy bài viết nổi bật
     */
    public function getFeatured($limit = 5) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `noi_bat` = 1 AND `trang_thai` = ? 
                ORDER BY `luot_xem` DESC, `ngay_tao` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_PUBLISHED, $limit]) ?: [];
    }
    
    /**
     * Lấy bài viết theo danh mục
     */
    public function getByCategory($categoryId, $limit = 20) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `ma_danh_muc` = ? AND `trang_thai` = ? 
                ORDER BY `ngay_xuat_ban` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$categoryId, self::STATUS_PUBLISHED, $limit]) ?: [];
    }
    
    /**
     * Lấy bài viết liên quan
     */
    public function getRelated($id, $categoryId, $limit = 5) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `ma_danh_muc` = ? 
                AND `{$this->primaryKey}` != ? 
                AND `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$categoryId, $id, self::STATUS_PUBLISHED, $limit]) ?: [];
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
     * Lấy tổng lượt xem
     */
    public function getTotalViews() {
        $sql = "SELECT SUM(`luot_xem`) as total FROM `{$this->table}`";
        $result = $this->db->querySingle($sql);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Lấy danh mục
     */
    public function getCategories() {
        $sql = "SELECT * FROM `danh_muc` 
                WHERE `loai` = 'van_hoa' AND `trang_thai` = 'hien_thi'
                ORDER BY `thu_tu`, `ten_danh_muc`";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Gắn tags cho bài viết
     */
    public function attachTags($id, $tagIds) {
        if (empty($tagIds)) {
            return true;
        }
        
        $this->beginTransaction();
        
        try {
            // Xóa tags cũ
            $sql = "DELETE FROM `van_hoa_tags` WHERE `ma_van_hoa` = ?";
            $this->db->execute($sql, [$id]);
            
            // Thêm tags mới
            $sql = "INSERT INTO `van_hoa_tags` (`ma_van_hoa`, `ma_tag`) VALUES (?, ?)";
            foreach ($tagIds as $tagId) {
                $this->db->execute($sql, [$id, $tagId]);
            }
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Lấy tags của bài viết
     */
    public function getTags($id) {
        $sql = "SELECT t.* 
                FROM `tags` t 
                INNER JOIN `van_hoa_tags` vt ON t.ma_tag = vt.ma_tag 
                WHERE vt.ma_van_hoa = ?";
        return $this->db->query($sql, [$id]) ?: [];
    }
    
    /**
     * Xuất bản bài viết
     */
    public function publish($id) {
        return $this->update($id, [
            'trang_thai' => self::STATUS_PUBLISHED,
            'ngay_xuat_ban' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Chuyển về nháp
     */
    public function unpublish($id) {
        return $this->update($id, ['trang_thai' => self::STATUS_DRAFT]);
    }
    
    /**
     * Đánh dấu nổi bật
     */
    public function setFeatured($id, $featured = 1) {
        return $this->update($id, ['noi_bat' => $featured]);
    }
    
    /**
     * Tìm kiếm (override BaseModel)
     */
    public function search($keyword, $fields = [], $limit = 50) {
        // Nếu không truyền fields, dùng mặc định
        if (empty($fields)) {
            $fields = ['tieu_de', 'mo_ta_ngan', 'noi_dung'];
        }
        return parent::search($keyword, $fields, $limit);
    }
    
    /**
     * Lấy bài viết phổ biến
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `luot_xem` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_PUBLISHED, $limit]) ?: [];
    }
    
    /**
     * Lấy bài viết mới nhất
     */
    public function getLatest($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `trang_thai` = ? 
                ORDER BY `ngay_xuat_ban` DESC 
                LIMIT ?";
        return $this->db->query($sql, [self::STATUS_PUBLISHED, $limit]) ?: [];
    }
}
