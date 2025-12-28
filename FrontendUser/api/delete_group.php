<?php
/**
 * API xóa nhóm học tập
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Log request
error_log("=== DELETE GROUP REQUEST ===");
error_log("Session: " . print_r($_SESSION, true));
error_log("POST data: " . file_get_contents('php://input'));

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    error_log("User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để xóa nhóm!'
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
$ma_nguoi_dung = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;

if (!$ma_nguoi_dung) {
    error_log("Cannot get user ID from session");
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
    
    // Kiểm tra nhóm tồn tại và người dùng có phải là người tạo không
    $stmt = $pdo->prepare("SELECT * FROM nhom_hoc_tap WHERE ma_nhom = ? AND trang_thai IN ('hoat_dong', 'cong_khai')");
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
    error_log("Creator ID: " . $group['ma_nguoi_tao'] . ", Current user: $ma_nguoi_dung");
    
    // Kiểm tra quyền xóa - chỉ người tạo mới được xóa
    if ($group['ma_nguoi_tao'] != $ma_nguoi_dung) {
        error_log("User is not the creator");
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền xóa nhóm này! Chỉ người tạo nhóm mới có thể xóa.'
        ]);
        exit;
    }
    
    // Bắt đầu transaction
    $pdo->beginTransaction();
    
    try {
        // Xóa các bảng liên quan (soft delete - chuyển trạng thái thành 'da_xoa')
        
        // 1. Cập nhật trạng thái nhóm thành 'da_xoa'
        $updateGroupStmt = $pdo->prepare("UPDATE nhom_hoc_tap SET trang_thai = 'da_xoa' WHERE ma_nhom = ?");
        $updateGroupStmt->execute([$ma_nhom]);
        error_log("Updated group status to 'da_xoa'");
        
        // Kiểm tra xem có update được không
        if ($updateGroupStmt->rowCount() === 0) {
            throw new Exception("Không thể cập nhật trạng thái nhóm");
        }
        
        // 2. Xóa thành viên (nếu có bảng)
        try {
            $deleteMembersStmt = $pdo->prepare("DELETE FROM thanh_vien_nhom WHERE ma_nhom = ?");
            $deleteMembersStmt->execute([$ma_nhom]);
            error_log("Deleted members");
        } catch (PDOException $e) {
            error_log("Error deleting members (table may not exist): " . $e->getMessage());
        }
        
        // 3. Xóa bài viết (nếu có bảng)
        try {
            $deletePostsStmt = $pdo->prepare("UPDATE bai_viet_nhom SET trang_thai = 'da_xoa' WHERE ma_nhom = ?");
            $deletePostsStmt->execute([$ma_nhom]);
            error_log("Updated posts status to 'da_xoa'");
        } catch (PDOException $e) {
            error_log("Error updating posts (table may not exist): " . $e->getMessage());
        }
        
        // Commit transaction
        $pdo->commit();
        error_log("Transaction committed successfully");
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa nhóm thành công!'
        ]);
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $pdo->rollBack();
        error_log("Transaction rolled back: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error deleting group: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi xóa nhóm: ' . $e->getMessage()
    ]);
}
