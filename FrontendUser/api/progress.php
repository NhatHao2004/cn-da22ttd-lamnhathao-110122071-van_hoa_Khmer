<?php
/**
 * API: Learning Progress
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

// GET - Get progress
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lessonId = intval($_GET['lesson_id'] ?? 0);
    
    if ($lessonId) {
        // Get specific lesson progress
        $stmt = $pdo->prepare("SELECT * FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND ma_bai_hoc = ?");
        $stmt->execute([$userId, $lessonId]);
        $progress = $stmt->fetch();
    } else {
        // Get all progress
        $stmt = $pdo->prepare("
            SELECT t.*, b.tieu_de, b.cap_do 
            FROM tien_trinh_hoc_tap t 
            JOIN bai_hoc b ON t.ma_bai_hoc = b.ma_bai_hoc 
            WHERE t.ma_nguoi_dung = ?
            ORDER BY t.ngay_hoan_thanh DESC
        ");
        $stmt->execute([$userId]);
        $progress = $stmt->fetchAll();
    }
    
    echo json_encode(['success' => true, 'data' => $progress]);
    exit;
}

// POST - Update progress
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $lessonId = intval($data['lesson_id'] ?? 0);
    $completed = (bool)($data['completed'] ?? false);
    
    if (!$lessonId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        exit;
    }
    
    // Get lesson info
    $stmt = $pdo->prepare("SELECT diem_thuong FROM bai_hoc WHERE ma_bai_hoc = ?");
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch();
    $points = $lesson['diem_thuong'] ?? 10;
    
    // Check existing progress
    $stmt = $pdo->prepare("SELECT * FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND ma_bai_hoc = ?");
    $stmt->execute([$userId, $lessonId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($completed && $existing['trang_thai'] !== 'hoan_thanh') {
            $stmt = $pdo->prepare("UPDATE tien_trinh_hoc_tap SET trang_thai = 'hoan_thanh', diem_so = ?, ngay_hoan_thanh = NOW() WHERE ma_tien_trinh = ?");
            $stmt->execute([$points, $existing['ma_tien_trinh']]);
            addUserPoints($userId, $points, 'Hoàn thành bài học');
            checkBadges($userId);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO tien_trinh_hoc_tap (ma_nguoi_dung, ma_bai_hoc, trang_thai, diem_so, ngay_bat_dau, ngay_hoan_thanh) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$userId, $lessonId, $completed ? 'hoan_thanh' : 'dang_hoc', $completed ? $points : 0, $completed ? date('Y-m-d H:i:s') : null]);
        
        if ($completed) {
            addUserPoints($userId, $points, 'Hoàn thành bài học');
            checkBadges($userId);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công', 'points' => $completed ? $points : 0]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
