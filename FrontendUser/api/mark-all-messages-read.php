<?php
/**
 * API: Mark all messages as read
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

try {
    $pdo = getDBConnection();
    
    // Mark all messages as read for current user
    $stmt = $pdo->prepare("UPDATE tin_nhan SET trang_thai = 'da_doc' WHERE nguoi_nhan = ? AND trang_thai = 'chua_doc'");
    $stmt->execute([$_SESSION['user_id']]);
    
    $count = $stmt->rowCount();
    
    echo json_encode([
        'success' => true, 
        'message' => "Đã đánh dấu $count tin nhắn đã đọc",
        'count' => $count
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
