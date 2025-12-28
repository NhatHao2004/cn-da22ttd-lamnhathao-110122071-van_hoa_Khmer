<?php
/**
 * API: Authentication
 */
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'check':
        // Check if user is logged in
        echo json_encode([
            'success' => true,
            'logged_in' => isset($_SESSION['user_id']),
            'user' => isset($_SESSION['user_id']) ? [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? ''
            ] : null
        ]);
        break;
        
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE email = ? AND trang_thai = 'hoat_dong'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mat_khau'])) {
            $_SESSION['user_id'] = $user['ma_nguoi_dung'];
            $_SESSION['user_name'] = $user['ho_ten'];
            
            $pdo->prepare("UPDATE nguoi_dung SET lan_dang_nhap_cuoi = NOW() WHERE ma_nguoi_dung = ?")->execute([$user['ma_nguoi_dung']]);
            logActivity($user['ma_nguoi_dung'], 'login', 'Đăng nhập qua API');
            
            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'user' => ['id' => $user['ma_nguoi_dung'], 'name' => $user['ho_ten']]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng']);
        }
        break;
        
    case 'logout':
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'Đăng xuất qua API');
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Đăng xuất thành công']);
        break;
        
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $hoTen = sanitize($data['ho_ten'] ?? '');
        $email = sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        if (!$hoTen || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
            exit;
        }
        
        // Check existing email
        $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
            exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $tenDangNhap = strtolower(str_replace(' ', '', $hoTen)) . rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, mat_khau, ngay_tao, trang_thai) VALUES (?, ?, ?, ?, NOW(), 'hoat_dong')");
        
        if ($stmt->execute([$tenDangNhap, $hoTen, $email, $hashedPassword])) {
            $userId = $pdo->lastInsertId();
            logActivity($userId, 'register', 'Đăng ký qua API');
            echo json_encode(['success' => true, 'message' => 'Đăng ký thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
