<?php
/**
 * API lấy thông tin lễ hội theo ID
 */
require_once 'config/database.php';
require_once 'models/LeHoi.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$lehoiModel = new LeHoi();
$lehoi = $lehoiModel->getById($_GET['id']);

if ($lehoi) {
    echo json_encode($lehoi);
} else {
    echo json_encode(['error' => 'Lễ hội không tồn tại']);
}
