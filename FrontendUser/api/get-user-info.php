<?php
/**
 * API lấy thông tin người dùng hiện tại
 * Hỗ trợ cả 2 cách lưu session
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Hàm lấy user ID từ session - hỗ trợ cả 2 cách lưu
function getUserIdFromSession() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return intval($_SESSION['user_id']);
    }
    if (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        return intval($_SESSION['user']['ma_nguoi_dung']);
    }
    return null;
}

$userId = getUserIdFromSession();

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'Chưa đăng nhập',
        'isLoggedIn' => false
    ]);
    exit();
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT ma_nguoi_dung, ten_dang_nhap, email, ho_ten, anh_dai_dien, tong_diem, cap_do
        FROM nguoi_dung 
        WHERE ma_nguoi_dung = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Xây dựng URL avatar
        $avatarUrl = null;
        if ($user['anh_dai_dien']) {
            $avatarUrl = '/DoAn_ChuyenNganh/uploads/avatar/' . $user['anh_dai_dien'];
        }
        
        echo json_encode([
            'success' => true,
            'isLoggedIn' => true,
            'user' => [
                'id' => $user['ma_nguoi_dung'],
                'username' => $user['ten_dang_nhap'],
                'name' => $user['ho_ten'],
                'email' => $user['email'],
                'avatar' => $avatarUrl,
                'points' => $user['tong_diem'],
                'level' => $user['cap_do']
            ]
        ]);
    } else {
        // User ID trong session nhưng không tìm thấy trong DB
        // Có thể user đã bị xóa
        unset($_SESSION['user_id']);
        unset($_SESSION['user']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thông tin người dùng',
            'isLoggedIn' => false
        ]);
    }
} catch (PDOException $e) {
    error_log("get-user-info error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi server'
    ]);
}
