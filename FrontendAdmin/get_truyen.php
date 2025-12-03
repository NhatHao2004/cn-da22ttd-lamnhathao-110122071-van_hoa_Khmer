<?php
/**
 * API lấy thông tin truyện theo ID
 */
require_once 'config/database.php';
require_once 'models/TruyenDanGian.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$truyenModel = new TruyenDanGian();
$truyen = $truyenModel->getById($_GET['id']);

if ($truyen) {
    echo json_encode($truyen);
} else {
    echo json_encode(['error' => 'Truyện không tồn tại']);
}
