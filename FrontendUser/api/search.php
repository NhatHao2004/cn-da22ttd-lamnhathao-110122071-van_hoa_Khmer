<?php
/**
 * API: Search
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$query = sanitize($_GET['q'] ?? '');
$type = sanitize($_GET['type'] ?? 'all');
$limit = min(20, intval($_GET['limit'] ?? 10));

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$pdo = getDBConnection();
$results = [];
$searchTerm = "%$query%";

// Search văn hóa
if ($type === 'all' || $type === 'vanhoa') {
    $stmt = $pdo->prepare("SELECT ma_van_hoa as id, tieu_de as title, 'vanhoa' as type FROM van_hoa WHERE trang_thai = 'xuat_ban' AND tieu_de LIKE ? LIMIT ?");
    $stmt->execute([$searchTerm, $limit]);
    $results = array_merge($results, $stmt->fetchAll());
}

// Search chùa
if ($type === 'all' || $type === 'chua') {
    $stmt = $pdo->prepare("SELECT ma_chua as id, ten_chua as title, 'chua' as type FROM chua_khmer WHERE trang_thai = 'hoat_dong' AND ten_chua LIKE ? LIMIT ?");
    $stmt->execute([$searchTerm, $limit]);
    $results = array_merge($results, $stmt->fetchAll());
}

// Search lễ hội
if ($type === 'all' || $type === 'lehoi') {
    $stmt = $pdo->prepare("SELECT ma_le_hoi as id, ten_le_hoi as title, 'lehoi' as type FROM le_hoi WHERE trang_thai = 'hien_thi' AND ten_le_hoi LIKE ? LIMIT ?");
    $stmt->execute([$searchTerm, $limit]);
    $results = array_merge($results, $stmt->fetchAll());
}

// Search truyện
if ($type === 'all' || $type === 'truyen') {
    $stmt = $pdo->prepare("SELECT ma_truyen as id, tieu_de as title, 'truyen' as type FROM truyen_dan_gian WHERE trang_thai = 'hien_thi' AND tieu_de LIKE ? LIMIT ?");
    $stmt->execute([$searchTerm, $limit]);
    $results = array_merge($results, $stmt->fetchAll());
}

echo json_encode(['success' => true, 'data' => array_slice($results, 0, $limit)]);
?>
