<?php
/**
 * API tham gia nhóm học tập
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Log request
error_log("=== JOIN GROUP REQUEST ===");
error_log("Session: " . print_r($_SESSION, true));
error_log("POST data: " . file_get_contents('php://input'));

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    error_log("User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để tham gia nhóm!'
    ]);
    exit;
}

// Lấy dữ liệu
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ!'
    ]);
    exit;
}

$ma_nhom = $data['ma_nhom'] ?? 0;
$ma_nguoi_dung = $_SESSION['user_id'] ?? $_SESSION['ma_nguoi_dung'] ?? null;

if (!$ma_nguoi_dung) {
    error_log("Cannot get user ID from session: " . print_r($_SESSION, true));
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xác định người dùng. Vui lòng đăng nhập lại!'
    ]);
    exit;
}

error_log("ma_nhom: $ma_nhom, ma_nguoi_dung: $ma_nguoi_dung");

if (!$ma_nhom) {
    error_log("Invalid ma_nhom");
    echo json_encode([
        'success' => false,
        'message' => 'Thông tin nhóm không hợp lệ!'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    error_log("Database connected");
    
    // Kiểm tra nhóm tồn tại
    $stmt = $pdo->prepare("SELECT * FROM nhom_hoc_tap WHERE ma_nhom = ? AND trang_thai = 'hoat_dong'");
    $stmt->execute([$ma_nhom]);
    $group = $stmt->fetch();
    
    if (!$group) {
        error_log("Group not found or inactive");
        echo json_encode([
            'success' => false,
            'message' => 'Nhóm không tồn tại!'
        ]);
        exit;
    }
    
    error_log("Group found: " . $group['ten_nhom']);
    
    // Kiểm tra đã là thành viên chưa
    $checkStmt = $pdo->prepare("SELECT * FROM thanh_vien_nhom WHERE ma_nhom = ? AND ma_nguoi_dung = ?");
    $checkStmt->execute([$ma_nhom, $ma_nguoi_dung]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        error_log("User already member, status: " . $existing['trang_thai']);
        if ($existing['trang_thai'] === 'hoat_dong') {
            echo json_encode([
                'success' => false,
                'message' => 'Bạn đã là thành viên của nhóm này!'
            ]);
            exit;
        } else {
            // Cập nhật lại trạng thái
            $updateStmt = $pdo->prepare("UPDATE thanh_vien_nhom SET trang_thai = 'hoat_dong', ngay_tham_gia = NOW() WHERE ma_thanh_vien_nhom = ?");
            $result = $updateStmt->execute([$existing['ma_thanh_vien_nhom']]);
            error_log("Update member status result: " . ($result ? 'success' : 'failed'));
        }
    } else {
        // Thêm thành viên mới
        error_log("Adding new member");
        $insertStmt = $pdo->prepare("
            INSERT INTO thanh_vien_nhom (ma_nhom, ma_nguoi_dung, vai_tro, trang_thai) 
            VALUES (?, ?, 'thanh_vien', 'hoat_dong')
        ");
        $result = $insertStmt->execute([$ma_nhom, $ma_nguoi_dung]);
        
        if (!$result) {
            error_log("Insert member failed: " . print_r($insertStmt->errorInfo(), true));
            throw new Exception("Failed to add member");
        }
        error_log("Member added successfully");
    }
    
    // Cập nhật số thành viên trong nhóm
    $countStmt = $pdo->prepare("UPDATE nhom_hoc_tap SET so_thanh_vien = so_thanh_vien + 1 WHERE ma_nhom = ?");
    $countStmt->execute([$ma_nhom]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tham gia nhóm thành công!'
    ]);
    
} catch (Exception $e) {
    error_log("Error joining group: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi tham gia nhóm: ' . $e->getMessage()
    ]);
}
