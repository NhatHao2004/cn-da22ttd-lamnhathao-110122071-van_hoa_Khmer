<?php
/**
 * BaiHoc Model
 * Xử lý các thao tác với bảng bai_hoc
 */

require_once __DIR__ . '/../config/database.php';

class BaiHoc {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả bài học
     */
    public function getAll($limit = 50, $offset = 0, $cap_do = null) {
        $sql = "SELECT bh.*, dm.ten_danh_muc 
                FROM bai_hoc bh
                LEFT JOIN danh_muc dm ON bh.ma_danh_muc = dm.ma_danh_muc";
        
        $params = [];
        if ($cap_do) {
            $sql .= " WHERE bh.cap_do = ?";
            $params[] = $cap_do;
        }
        
        $sql .= " ORDER BY bh.thu_tu, bh.ngay_tao DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Lấy bài học theo ID
     */
    public function getById($id) {
        $sql = "SELECT bh.*, dm.ten_danh_muc 
                FROM bai_hoc bh
                LEFT JOIN danh_muc dm ON bh.ma_danh_muc = dm.ma_danh_muc
                WHERE bh.ma_bai_hoc = ?";
        return $this->db->querySingle($sql, [$id]);
    }
    
    /**
     * Tạo bài học mới
     */
    public function create($data) {
        $sql = "INSERT INTO bai_hoc (ma_danh_muc, tieu_de, slug, mo_ta,
                noi_dung, cap_do, thu_tu, thoi_luong, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['ma_danh_muc'] ?? null,
            $data['tieu_de'],
            $this->generateSlug($data['tieu_de']),
            $data['mo_ta'] ?? null,
            $data['noi_dung'] ?? null,
            $data['cap_do'] ?? 'co_ban',
            $data['thu_tu'] ?? 0,
            $data['thoi_luong'] ?? 30,
            $data['trang_thai'] ?? 'xuat_ban'
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật bài học
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'ma_danh_muc', 'tieu_de', 'mo_ta', 'noi_dung',
            'cap_do', 'thu_tu', 'thoi_luong', 'trang_thai'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['tieu_de'])) {
            $fields[] = "slug = ?";
            $params[] = $this->generateSlug($data['tieu_de']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE bai_hoc SET " . implode(', ', $fields) . " WHERE ma_bai_hoc = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa bài học
     */
    public function delete($id) {
        $sql = "DELETE FROM bai_hoc WHERE ma_bai_hoc = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm tổng số bài học
     */
    public function count($cap_do = null) {
        $sql = "SELECT COUNT(*) as total FROM bai_hoc";
        $params = [];
        
        if ($cap_do) {
            $sql .= " WHERE cap_do = ?";
            $params[] = $cap_do;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy từ vựng của bài học
     */
    public function getVocabulary($ma_bai_hoc) {
        $sql = "SELECT * FROM tu_vung WHERE ma_bai_hoc = ? ORDER BY thu_tu";
        return $this->db->query($sql, [$ma_bai_hoc]);
    }
    
    /**
     * Lấy danh mục bài học
     */
    public function getCategories() {
        $sql = "SELECT * FROM danh_muc WHERE loai_danh_muc = 'bai_hoc' ORDER BY thu_tu";
        return $this->db->query($sql);
    }
    
    /**
     * Tạo slug
     */
    private function generateSlug($title) {
        $slug = mb_strtolower($title, 'UTF-8');
        
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
        
        $slug = str_replace($vietnamese, $latin, $slug);
        $slug = preg_replace('/[^a-z0-9-\s]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }
}
