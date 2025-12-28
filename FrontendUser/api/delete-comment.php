<?php
/**
 * API: Delete Comment
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Support both POST data and JSON
$input = json_decode(file_get_contents('php://input'), true);
$commentId = intval($input['comment_id'] ?? $_POST['comment_id'] ?? 0);

if (!$commentId) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ']);
    exit;
}

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Check forum table first
    $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM binh_luan_dien_dan WHERE ma_binh_luan = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    $isForum = $comment !== false;
    
    // If not forum, check general comment table
    if (!$isForum) {
        $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM binh_luan WHERE ma_binh_luan = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
    }
    
    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại']);
        exit;
    }
    
    if ($comment['ma_nguoi_dung'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này']);
        exit;
    }
    
    // Delete replies first
    if ($isForum) {
        $pdo->prepare("DELETE FROM binh_luan_dien_dan WHERE ma_binh_luan_cha = ?")->execute([$commentId]);
    } else {
        $pdo->prepare("DELETE FROM binh_luan WHERE ma_binh_luan_cha = ?")->execute([$commentId]);
    }
    
    // Delete likes
    $stmt = $pdo->prepare("DELETE FROM luot_thich_binh_luan WHERE ma_binh_luan = ?");
    $stmt->execute([$commentId]);
    
    // Delete comment
    if ($isForum) {
        $stmt = $pdo->prepare("DELETE FROM binh_luan_dien_dan WHERE ma_binh_luan = ?");
    } else {
        $stmt = $pdo->prepare("DELETE FROM binh_luan WHERE ma_binh_luan = ?");
    }
    $stmt->execute([$commentId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa bình luận'
    ]);
} catch (Exception $e) {
    error_log("Delete comment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
