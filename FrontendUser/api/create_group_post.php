<?php
/**
 * API: Tạo bài viết trong nhóm
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
$ma_nhom = $_POST['ma_nhom'] ?? 0;
$tieu_de = trim($_POST['tieu_de'] ?? '');
$noi_dung = trim($_POST['noi_dung'] ?? '');
$ma_nguoi_dung = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;

if (!$ma_nguoi_dung) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng!']);
    exit;
}

// Validate
if (!$ma_nhom || !$noi_dung) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Debug: Log thông tin nhận được
    error_log("=== CREATE POST DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("ma_nhom: $ma_nhom");
    error_log("ma_nguoi_dung: $ma_nguoi_dung");
    error_log("tieu_de: $tieu_de");
    error_log("noi_dung: $noi_dung");
    
    // Kiểm tra người dùng có phải thành viên nhóm không
    $checkStmt = $pdo->prepare("
        SELECT * FROM thanh_vien_nhom 
        WHERE ma_nhom = ? AND ma_nguoi_dung = ? AND trang_thai = 'hoat_dong'
    ");
    $checkStmt->execute([$ma_nhom, $ma_nguoi_dung]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Bạn không phải thành viên nhóm này!']);
        exit;
    }
    
    // Thêm bài viết
    $stmt = $pdo->prepare("
        INSERT INTO bai_viet_nhom (ma_nhom, ma_nguoi_dung, tieu_de, noi_dung, ngay_dang, trang_thai)
        VALUES (?, ?, ?, ?, NOW(), 'hien_thi')
    ");
    $stmt->execute([$ma_nhom, $ma_nguoi_dung, $tieu_de, $noi_dung]);
    
    $post_id = $pdo->lastInsertId();
    
    // Xử lý upload ảnh
    $uploaded_images = [];
    if (!empty($_FILES['images'])) {
        error_log("Processing images upload...");
        $upload_dir = __DIR__ . '/../uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            error_log("Created directory: $upload_dir");
        }
        
        $files = $_FILES['images'];
        $file_count = count($files['name']);
        error_log("Number of images: $file_count");
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                error_log("Processing image $i: $name (ext: $ext)");
                
                // Validate image
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $new_name = uniqid() . '_' . time() . '.' . $ext;
                    $destination = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images[] = $new_name;
                        error_log("Image uploaded successfully: $new_name");
                    } else {
                        error_log("Failed to move uploaded file: $name");
                    }
                } else {
                    error_log("Invalid image extension: $ext");
                }
            } else {
                error_log("Upload error for image $i: " . $files['error'][$i]);
            }
        }
    } else {
        error_log("No images in request");
    }
    
    // Xử lý upload tài liệu
    $uploaded_files = [];
    if (!empty($_FILES['files'])) {
        error_log("Processing files upload...");
        $upload_dir = __DIR__ . '/../uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            error_log("Created directory: $upload_dir");
        }
        
        $files = $_FILES['files'];
        $file_count = count($files['name']);
        error_log("Number of files: $file_count");
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                error_log("Processing file $i: $name (ext: $ext)");
                
                // Validate file
                $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
                if (in_array($ext, $allowed)) {
                    $new_name = uniqid() . '_' . time() . '.' . $ext;
                    $destination = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_files[] = [
                            'name' => $name,
                            'file' => $new_name
                        ];
                        error_log("File uploaded successfully: $new_name");
                    } else {
                        error_log("Failed to move uploaded file: $name");
                    }
                } else {
                    error_log("Invalid file extension: $ext");
                }
            } else {
                error_log("Upload error for file $i: " . $files['error'][$i]);
            }
        }
    } else {
        error_log("No files in request");
    }
    
    // Cập nhật bài viết với ảnh và tài liệu
    if (!empty($uploaded_images) || !empty($uploaded_files)) {
        error_log("Updating post with attachments...");
        error_log("Images: " . json_encode($uploaded_images));
        error_log("Files: " . json_encode($uploaded_files));
        
        $updatePostStmt = $pdo->prepare("
            UPDATE bai_viet_nhom 
            SET anh_dinh_kem = ?, tai_lieu_dinh_kem = ?
            WHERE ma_bai_viet = ?
        ");
        $result = $updatePostStmt->execute([
            !empty($uploaded_images) ? json_encode($uploaded_images) : null,
            !empty($uploaded_files) ? json_encode($uploaded_files) : null,
            $post_id
        ]);
        error_log("Update result: " . ($result ? "SUCCESS" : "FAILED"));
    } else {
        error_log("No attachments to update");
    }
    
    // Cập nhật số bài viết trong nhóm
    $updateStmt = $pdo->prepare("
        UPDATE nhom_hoc_tap 
        SET so_bai_viet = so_bai_viet + 1 
        WHERE ma_nhom = ?
    ");
    $updateStmt->execute([$ma_nhom]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã đăng bài viết thành công!',
        'post_id' => $post_id,
        'images' => $uploaded_images,
        'files' => $uploaded_files
    ]);
    
} catch (Exception $e) {
    error_log("Error creating post: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi!']);
}
