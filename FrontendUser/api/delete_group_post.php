<?php
/**
 * API: Xóa bài viết trong nhóm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

// Lấy dữ liệu
$data = json_decode(file_get_contents('php://input'), true);
$ma_bai_viet = $data['ma_bai_viet'] ?? 0;
$ma_nguoi_dung = $_SESSION['ma_nguoi_dung'];

// Validate
if (!$ma_bai_viet) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài viết!']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Kiểm tra quyền sở hữu bài viết
    $checkStmt = $pdo->prepare("
        SELECT ma_nhom FROM bai_viet_nhom 
        WHERE ma_bai_viet = ? AND ma_nguoi_dung = ?
    ");
    $checkStmt->execute([$ma_bai_viet, $ma_nguoi_dung]);
    $post = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bài viết này!']);
        exit;
    }
    
    // Xóa bài viết
    $stmt = $pdo->prepare("DELETE FROM bai_viet_nhom WHERE ma_bai_viet = ?");
    $stmt->execute([$ma_bai_viet]);
    
    // Cập nhật số bài viết trong nhóm
    $updateStmt = $pdo->prepare("
        UPDATE nhom_hoc_tap 
        SET so_bai_viet = GREATEST(0, so_bai_viet - 1)
        WHERE ma_nhom = ?
    ");
    $updateStmt->execute([$post['ma_nhom']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã xóa bài viết thành công!'
    ]);
    
} catch (Exception $e) {
    error_log("Error deleting post: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi!']);
}
