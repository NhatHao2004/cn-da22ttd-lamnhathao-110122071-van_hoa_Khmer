<?php
/**
 * TuVung Model
 * Xử lý các thao tác với bảng tu_vung
 */

require_once __DIR__ . '/../config/database.php';

class TuVung {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả từ vựng
     */
    public function getAll($limit = 100, $offset = 0, $ma_bai_hoc = null) {
        $sql = "SELECT tv.*, bh.tieu_de as ten_bai_hoc 
                FROM tu_vung tv
                LEFT JOIN bai_hoc bh ON tv.ma_bai_hoc = bh.ma_bai_hoc";
        
        $params = [];
        if ($ma_bai_hoc) {
            $sql .= " WHERE tv.ma_bai_hoc = ?";
            $params[] = $ma_bai_hoc;
        }
        
        $sql .= " ORDER BY tv.ma_bai_hoc, tv.thu_tu LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Lấy từ vựng theo ID
     */
    public function getById($id) {
        $sql = "SELECT tv.*, bh.tieu_de as ten_bai_hoc 
                FROM tu_vung tv
                LEFT JOIN bai_hoc bh ON tv.ma_bai_hoc = bh.ma_bai_hoc
                WHERE tv.ma_tu_vung = ?";
        return $this->db->querySingle($sql, [$id]);
    }
    
    /**
     * Tạo từ vựng mới
     */
    public function create($data) {
        $sql = "INSERT INTO tu_vung (ma_bai_hoc, tu_khmer, phien_am, nghia_tieng_viet, 
                vi_du, file_am_thanh, anh_minh_hoa, loai_tu, ghi_chu, thu_tu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['ma_bai_hoc'] ?? null,
            $data['tu_khmer'],
            $data['phien_am'] ?? null,
            $data['nghia_tieng_viet'],
            $data['vi_du'] ?? null,
            $data['file_am_thanh'] ?? null,
            $data['anh_minh_hoa'] ?? null,
            $data['loai_tu'] ?? 'danh_tu',
            $data['ghi_chu'] ?? null,
            $data['thu_tu'] ?? 0
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật từ vựng
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'ma_bai_hoc', 'tu_khmer', 'phien_am', 'nghia_tieng_viet',
            'vi_du', 'file_am_thanh', 'anh_minh_hoa', 'loai_tu', 'ghi_chu', 'thu_tu'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE tu_vung SET " . implode(', ', $fields) . " WHERE ma_tu_vung = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa từ vựng
     */
    public function delete($id) {
        $sql = "DELETE FROM tu_vung WHERE ma_tu_vung = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm tổng số từ vựng
     */
    public function count($ma_bai_hoc = null) {
        $sql = "SELECT COUNT(*) as total FROM tu_vung";
        $params = [];
        
        if ($ma_bai_hoc) {
            $sql .= " WHERE ma_bai_hoc = ?";
            $params[] = $ma_bai_hoc;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Tìm kiếm từ vựng
     */
    public function search($keyword, $limit = 50) {
        $sql = "SELECT tv.*, bh.tieu_de as ten_bai_hoc 
                FROM tu_vung tv
                LEFT JOIN bai_hoc bh ON tv.ma_bai_hoc = bh.ma_bai_hoc
                WHERE tv.tu_khmer LIKE ? OR tv.nghia_tieng_viet LIKE ? OR tv.phien_am LIKE ? 
                ORDER BY tv.thu_tu LIMIT ?";
        $searchTerm = "%$keyword%";
        return $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Lấy từ vựng theo loại từ
     */
    public function getByType($loai_tu, $limit = 50) {
        $sql = "SELECT * FROM tu_vung WHERE loai_tu = ? ORDER BY thu_tu LIMIT ?";
        return $this->db->query($sql, [$loai_tu, $limit]);
    }
}
