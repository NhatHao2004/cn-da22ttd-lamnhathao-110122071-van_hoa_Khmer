<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$admin_id = $_SESSION['admin_id'];

// Đánh dấu tất cả thông báo đã đọc
$result = $db->execute(
    "UPDATE thong_bao SET trang_thai = 'da_doc' WHERE (ma_qtv = ? OR ma_qtv IS NULL) AND trang_thai = 'chua_doc'",
    [$admin_id]
);

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Đã đánh dấu tất cả đã đọc' : 'Có lỗi xảy ra'
], JSON_UNESCAPED_UNICODE);
