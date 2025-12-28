<?php
/**
 * API thích bài viết nhóm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập!'
    ]);
    exit;
}

// Lấy dữ liệu
$data = json_decode(file_get_contents('php://input'), true);
$ma_bai_viet = $data['ma_bai_viet'] ?? 0;
$ma_nguoi_dung = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;

if (!$ma_nguoi_dung) {
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy thông tin người dùng!'
    ]);
    exit;
}

if (!$ma_bai_viet) {
    echo json_encode([
        'success' => false,
        'message' => 'Thông tin bài viết không hợp lệ!'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Kiểm tra bài viết tồn tại
    $stmt = $pdo->prepare("SELECT * FROM bai_viet_nhom WHERE ma_bai_viet = ? AND trang_thai = 'hien_thi'");
    $stmt->execute([$ma_bai_viet]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Bài viết không tồn tại!'
        ]);
        exit;
    }
    
    // Kiểm tra đã thích chưa
    $checkStmt = $pdo->prepare("SELECT * FROM luot_thich_bai_viet_nhom WHERE ma_bai_viet = ? AND ma_nguoi_dung = ?");
    $checkStmt->execute([$ma_bai_viet, $ma_nguoi_dung]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Bỏ thích
        $deleteStmt = $pdo->prepare("DELETE FROM luot_thich_bai_viet_nhom WHERE ma_luot_thich = ?");
        $deleteStmt->execute([$existing['ma_luot_thich']]);
        
        // Cập nhật số lượt thích
        $updateStmt = $pdo->prepare("UPDATE bai_viet_nhom SET so_luot_thich = GREATEST(so_luot_thich - 1, 0) WHERE ma_bai_viet = ?");
        $updateStmt->execute([$ma_bai_viet]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã bỏ thích!',
            'action' => 'unliked'
        ]);
    } else {
        // Thích
        $insertStmt = $pdo->prepare("
            INSERT INTO luot_thich_bai_viet_nhom (ma_bai_viet, ma_nguoi_dung, loai_cam_xuc, ngay_thich) 
            VALUES (?, ?, 'thich', NOW())
        ");
        $insertStmt->execute([$ma_bai_viet, $ma_nguoi_dung]);
        
        // Cập nhật số lượt thích
        $updateStmt = $pdo->prepare("UPDATE bai_viet_nhom SET so_luot_thich = so_luot_thich + 1 WHERE ma_bai_viet = ?");
        $updateStmt->execute([$ma_bai_viet]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã thích bài viết!',
            'action' => 'liked'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error liking post: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
