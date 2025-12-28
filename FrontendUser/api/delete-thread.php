<?php
/**
 * API xóa chủ đề thảo luận
 */
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi động session trước
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    // Chỉ chấp nhận POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login']);
        exit;
    }

    // Lấy dữ liệu từ POST hoặc JSON
    $threadId = intval($_POST['thread_id'] ?? 0);
    if (!$threadId) {
        $data = json_decode(file_get_contents('php://input'), true);
        $threadId = intval($data['thread_id'] ?? 0);
    }

    if (!$threadId) {
        echo json_encode(['success' => false, 'message' => 'Invalid thread ID: ' . $threadId]);
        exit;
    }

    $pdo = getDBConnection();
    
    // Kiểm tra quyền sở hữu
    $stmt = $pdo->prepare("SELECT ma_nguoi_tao FROM chu_de_thao_luan WHERE ma_chu_de = ?");
    $stmt->execute([$threadId]);
    $thread = $stmt->fetch();
    
    if (!$thread) {
        echo json_encode(['success' => false, 'message' => 'Thread not found with ID: ' . $threadId]);
        exit;
    }
    
    if ($thread['ma_nguoi_tao'] != $_SESSION['user_id']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Permission denied. Owner: ' . $thread['ma_nguoi_tao'] . ', Current user: ' . $_SESSION['user_id']
        ]);
        exit;
    }
    
    // Xóa chủ đề (cascade sẽ xóa bình luận)
    $deleteStmt = $pdo->prepare("DELETE FROM chu_de_thao_luan WHERE ma_chu_de = ?");
    $success = $deleteStmt->execute([$threadId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Thread deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete thread']);
    }
    
} catch (Exception $e) {
    error_log("Delete thread error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
