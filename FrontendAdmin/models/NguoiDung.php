<?php
/**
 * NguoiDung Model
 * Xử lý các thao tác với bảng nguoi_dung
 */

require_once __DIR__ . '/../config/database.php';

class NguoiDung {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả người dùng
     */
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT ma_nguoi_dung, ten_dang_nhap, email, ho_ten, ngay_sinh, 
                gioi_tinh, so_dien_thoai, anh_dai_dien, tong_diem, cap_do, 
                trang_thai, ngay_tao, lan_dang_nhap_cuoi 
                FROM nguoi_dung 
                ORDER BY ngay_tao DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset]);
    }
    
    /**
     * Lấy người dùng theo ID
     */
    public function getById($id) {
        $sql = "SELECT ma_nguoi_dung, ten_dang_nhap, email, ho_ten, ngay_sinh, 
                gioi_tinh, so_dien_thoai, anh_dai_dien, tong_diem, cap_do, 
                trang_thai, ngay_tao, lan_dang_nhap_cuoi 
                FROM nguoi_dung WHERE ma_nguoi_dung = ?";
        return $this->db->querySingle($sql, [$id]);
    }
    
    /**
     * Tạo người dùng mới
     */
    public function create($data) {
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, email, mat_khau, ho_ten, 
                ngay_sinh, gioi_tinh, so_dien_thoai, anh_dai_dien, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        
        $params = [
            $data['ten_dang_nhap'],
            $data['email'],
            $hashedPassword,
            $data['ho_ten'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['anh_dai_dien'] ?? null,
            $data['trang_thai'] ?? 'hoat_dong'
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật người dùng
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'email', 'ho_ten', 'ngay_sinh', 'gioi_tinh', 'so_dien_thoai',
            'anh_dai_dien', 'tong_diem', 'cap_do', 'trang_thai'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['mat_khau'])) {
            $fields[] = "mat_khau = ?";
            $params[] = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE nguoi_dung SET " . implode(', ', $fields) . " WHERE ma_nguoi_dung = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa người dùng
     */
    public function delete($id) {
        $sql = "DELETE FROM nguoi_dung WHERE ma_nguoi_dung = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đếm tổng số người dùng
     */
    public function count($trang_thai = null) {
        $sql = "SELECT COUNT(*) as total FROM nguoi_dung";
        $params = [];
        
        if ($trang_thai) {
            $sql .= " WHERE trang_thai = ?";
            $params[] = $trang_thai;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Tìm kiếm người dùng
     */
    public function search($keyword, $limit = 50) {
        $sql = "SELECT ma_nguoi_dung, ten_dang_nhap, email, ho_ten, so_dien_thoai, 
                anh_dai_dien, tong_diem, cap_do, trang_thai, ngay_tao 
                FROM nguoi_dung 
                WHERE ten_dang_nhap LIKE ? OR email LIKE ? OR ho_ten LIKE ? 
                ORDER BY ngay_tao DESC LIMIT ?";
        $searchTerm = "%$keyword%";
        return $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Kiểm tra username đã tồn tại
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM nguoi_dung WHERE ten_dang_nhap = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND ma_nguoi_dung != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Kiểm tra email đã tồn tại
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM nguoi_dung WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND ma_nguoi_dung != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Lấy top người dùng theo điểm
     */
    public function getTopUsers($limit = 10) {
        $sql = "SELECT ma_nguoi_dung, ten_dang_nhap, ho_ten, anh_dai_dien, 
                tong_diem, cap_do 
                FROM nguoi_dung 
                WHERE trang_thai = 'hoat_dong' 
                ORDER BY tong_diem DESC LIMIT ?";
        return $this->db->query($sql, [$limit]);
    }
    
    /**
     * Cập nhật điểm người dùng
     */
    public function updatePoints($id, $points) {
        $sql = "UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?";
        return $this->db->execute($sql, [$points, $id]);
    }
    
    /**
     * Cập nhật cấp độ người dùng
     */
    public function updateLevel($id, $cap_do) {
        $sql = "UPDATE nguoi_dung SET cap_do = ? WHERE ma_nguoi_dung = ?";
        return $this->db->execute($sql, [$cap_do, $id]);
    }
}
