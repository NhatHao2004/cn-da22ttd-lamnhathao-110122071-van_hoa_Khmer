<?php
/**
 * User Model
 */
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM nguoi_dung WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO nguoi_dung (ho_ten, email, mat_khau, ngay_tao, trang_thai) 
            VALUES (?, ?, ?, NOW(), 'hoat_dong')
        ");
        $stmt->execute([
            $data['ho_ten'],
            $data['email'],
            password_hash($data['mat_khau'], PASSWORD_DEFAULT)
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'ma_nguoi_dung' && $key !== 'mat_khau') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE nguoi_dung SET " . implode(', ', $fields) . " WHERE ma_nguoi_dung = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function updatePassword($id, $password) {
        $stmt = $this->pdo->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?");
        return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }
    
    public function addPoints($id, $points) {
        $stmt = $this->pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?");
        return $stmt->execute([$points, $id]);
    }
    
    public function getLeaderboard($limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT ma_nguoi_dung, ho_ten, anh_dai_dien, tong_diem 
            FROM nguoi_dung 
            WHERE trang_thai = 'hoat_dong' 
            ORDER BY tong_diem DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getRank($id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) + 1 
            FROM nguoi_dung 
            WHERE tong_diem > (SELECT tong_diem FROM nguoi_dung WHERE ma_nguoi_dung = ?)
        ");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
    
    public function getBadges($id) {
        $stmt = $this->pdo->prepare("
            SELECT h.*, hn.ngay_dat_duoc 
            FROM huy_hieu h 
            JOIN huy_hieu_nguoi_dung hn ON h.ma_huy_hieu = hn.ma_huy_hieu 
            WHERE hn.ma_nguoi_dung = ?
            ORDER BY hn.ngay_dat_duoc DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    
    public function getProgress($id) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, b.tieu_de, b.cap_do 
            FROM tien_trinh_hoc_tap t 
            JOIN bai_hoc b ON t.ma_bai_hoc = b.ma_bai_hoc 
            WHERE t.ma_nguoi_dung = ?
            ORDER BY t.ngay_hoan_thanh DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}
?>
