<?php
/**
 * API: Like/Unlike Comment
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
    
    // Check which table to use
    $checkForum = $pdo->prepare("SELECT ma_binh_luan FROM binh_luan_dien_dan WHERE ma_binh_luan = ?");
    $checkForum->execute([$commentId]);
    $isForum = $checkForum->fetch() !== false;
    
    // Check if user already liked this comment
    $stmt = $pdo->prepare("SELECT * FROM luot_thich_binh_luan WHERE ma_binh_luan = ? AND ma_nguoi_dung = ?");
    $stmt->execute([$commentId, $userId]);
    $existingLike = $stmt->fetch();
    
    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM luot_thich_binh_luan WHERE ma_binh_luan = ? AND ma_nguoi_dung = ?");
        $stmt->execute([$commentId, $userId]);
        
        // Get new like count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM luot_thich_binh_luan WHERE ma_binh_luan = ?");
        $stmt->execute([$commentId]);
        $likeCount = $stmt->fetch()['count'];
        
        // Update comment table
        if ($isForum) {
            $pdo->prepare("UPDATE binh_luan_dien_dan SET so_like = ? WHERE ma_binh_luan = ?")->execute([$likeCount, $commentId]);
        } else {
            $pdo->prepare("UPDATE binh_luan SET so_like = ? WHERE ma_binh_luan = ?")->execute([$likeCount, $commentId]);
        }
        
        echo json_encode([
            'success' => true,
            'liked' => false,
            'likes' => $likeCount,
            'message' => 'Đã bỏ thích'
        ]);
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO luot_thich_binh_luan (ma_binh_luan, ma_nguoi_dung, ngay_tao) VALUES (?, ?, NOW())");
        $stmt->execute([$commentId, $userId]);
        
        // Get new like count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM luot_thich_binh_luan WHERE ma_binh_luan = ?");
        $stmt->execute([$commentId]);
        $likeCount = $stmt->fetch()['count'];
        
        // Update comment table
        if ($isForum) {
            $pdo->prepare("UPDATE binh_luan_dien_dan SET so_like = ? WHERE ma_binh_luan = ?")->execute([$likeCount, $commentId]);
        } else {
            $pdo->prepare("UPDATE binh_luan SET so_like = ? WHERE ma_binh_luan = ?")->execute([$likeCount, $commentId]);
        }
        
        echo json_encode([
            'success' => true,
            'liked' => true,
            'likes' => $likeCount,
            'message' => 'Đã thích bình luận'
        ]);
    }
} catch (Exception $e) {
    error_log("Like comment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
