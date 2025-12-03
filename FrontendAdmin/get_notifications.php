<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$admin_id = $_SESSION['admin_id'];

// Lấy thông báo
$notifications_query = "
    SELECT 
        ma_thong_bao,
        tieu_de,
        noi_dung,
        loai,
        lien_ket,
        trang_thai,
        CASE 
            WHEN TIMESTAMPDIFF(SECOND, ngay_tao, NOW()) < 60 
                THEN 'Vừa xong'
            WHEN TIMESTAMPDIFF(MINUTE, ngay_tao, NOW()) < 60 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE, ngay_tao, NOW()), ' phút trước')
            WHEN TIMESTAMPDIFF(HOUR, ngay_tao, NOW()) < 24 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, ngay_tao, NOW()), ' giờ trước')
            WHEN TIMESTAMPDIFF(DAY, ngay_tao, NOW()) < 7 
                THEN CONCAT(TIMESTAMPDIFF(DAY, ngay_tao, NOW()), ' ngày trước')
            ELSE DATE_FORMAT(ngay_tao, '%d/%m/%Y')
        END as thoi_gian_hien_thi
    FROM thong_bao
    WHERE ma_qtv = ? OR ma_qtv IS NULL
    ORDER BY 
        CASE WHEN trang_thai = 'chua_doc' THEN 0 ELSE 1 END,
        ngay_tao DESC 
    LIMIT 10
";

$notifications = $db->query($notifications_query, [$admin_id]) ?: [];

// Đếm số thông báo chưa đọc
$unread_count = $db->querySingle(
    "SELECT COUNT(*) as count FROM thong_bao WHERE (ma_qtv = ? OR ma_qtv IS NULL) AND trang_thai = 'chua_doc'",
    [$admin_id]
)['count'] ?? 0;

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int)$unread_count
], JSON_UNESCAPED_UNICODE);
