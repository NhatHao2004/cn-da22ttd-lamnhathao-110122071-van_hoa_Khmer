<?php
/**
 * File kiểm tra xác thực admin
 * Include file này ở đầu mỗi trang admin
 */

// Đảm bảo session đã được start
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra admin đã đăng nhập chưa
 */
function checkAdminAuth() {
    // Kiểm tra session timeout (30 phút không hoạt động)
    checkSessionTimeout(1800);
    
    if(!isset($_SESSION['admin_logged_in']) || 
       !isset($_SESSION['admin_id']) || 
       $_SESSION['admin_logged_in'] !== true) {
        
        // Lưu URL hiện tại để redirect sau khi đăng nhập
        $current_url = $_SERVER['REQUEST_URI'];
        header('Location: dangnhap.php?redirect=' . urlencode($current_url));
        exit;
    }
    
    // Cập nhật last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Lấy thông tin admin hiện tại
 */
function getAdminInfo() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? '',
        'name' => $_SESSION['admin_name'] ?? '',
        'role' => $_SESSION['admin_role'] ?? '',
        'avatar' => $_SESSION['admin_avatar'] ?? null
    ];
}

/**
 * Kiểm tra quyền admin
 */
function checkAdminRole($required_role) {
    $role_hierarchy = [
        'sieu_quan_tri' => 3,
        'quan_tri' => 2,
        'bien_tap_vien' => 1
    ];
    
    $current_role = $_SESSION['admin_role'] ?? '';
    $user_level = $role_hierarchy[$current_role] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 99;
    
    return $user_level >= $required_level;
}

/**
 * Kiểm tra có phải super admin không
 */
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'sieu_quan_tri';
}

/**
 * Cập nhật thông tin admin từ database
 * Gọi hàm này để đồng bộ session với database
 */
function refreshAdminInfo() {
    if(!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance();
    
    $admin_info = $db->querySingle(
        "SELECT ho_ten, ten_dang_nhap, vai_tro, anh_dai_dien, trang_thai FROM quan_tri_vien WHERE ma_qtv = ?",
        [$_SESSION['admin_id']]
    );
    
    if($admin_info) {
        // Kiểm tra trạng thái tài khoản
        if($admin_info['trang_thai'] !== 'hoat_dong') {
            session_destroy();
            header('Location: dangnhap.php');
            exit;
        }
        
        // Cập nhật session với thông tin mới nhất
        $_SESSION['admin_name'] = $admin_info['ho_ten'];
        $_SESSION['admin_username'] = $admin_info['ten_dang_nhap'];
        $_SESSION['admin_role'] = $admin_info['vai_tro'];
        $_SESSION['admin_avatar'] = $admin_info['anh_dai_dien'] ?? null;
        
        return true;
    } else {
        // Tài khoản không tồn tại, đăng xuất
        session_destroy();
        header('Location: dangnhap.php');
        exit;
    }
}

/**
 * Logout admin
 */
function logoutAdmin() {
    // Ghi log đăng xuất
    if(isset($_SESSION['admin_id'])) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance();
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $logSql = "INSERT INTO `nhat_ky_hoat_dong` 
                       (`ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `mo_ta`, `ip_address`) 
                       VALUES (?, 'quan_tri', 'logout', 'Đăng xuất khỏi hệ thống', ?)";
            $db->execute($logSql, [$_SESSION['admin_id'], $ip_address]);
        } catch(Exception $e) {
            error_log('Logout log error: ' . $e->getMessage());
        }
    }
    
    // Xóa session
    session_unset();
    session_destroy();
    
    // Xóa cookie session
    if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Xóa remember token cookie
    if(isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time()-3600, '/');
    }
    
    header('Location: dangnhap.php');
    exit;
}

/**
 * Kiểm tra session timeout
 */
function checkSessionTimeout($timeout = 3600) { // 1 giờ mặc định
    if(isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if($elapsed > $timeout) {
            logoutAdmin();
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Kiểm tra và xử lý Remember Me
 */
function checkRememberMe() {
    if(!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['remember_token'])) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance();
            
            $token = $_COOKIE['remember_token'];
            $token_hash = hash('sha256', $token);
            
            // Kiểm tra token trong database (cần tạo bảng remember_tokens)
            // Nếu hợp lệ, tự động đăng nhập
            
        } catch(Exception $e) {
            error_log('Remember me error: ' . $e->getMessage());
        }
    }
}
