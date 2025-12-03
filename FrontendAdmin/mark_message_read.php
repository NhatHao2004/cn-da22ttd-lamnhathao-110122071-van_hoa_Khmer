<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$admin_id = $_SESSION['admin_id'];

// Lấy dữ liệu JSON từ request
$data = json_decode(file_get_contents('php://input'), true);
$message_id = $data['message_id'] ?? null;

if($message_id) {
    // Đánh dấu tin nhắn đã đọc
    $result = $db->execute(
        "UPDATE tin_nhan SET trang_thai = 'da_doc' WHERE ma_tin_nhan = ? AND ma_nguoi_nhan = ?",
        [$message_id, $admin_id]
    );
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Đã đánh dấu đọc' : 'Có lỗi xảy ra'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin'
    ], JSON_UNESCAPED_UNICODE);
}
