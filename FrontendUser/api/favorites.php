<?php
/**
 * API: Favorites/Bookmarks
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $contentId = intval($data['id'] ?? 0);
    $type = sanitize($data['type'] ?? '');
    
    if (!$contentId || !$type) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        exit;
    }
    
    // Check if already bookmarked
    $stmt = $pdo->prepare("SELECT ma_yeu_thich FROM yeu_thich WHERE ma_nguoi_dung = ? AND ma_doi_tuong = ? AND loai_doi_tuong = ?");
    $stmt->execute([$userId, $contentId, $type]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove bookmark
        $stmt = $pdo->prepare("DELETE FROM yeu_thich WHERE ma_yeu_thich = ?");
        $stmt->execute([$existing['ma_yeu_thich']]);
        echo json_encode(['success' => true, 'action' => 'removed', 'bookmarked' => false, 'message' => 'Đã bỏ lưu']);
    } else {
        // Add bookmark
        $stmt = $pdo->prepare("INSERT INTO yeu_thich (ma_nguoi_dung, ma_doi_tuong, loai_doi_tuong, ngay_tao) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $contentId, $type]);
        echo json_encode(['success' => true, 'action' => 'added', 'bookmarked' => true, 'message' => 'Đã lưu thành công']);
    }
    exit;
}

// Handle GET request - list favorites
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = sanitize($_GET['type'] ?? '');
    
    $query = "SELECT y.*, 
              CASE 
                WHEN y.loai_doi_tuong = 'van_hoa' THEN (SELECT tieu_de FROM van_hoa WHERE ma_van_hoa = y.ma_doi_tuong)
                WHEN y.loai_doi_tuong = 'chua_khmer' THEN (SELECT ten_chua FROM chua_khmer WHERE ma_chua = y.ma_doi_tuong)
                WHEN y.loai_doi_tuong = 'truyen_dan_gian' THEN (SELECT tieu_de FROM truyen_dan_gian WHERE ma_truyen = y.ma_doi_tuong)
              END as title
              FROM yeu_thich y 
              WHERE y.ma_nguoi_dung = ?";
    
    $params = [$userId];
    
    if ($type) {
        $query .= " AND y.loai_doi_tuong = ?";
        $params[] = $type;
    }
    
    $query .= " ORDER BY y.ngay_tao DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $favorites = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $favorites]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
