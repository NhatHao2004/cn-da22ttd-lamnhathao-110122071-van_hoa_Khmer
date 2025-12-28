<?php
/**
 * API: Edit Comment
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
$noiDung = trim($input['noi_dung'] ?? $_POST['noi_dung'] ?? '');

if (!$commentId || strlen($noiDung) < 5) {
    echo json_encode(['success' => false, 'message' => 'Nội dung phải có ít nhất 5 ký tự']);
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
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa bình luận này']);
        exit;
    }
    
    // Update comment
    if ($isForum) {
        $stmt = $pdo->prepare("UPDATE binh_luan_dien_dan SET noi_dung = ?, ngay_cap_nhat = NOW() WHERE ma_binh_luan = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE binh_luan SET noi_dung = ?, ngay_cap_nhat = NOW() WHERE ma_binh_luan = ?");
    }
    $stmt->execute([$noiDung, $commentId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật bình luận',
        'noi_dung' => nl2br(htmlspecialchars($noiDung))
    ]);
} catch (Exception $e) {
    error_log("Edit comment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
