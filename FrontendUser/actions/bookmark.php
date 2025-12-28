<?php
/**
 * Bookmark/Save Article Action
 * Xử lý lưu và bỏ lưu bài viết
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này']);
    exit;
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$loaiDoiTuong = $data['loai_doi_tuong'] ?? '';
$maDoiTuong = intval($data['ma_doi_tuong'] ?? 0);

if (empty($action) || empty($loaiDoiTuong) || $maDoiTuong <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $pdo = getDBConnection();
    $currentUser = getCurrentUser();
    $maNguoiDung = $currentUser['ma_nguoi_dung'];
    
    if ($action === 'add') {
        // Kiểm tra xem đã lưu chưa
        $checkStmt = $pdo->prepare("SELECT ma_yeu_thich FROM yeu_thich WHERE ma_nguoi_dung = ? AND ma_doi_tuong = ? AND loai_doi_tuong = ?");
        $checkStmt->execute([$maNguoiDung, $maDoiTuong, $loaiDoiTuong]);
        
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã lưu bài viết này rồi']);
            exit;
        }
        
        // Thêm vào yêu thích
        $insertStmt = $pdo->prepare("INSERT INTO yeu_thich (ma_nguoi_dung, ma_doi_tuong, loai_doi_tuong, ngay_tao) VALUES (?, ?, ?, NOW())");
        $insertStmt->execute([$maNguoiDung, $maDoiTuong, $loaiDoiTuong]);
        
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Đã lưu bài viết']);
        
    } elseif ($action === 'remove') {
        // Xóa khỏi yêu thích
        $deleteStmt = $pdo->prepare("DELETE FROM yeu_thich WHERE ma_nguoi_dung = ? AND ma_doi_tuong = ? AND loai_doi_tuong = ?");
        $deleteStmt->execute([$maNguoiDung, $maDoiTuong, $loaiDoiTuong]);
        
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Đã bỏ lưu bài viết']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
    }
    
} catch (PDOException $e) {
    error_log("Bookmark error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại']);
}
