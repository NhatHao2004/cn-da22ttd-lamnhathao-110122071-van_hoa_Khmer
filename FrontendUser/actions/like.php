<?php
/**
 * Handler cho chức năng Like/Unlike
 */

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$contentId = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$contentType = isset($_POST['content_type']) ? $_POST['content_type'] : '';

// Validate
if ($contentId <= 0 || empty($contentType)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Các loại nội dung được phép
$allowedTypes = ['van_hoa', 'chua_khmer', 'le_hoi', 'bai_hoc', 'truyen_dan_gian'];
if (!in_array($contentType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Loại nội dung không hợp lệ']);
    exit;
}

try {
    // Kiểm tra đã like chưa
    $existing = $db->query(
        "SELECT id FROM yeu_thich 
         WHERE nguoi_dung_id = ? AND loai_noi_dung = ? AND noi_dung_id = ?",
        [$userId, $contentType, $contentId]
    );
    
    if ($existing && count($existing) > 0) {
        // Unlike - Xóa like
        $db->delete('yeu_thich', "nguoi_dung_id = $userId AND loai_noi_dung = '$contentType' AND noi_dung_id = $contentId");
        
        // Lấy số lượt thích mới
        $likeCount = $db->count('yeu_thich', "loai_noi_dung = '$contentType' AND noi_dung_id = $contentId");
        
        echo json_encode([
            'success' => true,
            'action' => 'unliked',
            'message' => 'Đã bỏ thích',
            'like_count' => $likeCount
        ]);
    } else {
        // Like - Thêm mới
        $data = [
            'nguoi_dung_id' => $userId,
            'loai_noi_dung' => $contentType,
            'noi_dung_id' => $contentId,
            'ngay_tao' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('yeu_thich', $data);
        
        // Lấy số lượt thích mới
        $likeCount = $db->count('yeu_thich', "loai_noi_dung = '$contentType' AND noi_dung_id = $contentId");
        
        echo json_encode([
            'success' => true,
            'action' => 'liked',
            'message' => 'Đã thích',
            'like_count' => $likeCount
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
