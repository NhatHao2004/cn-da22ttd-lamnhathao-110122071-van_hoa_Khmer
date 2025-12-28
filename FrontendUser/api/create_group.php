<?php
/**
 * API tạo nhóm học tập mới
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Log request
error_log("=== CREATE GROUP REQUEST ===");
error_log("Session: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    error_log("User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để tạo nhóm!'
    ]);
    exit;
}

// Lấy dữ liệu từ POST (FormData)
$ten_nhom = trim($_POST['ten_nhom'] ?? '');
$ten_nhom_km = trim($_POST['ten_nhom_km'] ?? '');
$mo_ta = trim($_POST['mo_ta'] ?? '');
$mo_ta_km = trim($_POST['mo_ta_km'] ?? '');
$icon = trim($_POST['icon'] ?? 'fas fa-users');
$mau_sac = '#000000'; // Màu mặc định

// Lấy user ID từ session - thử nhiều key
$ma_nguoi_tao = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;

if (!$ma_nguoi_tao) {
    error_log("Cannot get user ID from session: " . print_r($_SESSION, true));
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xác định người dùng. Vui lòng đăng nhập lại!'
    ]);
    exit;
}

error_log("Parsed data - ten_nhom: $ten_nhom, mo_ta: $mo_ta, ma_nguoi_tao: $ma_nguoi_tao");

// Validate
if (empty($ten_nhom)) {
    error_log("Empty ten_nhom");
    echo json_encode([
        'success' => false,
        'message' => 'Tên nhóm không được để trống!'
    ]);
    exit;
}

// Mô tả không bắt buộc, nếu trống thì dùng giá trị mặc định
if (empty($mo_ta)) {
    $mo_ta = 'Nhóm học tập ' . $ten_nhom;
    error_log("Empty mo_ta, using default: $mo_ta");
}

// Xử lý upload ảnh banner
$anh_banner = null;
if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['hinh_anh'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Kiểm tra loại file
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!'
        ]);
        exit;
    }
    
    // Kiểm tra kích thước
    if ($file['size'] > $max_size) {
        echo json_encode([
            'success' => false,
            'message' => 'Kích thước file quá lớn! Tối đa 5MB.'
        ]);
        exit;
    }
    
    // Tạo tên file unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'group_' . time() . '_' . uniqid() . '.' . $extension;
    
    // Tạo thư mục nếu chưa tồn tại
    $upload_dir = __DIR__ . '/../uploads/group_banners/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $anh_banner = 'uploads/group_banners/' . $filename;
        error_log("Banner uploaded successfully: $anh_banner");
    } else {
        error_log("Failed to upload banner");
    }
}

try {
    $pdo = getDBConnection();
    error_log("Database connected");
    
    // Kiểm tra bảng tồn tại
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'nhom_hoc_tap'")->rowCount();
    if ($tableCheck === 0) {
        error_log("Table nhom_hoc_tap does not exist");
        echo json_encode([
            'success' => false,
            'message' => 'Bảng dữ liệu chưa được tạo. Vui lòng chạy file SQL để tạo bảng!'
        ]);
        exit;
    }
    
    // Kiểm tra tên nhóm đã tồn tại chưa
    $checkStmt = $pdo->prepare("SELECT ma_nhom FROM nhom_hoc_tap WHERE ten_nhom = ?");
    $checkStmt->execute([$ten_nhom]);
    if ($checkStmt->rowCount() > 0) {
        error_log("Group name already exists");
        echo json_encode([
            'success' => false,
            'message' => 'Tên nhóm đã tồn tại!'
        ]);
        exit;
    }
    
    // Lấy thứ tự cao nhất
    $maxOrderStmt = $pdo->query("SELECT MAX(thu_tu) as max_order FROM nhom_hoc_tap");
    $maxOrder = $maxOrderStmt->fetch(PDO::FETCH_ASSOC)['max_order'] ?? 0;
    $thu_tu = $maxOrder + 1;
    
    error_log("Inserting group with thu_tu: $thu_tu");
    
    // Thêm nhóm mới
    $insertStmt = $pdo->prepare("
        INSERT INTO nhom_hoc_tap (
            ten_nhom, 
            ten_nhom_km, 
            mo_ta, 
            mo_ta_km,
            anh_banner,
            icon, 
            mau_sac, 
            ma_nguoi_tao, 
            thu_tu,
            trang_thai,
            so_thanh_vien,
            so_bai_viet
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'hoat_dong', 1, 0)
    ");
    
    $result = $insertStmt->execute([
        $ten_nhom,
        $ten_nhom_km,
        $mo_ta,
        $mo_ta_km,
        $anh_banner,
        $icon,
        $mau_sac,
        $ma_nguoi_tao,
        $thu_tu
    ]);
    
    if (!$result) {
        error_log("Insert failed: " . print_r($insertStmt->errorInfo(), true));
        throw new Exception("Failed to insert group");
    }
    
    $ma_nhom = $pdo->lastInsertId();
    error_log("Group created with ID: $ma_nhom");
    
    // Tự động thêm người tạo vào nhóm với vai trò admin
    $memberStmt = $pdo->prepare("
        INSERT INTO thanh_vien_nhom (ma_nhom, ma_nguoi_dung, vai_tro, trang_thai) 
        VALUES (?, ?, 'quan_tri', 'hoat_dong')
    ");
    $memberResult = $memberStmt->execute([$ma_nhom, $ma_nguoi_tao]);
    
    if (!$memberResult) {
        error_log("Member insert failed: " . print_r($memberStmt->errorInfo(), true));
    }
    
    error_log("Group created successfully");
    echo json_encode([
        'success' => true,
        'message' => 'Tạo nhóm thành công!',
        'ma_nhom' => $ma_nhom
    ]);
    
} catch (Exception $e) {
    error_log("Error creating group: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi tạo nhóm: ' . $e->getMessage()
    ]);
}
