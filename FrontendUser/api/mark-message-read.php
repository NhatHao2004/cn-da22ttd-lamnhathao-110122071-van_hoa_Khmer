<?php
/**
 * API: Mark message as read
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$messageId = intval($_POST['message_id'] ?? 0);

if (!$messageId) {
    echo json_encode(['success' => false, 'message' => 'ID tin nhắn không hợp lệ']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verify message belongs to current user
    $checkStmt = $pdo->prepare("SELECT nguoi_nhan FROM tin_nhan WHERE ma_tin_nhan = ?");
    $checkStmt->execute([$messageId]);
    $message = $checkStmt->fetch();
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Tin nhắn không tồn tại']);
        exit;
    }
    
    if ($message['nguoi_nhan'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }
    
    // Mark as read
    $stmt = $pdo->prepare("UPDATE tin_nhan SET trang_thai = 'da_doc' WHERE ma_tin_nhan = ?");
    $stmt->execute([$messageId]);
    
    echo json_encode(['success' => true, 'message' => 'Đã đánh dấu đã đọc']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
