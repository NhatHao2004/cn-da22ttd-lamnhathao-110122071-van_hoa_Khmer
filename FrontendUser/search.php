<?php
/**
 * Search API - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

header('Content-Type: application/json');
require_once 'config/database.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$db = Database::getInstance();
$searchTerm = "%$query%";
$results = [];

// Tìm trong văn hóa
$vanHoa = $db->query(
    "SELECT id, tieu_de as title, 'Văn hóa' as type, 'fas fa-book-open' as icon 
     FROM van_hoa 
     WHERE trang_thai = 'xuat_ban' AND tieu_de LIKE ? 
     LIMIT 3",
    [$searchTerm]
);

foreach ($vanHoa as $item) {
    $results[] = [
        'title' => $item['title'],
        'type' => $item['type'],
        'icon' => $item['icon'],
        'url' => 'van-hoa-chi-tiet.php?id=' . $item['id']
    ];
}

// Tìm trong chùa
$chua = $db->query(
    "SELECT id, ten_chua as title, 'Chùa Khmer' as type, 'fas fa-place-of-worship' as icon 
     FROM chua_khmer 
     WHERE trang_thai = 'hoat_dong' AND (ten_chua LIKE ? OR ten_tieng_khmer LIKE ?) 
     LIMIT 3",
    [$searchTerm, $searchTerm]
);

foreach ($chua as $item) {
    $results[] = [
        'title' => $item['title'],
        'type' => $item['type'],
        'icon' => $item['icon'],
        'url' => 'chua-khmer-chi-tiet.php?id=' . $item['id']
    ];
}

// Tìm trong lễ hội
$leHoi = $db->query(
    "SELECT id, ten_le_hoi as title, 'Lễ hội' as type, 'fas fa-calendar-alt' as icon 
     FROM le_hoi 
     WHERE trang_thai = 'hien_thi' AND (ten_le_hoi LIKE ? OR ten_tieng_khmer LIKE ?) 
     LIMIT 3",
    [$searchTerm, $searchTerm]
);

foreach ($leHoi as $item) {
    $results[] = [
        'title' => $item['title'],
        'type' => $item['type'],
        'icon' => $item['icon'],
        'url' => 'le-hoi-chi-tiet.php?id=' . $item['id']
    ];
}

// Tìm trong bài học
$baiHoc = $db->query(
    "SELECT id, tieu_de as title, 'Bài học' as type, 'fas fa-graduation-cap' as icon 
     FROM bai_hoc 
     WHERE trang_thai = 'xuat_ban' AND tieu_de LIKE ? 
     LIMIT 3",
    [$searchTerm]
);

foreach ($baiHoc as $item) {
    $results[] = [
        'title' => $item['title'],
        'type' => $item['type'],
        'icon' => $item['icon'],
        'url' => 'bai-hoc-chi-tiet.php?id=' . $item['id']
    ];
}

echo json_encode(['results' => $results]);
