<?php
/**
 * API rời nhóm học tập
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

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
$ma_nhom = $data['ma_nhom'] ?? 0;
$ma_nguoi_dung = $_SESSION['user_id'] ?? $_SESSION['ma_nguoi_dung'] ?? null;

if (!$ma_nguoi_dung) {
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xác định người dùng!'
    ]);
    exit;
}

if (!$ma_nhom) {
    echo json_encode([
        'success' => false,
        'message' => 'Thông tin nhóm không hợp lệ!'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Kiểm tra là thành viên
    $stmt = $pdo->prepare("SELECT * FROM thanh_vien_nhom WHERE ma_nhom = ? AND ma_nguoi_dung = ? AND trang_thai = 'hoat_dong'");
    $stmt->execute([$ma_nhom, $ma_nguoi_dung]);
    $member = $stmt->fetch();
    
    if (!$member) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không phải là thành viên của nhóm này!'
        ]);
        exit;
    }
    
    // Kiểm tra không phải người tạo nhóm
    $groupStmt = $pdo->prepare("SELECT ma_nguoi_tao FROM nhom_hoc_tap WHERE ma_nhom = ?");
    $groupStmt->execute([$ma_nhom]);
    $group = $groupStmt->fetch();
    
    if ($group && $group['ma_nguoi_tao'] == $ma_nguoi_dung) {
        echo json_encode([
            'success' => false,
            'message' => 'Người tạo nhóm không thể rời nhóm!'
        ]);
        exit;
    }
    
    // Cập nhật trạng thái
    $updateStmt = $pdo->prepare("UPDATE thanh_vien_nhom SET trang_thai = 'da_roi' WHERE ma_thanh_vien_nhom = ?");
    $updateStmt->execute([$member['ma_thanh_vien_nhom']]);
    
    // Giảm số thành viên trong nhóm
    $countStmt = $pdo->prepare("UPDATE nhom_hoc_tap SET so_thanh_vien = GREATEST(0, so_thanh_vien - 1) WHERE ma_nhom = ?");
    $countStmt->execute([$ma_nhom]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã rời khỏi nhóm!'
    ]);
    
} catch (Exception $e) {
    error_log("Error leaving group: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi rời nhóm!'
    ]);
}
