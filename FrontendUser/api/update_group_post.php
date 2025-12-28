<?php
/**
 * API: Cập nhật bài viết trong nhóm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

// Lấy dữ liệu từ POST (FormData)
$ma_bai_viet = $_POST['ma_bai_viet'] ?? 0;
$tieu_de = trim($_POST['tieu_de'] ?? '');
$noi_dung = trim($_POST['noi_dung'] ?? '');
$ma_nguoi_dung = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;

if (!$ma_nguoi_dung) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng!']);
    exit;
}

// Validate
if (!$ma_bai_viet || !$noi_dung) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Kiểm tra quyền sở hữu bài viết
    $checkStmt = $pdo->prepare("
        SELECT * FROM bai_viet_nhom 
        WHERE ma_bai_viet = ? AND ma_nguoi_dung = ?
    ");
    $checkStmt->execute([$ma_bai_viet, $ma_nguoi_dung]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa bài viết này!']);
        exit;
    }
    
    // Cập nhật bài viết
    $stmt = $pdo->prepare("
        UPDATE bai_viet_nhom 
        SET tieu_de = ?, noi_dung = ?, ngay_cap_nhat = NOW()
        WHERE ma_bai_viet = ?
    ");
    $stmt->execute([$tieu_de, $noi_dung, $ma_bai_viet]);
    
    // Xử lý upload ảnh mới (nếu có)
    $uploaded_images = [];
    if (!empty($_FILES['images'])) {
        $upload_dir = __DIR__ . '/../uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $files = $_FILES['images'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $new_name = uniqid() . '_' . time() . '.' . $ext;
                    $destination = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_name;
                    }
                }
            }
        }
        
        // Cập nhật ảnh nếu có upload mới
        if (!empty($uploaded_images)) {
            $updateImgStmt = $pdo->prepare("
                UPDATE bai_viet_nhom 
                SET anh_dinh_kem = ?
                WHERE ma_bai_viet = ?
            ");
            $updateImgStmt->execute([json_encode($uploaded_images), $ma_bai_viet]);
        }
    }
    
    // Xử lý upload tài liệu mới (nếu có)
    $uploaded_files = [];
    if (!empty($_FILES['files'])) {
        $upload_dir = __DIR__ . '/../uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $files = $_FILES['files'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
                if (in_array($ext, $allowed)) {
                    $new_name = uniqid() . '_' . time() . '.' . $ext;
                    $destination = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_files[] = [
                            'name' => $name,
                            'file' => $new_name
                        ];
                    }
                }
            }
        }
        
        // Cập nhật tài liệu nếu có upload mới
        if (!empty($uploaded_files)) {
            $updateFileStmt = $pdo->prepare("
                UPDATE bai_viet_nhom 
                SET tai_lieu_dinh_kem = ?
                WHERE ma_bai_viet = ?
            ");
            $updateFileStmt->execute([json_encode($uploaded_files), $ma_bai_viet]);
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã cập nhật bài viết thành công!'
    ]);
    
} catch (Exception $e) {
    error_log("Error updating post: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi!']);
}
