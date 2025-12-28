<?php
/**
 * AJAX Search Handler
 * Xử lý tìm kiếm nhanh
 */

session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Lấy query từ request
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập ít nhất 2 ký tự'
    ]);
    exit;
}

$db = Database::getInstance();
$results = [];

// Tìm kiếm trong văn hóa
$vanHoa = $db->query(
    "SELECT 'van_hoa' as type, ma_van_hoa as id, tieu_de as title, slug, anh_dai_dien as image, tom_tat as description, luot_xem as views
     FROM van_hoa 
     WHERE trang_thai = 'xuat_ban' 
     AND (tieu_de LIKE ? OR noi_dung LIKE ? OR tom_tat LIKE ?)
     ORDER BY luot_xem DESC 
     LIMIT 3",
    ["%$query%", "%$query%", "%$query%"]
);

// Tìm kiếm trong chùa khmer
$chuaKhmer = $db->query(
    "SELECT 'chua_khmer' as type, ma_chua as id, ten_chua as title, slug, anh_dai_dien as image, mo_ta as description, luot_xem as views
     FROM chua_khmer 
     WHERE trang_thai = 'hoat_dong' 
     AND (ten_chua LIKE ? OR dia_chi LIKE ? OR mo_ta LIKE ?)
     ORDER BY luot_xem DESC 
     LIMIT 3",
    ["%$query%", "%$query%", "%$query%"]
);

// Tìm kiếm trong lễ hội
$leHoi = $db->query(
    "SELECT 'le_hoi' as type, ma_le_hoi as id, ten_le_hoi as title, slug, anh_dai_dien as image, mo_ta as description, luot_xem as views
     FROM le_hoi 
     WHERE trang_thai = 'hien_thi' 
     AND (ten_le_hoi LIKE ? OR mo_ta LIKE ? OR dia_diem LIKE ?)
     ORDER BY ngay_bat_dau DESC 
     LIMIT 3",
    ["%$query%", "%$query%", "%$query%"]
);

// Tìm kiếm trong bài học
$baiHoc = $db->query(
    "SELECT 'bai_hoc' as type, ma_bai_hoc as id, tieu_de as title, slug, anh_minh_hoa as image, mo_ta as description, luot_hoc as views
     FROM bai_hoc 
     WHERE tieu_de LIKE ? OR mo_ta LIKE ? OR noi_dung LIKE ?
     ORDER BY luot_hoc DESC 
     LIMIT 3",
    ["%$query%", "%$query%", "%$query%"]
);

// Tìm kiếm trong truyện dân gian
$truyenDanGian = $db->query(
    "SELECT 'truyen_dan_gian' as type, ma_truyen as id, tieu_de as title, slug, anh_dai_dien as image, tom_tat as description, luot_xem as views
     FROM truyen_dan_gian 
     WHERE trang_thai = 'hien_thi' 
     AND (tieu_de LIKE ? OR tom_tat LIKE ? OR noi_dung LIKE ?)
     ORDER BY luot_xem DESC 
     LIMIT 3",
    ["%$query%", "%$query%", "%$query%"]
);

// Tổng hợp kết quả
$results = [
    'van_hoa' => $vanHoa ?: [],
    'chua_khmer' => $chuaKhmer ?: [],
    'le_hoi' => $leHoi ?: [],
    'bai_hoc' => $baiHoc ?: [],
    'truyen_dan_gian' => $truyenDanGian ?: []
];

$totalResults = count($vanHoa) + count($chuaKhmer) + count($leHoi) + count($baiHoc) + count($truyenDanGian);

echo json_encode([
    'success' => true,
    'query' => $query,
    'total' => $totalResults,
    'results' => $results
]);
