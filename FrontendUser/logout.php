<?php
/**
 * Đăng xuất - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();

// Xóa remember token nếu có
if(isset($_COOKIE['remember_token']) && isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $db = Database::getInstance();
    
    $db->execute(
        "UPDATE nguoi_dung SET remember_token = NULL WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    setcookie('remember_token', '', time() - 3600, '/');
}

// Xóa tất cả session
session_unset();
session_destroy();

// Redirect về trang chủ
header('Location: index.php');
exit;
