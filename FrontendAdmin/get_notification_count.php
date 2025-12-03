<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$admin_id = $_SESSION['admin_id'];

// Đếm số thông báo chưa đọc
$result = $db->querySingle(
    "SELECT COUNT(*) as count FROM thong_bao WHERE (ma_qtv = ? OR ma_qtv IS NULL) AND trang_thai = 'chua_doc'",
    [$admin_id]
);

echo json_encode([
    'success' => true,
    'count' => (int)($result['count'] ?? 0)
], JSON_UNESCAPED_UNICODE);
