<?php
/**
 * Model Bài Viết Văn Hóa
 * Quản lý CRUD cho bảng bai_viet
 */

require_once __DIR__ . '/../config/database.php';

class BaiViet {
    private $db;
    private $table = 'bai_viet';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả bài viết với thông tin tác giả và danh mục
     */
    public function getAll($limit = 50, $offset = 0, $trang_thai = null) {
        $sql = "SELECT bv.*, 
                       qtv.ho_ten as ten_tac_gia,
                       dm.ten_danh_muc as ten_danh_muc
                FROM `{$this->table}` bv
                LEFT JOIN `quan_tri_vien` qtv ON bv.ma_tac_gia = qtv.ma_qtv
                LEFT JOIN `danh_muc_van_hoa` dm ON bv.ma_danh_muc = dm.ma_danh_muc";
        
        $params = [];
        if($trang_thai) {
            $sql .= " WHERE bv.trang_thai = ?";
            $params[] = $trang_thai;
        }
        
        $sql .= " ORDER BY bv.ngay_tao DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Lấy bài viết theo ID
     */
    public function getById($id) {
        $sql = "SELECT bv.*, 
                       qtv.ho_ten as ten_tac_gia,
                       dm.ten_danh_muc as ten_danh_muc
                FROM `{$this->table}` bv
                LEFT JOIN `quan_tri_vien` qtv ON bv.ma_tac_gia = qtv.ma_qtv
                LEFT JOIN `danh_muc_van_hoa` dm ON bv.ma_danh_muc = dm.ma_danh_muc
                WHERE bv.ma_bai_viet = ?";
        return $this->db->querySingle($sql, [$id]);
    }
    
    /**
     * Tạo bài viết mới
     */
    public function create($data) {
        // Tự động tạo đường dẫn từ tiêu đề
        if(empty($data['duong_dan'])) {
            $data['duong_dan'] = $this->createSlug($data['tieu_de']);
        }
        
        $sql = "INSERT INTO `{$this->table}` 
                (`ma_danh_muc`, `tieu_de`, `tieu_de_khmer`, `duong_dan`, `tom_tat`, 
                 `noi_dung`, `anh_dai_dien`, `ma_tac_gia`, `trang_thai`, `ngay_xuat_ban`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['ma_danh_muc'] ?? null,
            $data['tieu_de'],
            $data['tieu_de_khmer'] ?? null,
            $data['duong_dan'],
            $data['tom_tat'] ?? null,
            $data['noi_dung'] ?? null,
            $data['anh_dai_dien'] ?? null,
            $data['ma_tac_gia'] ?? null,
            $data['trang_thai'] ?? 'nhap',
            ($data['trang_thai'] === 'da_xuat_ban') ? date('Y-m-d H:i:s') : null
        ];
        
        if($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Cập nhật bài viết
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        // Cập nhật đường dẫn nếu tiêu đề thay đổi
        if(isset($data['tieu_de']) && empty($data['duong_dan'])) {
            $data['duong_dan'] = $this->createSlug($data['tieu_de']);
        }
        
        // Cập nhật ngày xuất bản khi chuyển sang trạng thái đã xuất bản
        if(isset($data['trang_thai']) && $data['trang_thai'] === 'da_xuat_ban') {
            $current = $this->getById($id);
            if($current && $current['trang_thai'] !== 'da_xuat_ban') {
                $data['ngay_xuat_ban'] = date('Y-m-d H:i:s');
            }
        }
        
        foreach($data as $key => $value) {
            if($key !== 'ma_bai_viet') {
                $fields[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        
        if(empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $fields) . " WHERE `ma_bai_viet` = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa bài viết
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `ma_bai_viet` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Tăng lượt xem
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `luot_xem` = `luot_xem` + 1 WHERE `ma_bai_viet` = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm số lượng bài viết
     */
    public function count($where = '', $params = []) {
        return $this->db->count($this->table, $where, $params);
    }
    
    /**
     * Tạo slug từ tiêu đề tiếng Việt
     */
    private function createSlug($text) {
        // Chuyển về chữ thường
        $text = mb_strtolower($text, 'UTF-8');
        
        // Xóa dấu tiếng Việt
        $text = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $text);
        $text = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $text);
        $text = preg_replace('/[íìỉĩị]/u', 'i', $text);
        $text = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $text);
        $text = preg_replace('/[úùủũụưứừửữự]/u', 'u', $text);
        $text = preg_replace('/[ýỳỷỹỵ]/u', 'y', $text);
        $text = preg_replace('/đ/u', 'd', $text);
        
        // Xóa ký tự đặc biệt
        $text = preg_replace('/[^a-z0-9\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        // Thêm timestamp để đảm bảo unique
        return $text . '-' . time();
    }
    
    /**
     * Tìm kiếm bài viết
     */
    public function search($keyword, $limit = 20) {
        $sql = "SELECT bv.*, 
                       qtv.ho_ten as ten_tac_gia,
                       dm.ten_danh_muc as ten_danh_muc
                FROM `{$this->table}` bv
                LEFT JOIN `quan_tri_vien` qtv ON bv.ma_tac_gia = qtv.ma_qtv
                LEFT JOIN `danh_muc_van_hoa` dm ON bv.ma_danh_muc = dm.ma_danh_muc
                WHERE bv.tieu_de LIKE ? OR bv.noi_dung LIKE ?
                ORDER BY bv.ngay_tao DESC
                LIMIT ?";
        
        $searchTerm = '%' . $keyword . '%';
        return $this->db->query($sql, [$searchTerm, $searchTerm, $limit]);
    }
}
