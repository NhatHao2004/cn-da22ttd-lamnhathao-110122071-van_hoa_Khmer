<?php
/**
 * Model Quản Trị Viên
 * Quản lý CRUD cho bảng quan_tri_vien
 * 
 * @author Lâm Nhật Hào
 * @version 2.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseModel.php';

class QuanTriVien extends BaseModel {
    protected $table = 'quan_tri_vien';
    protected $primaryKey = 'ma_qtv';
    
    protected $fillable = [
        'ten_dang_nhap', 'email', 'mat_khau', 'ho_ten', 'vai_tro', 
        'trang_thai', 'anh_dai_dien', 'so_dien_thoai', 'dia_chi'
    ];
    
    protected $hidden = ['mat_khau'];
    
    // Các vai trò
    const ROLE_SUPER_ADMIN = 'sieu_quan_tri';
    const ROLE_ADMIN = 'quan_tri';
    const ROLE_EDITOR = 'bien_tap_vien';
    
    // Các trạng thái
    const STATUS_ACTIVE = 'hoat_dong';
    const STATUS_SUSPENDED = 'tam_khoa';
    const STATUS_BANNED = 'khoa_vinh_vien';
    
    /**
     * Lấy tất cả quản trị viên (ẩn mật khẩu) - Override BaseModel
     */
    public function getAll($limit = 50, $offset = 0, $orderBy = null, $orderDir = 'DESC') {
        $orderBy = $orderBy ?? 'ngay_tao';
        
        $sql = "SELECT `ma_qtv`, `ten_dang_nhap`, `email`, `ho_ten`, `vai_tro`, `trang_thai`, 
                `anh_dai_dien`, `so_lan_dang_nhap`, `lan_dang_nhap_cuoi`, `ngay_tao`, `ngay_cap_nhat`
                FROM `{$this->table}` 
                ORDER BY `{$orderBy}` {$orderDir} 
                LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset]) ?: [];
    }
    
    /**
     * Lấy quản trị viên theo ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `ma_qtv` = ?";
        return $this->db->querySingle($sql, [$id]);
    }
    
    /**
     * Lấy theo username
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `ten_dang_nhap` = ?";
        return $this->db->querySingle($sql, [$username]);
    }
    
    /**
     * Validate dữ liệu
     */
    protected function validate($data, $id = null) {
        $errors = [];
        
        // Validate username
        if (empty($data['ten_dang_nhap'])) {
            $errors[] = 'Tên đăng nhập không được để trống';
        } elseif (strlen($data['ten_dang_nhap']) < 3) {
            $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự';
        } elseif ($this->usernameExists($data['ten_dang_nhap'], $id)) {
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $errors[] = 'Email không được để trống';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        } elseif ($this->emailExists($data['email'], $id)) {
            $errors[] = 'Email đã tồn tại';
        }
        
        // Validate password (chỉ khi tạo mới)
        if (!$id && empty($data['mat_khau'])) {
            $errors[] = 'Mật khẩu không được để trống';
        } elseif (!empty($data['mat_khau']) && strlen($data['mat_khau']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }
        
        // Validate vai trò
        $validRoles = [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_EDITOR];
        if (isset($data['vai_tro']) && !in_array($data['vai_tro'], $validRoles)) {
            $errors[] = 'Vai trò không hợp lệ';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Tạo quản trị viên mới
     */
    public function create($data) {
        // Hash password
        if (isset($data['mat_khau'])) {
            $data['mat_khau'] = password_hash($data['mat_khau'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        
        // Set defaults
        $data['vai_tro'] = $data['vai_tro'] ?? self::ROLE_EDITOR;
        $data['trang_thai'] = $data['trang_thai'] ?? self::STATUS_ACTIVE;
        
        $id = parent::create($data);
        
        if ($id) {
            $this->logActivity('create', $id, "Tạo quản trị viên: {$data['ten_dang_nhap']}");
        }
        
        return $id;
    }
    
    /**
     * Cập nhật quản trị viên
     */
    public function update($id, $data) {
        // Hash password nếu có
        if (isset($data['mat_khau']) && !empty($data['mat_khau'])) {
            $data['mat_khau'] = password_hash($data['mat_khau'], PASSWORD_BCRYPT, ['cost' => 12]);
        } else {
            unset($data['mat_khau']); // Không update password nếu rỗng
        }
        
        $result = parent::update($id, $data);
        
        if ($result) {
            $this->logActivity('update', $id, "Cập nhật quản trị viên ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Xóa quản trị viên
     */
    public function delete($id) {
        // Không cho xóa siêu quản trị
        $admin = $this->getById($id);
        if ($admin && $admin['vai_tro'] === self::ROLE_SUPER_ADMIN) {
            throw new Exception('Không thể xóa Siêu Quản Trị');
        }
        
        // Không cho xóa chính mình
        if ($id == ($_SESSION['admin_id'] ?? 0)) {
            throw new Exception('Không thể xóa tài khoản của chính bạn');
        }
        
        $result = parent::delete($id);
        
        if ($result) {
            $this->logActivity('delete', $id, "Xóa quản trị viên ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Xác thực đăng nhập
     */
    public function authenticate($username, $password) {
        $admin = $this->getByUsername($username);
        
        if (!$admin) {
            return false;
        }
        
        // Kiểm tra trạng thái
        if ($admin['trang_thai'] !== self::STATUS_ACTIVE) {
            throw new Exception('Tài khoản đã bị khóa');
        }
        
        // Verify password
        if (!password_verify($password, $admin['mat_khau'])) {
            return false;
        }
        
        // Cập nhật thông tin đăng nhập
        $this->updateLoginInfo($admin['ma_qtv']);
        
        return $admin;
    }
    
    /**
     * Cập nhật thông tin đăng nhập
     */
    public function updateLoginInfo($id) {
        $sql = "UPDATE `{$this->table}` 
                SET `so_lan_dang_nhap` = `so_lan_dang_nhap` + 1,
                    `lan_dang_nhap_cuoi` = NOW(),
                    `ip_dang_nhap_cuoi` = ?
                WHERE `{$this->primaryKey}` = ?";
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        return $this->db->execute($sql, [$ip, $id]);
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        $admin = $this->getById($id);
        
        if (!$admin) {
            throw new Exception('Quản trị viên không tồn tại');
        }
        
        // Verify old password
        $fullAdmin = $this->db->querySingle("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
        
        if (!password_verify($oldPassword, $fullAdmin['mat_khau'])) {
            throw new Exception('Mật khẩu cũ không đúng');
        }
        
        // Update new password
        return $this->update($id, ['mat_khau' => $newPassword]);
    }
    
    /**
     * Reset mật khẩu
     */
    public function resetPassword($id, $newPassword) {
        return $this->update($id, ['mat_khau' => $newPassword]);
    }
    
    /**
     * Lấy theo vai trò
     */
    public function getByRole($role, $limit = 50) {
        $sql = "SELECT `ma_qtv`, `ten_dang_nhap`, `email`, `ho_ten`, `vai_tro`, `trang_thai`, 
                `anh_dai_dien`, `ngay_tao`
                FROM `{$this->table}` 
                WHERE `vai_tro` = ? 
                ORDER BY `ngay_tao` DESC 
                LIMIT ?";
        return $this->db->query($sql, [$role, $limit]) ?: [];
    }
    
    /**
     * Đếm theo vai trò
     */
    public function countByRole($role) {
        return $this->count("`vai_tro` = ?", [$role]);
    }
    
    /**
     * Đếm theo trạng thái
     */
    public function countByStatus($status) {
        return $this->count("`trang_thai` = ?", [$status]);
    }
    
    /**
     * Khóa tài khoản
     */
    public function suspend($id, $reason = '') {
        $result = $this->update($id, ['trang_thai' => self::STATUS_SUSPENDED]);
        
        if ($result) {
            $this->logActivity('suspend', $id, "Khóa tạm thời tài khoản. Lý do: {$reason}");
        }
        
        return $result;
    }
    
    /**
     * Mở khóa tài khoản
     */
    public function activate($id) {
        $result = $this->update($id, ['trang_thai' => self::STATUS_ACTIVE]);
        
        if ($result) {
            $this->logActivity('activate', $id, "Kích hoạt lại tài khoản");
        }
        
        return $result;
    }
    
    /**
     * Tìm kiếm quản trị viên (override BaseModel)
     */
    public function search($keyword, $fields = [], $limit = 50) {
        // Nếu không truyền fields, dùng mặc định
        if (empty($fields)) {
            $fields = ['ten_dang_nhap', 'email', 'ho_ten'];
        }
        return parent::search($keyword, $fields, $limit);
    }
    
    /**
     * Kiểm tra username tồn tại
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `ten_dang_nhap` = ?";
        $params = [$username];
        
        if($excludeId) {
            $sql .= " AND `ma_qtv` != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Kiểm tra email tồn tại
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `email` = ?";
        $params = [$email];
        
        if($excludeId) {
            $sql .= " AND `ma_qtv` != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->querySingle($sql, $params);
        return $result['count'] > 0;
    }
}
