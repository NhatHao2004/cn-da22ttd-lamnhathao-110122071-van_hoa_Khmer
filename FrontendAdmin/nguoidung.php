<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/NguoiDung.php';
require_once 'includes/upload.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();
$nguoiDungModel = new NguoiDung();

// Xử lý các hành động với PRG Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'update_points':
                $points = (int)$_POST['diem_them'];
                if($nguoiDungModel->updatePoints($_POST['ma_nguoi_dung'], $points)) {
                    $_SESSION['flash_message'] = 'Cập nhật điểm thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi cập nhật điểm!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: nguoidung.php');
                exit;
                
            case 'delete':
                $ma_nguoi_dung = intval($_POST['ma_nguoi_dung']);
                
                // Lấy thông tin người dùng để xóa ảnh đại diện
                $user = $nguoiDungModel->getById($ma_nguoi_dung);
                
                // Xóa ảnh đại diện nếu có
                if ($user && !empty($user['anh_dai_dien'])) {
                    $imagePath = __DIR__ . '/../FrontendUser/' . $user['anh_dai_dien'];
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
                
                // Xóa người dùng
                if($nguoiDungModel->delete($ma_nguoi_dung)) {
                    $_SESSION['flash_message'] = 'Xóa người dùng thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa người dùng!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: nguoidung.php');
                exit;
                
            case 'edit':
                // Lấy thông tin người dùng hiện tại
                $currentUser = $nguoiDungModel->getById($_POST['ma_nguoi_dung']);
                $anh_dai_dien = $currentUser['anh_dai_dien'] ?? '';
                
                // Xử lý upload ảnh mới
                if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Upload vào thư mục FrontendUser/uploads/avatar để nhất quán
                    $uploadDir = __DIR__ . '/../FrontendUser/uploads/avatar/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $file = $_FILES['anh_dai_dien'];
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (in_array('image/' . $fileType, $allowedTypes) || in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $fileName = date('Ymd_His') . '_' . uniqid() . '.' . $fileType;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            // Xóa ảnh cũ nếu có
                            if ($anh_dai_dien && file_exists(__DIR__ . '/../FrontendUser/' . $anh_dai_dien)) {
                                @unlink(__DIR__ . '/../FrontendUser/' . $anh_dai_dien);
                            }
                            // Lưu đường dẫn tương đối (không có FrontendUser prefix)
                            $anh_dai_dien = 'uploads/avatar/' . $fileName;
                        } else {
                            throw new Exception('Không thể lưu file ảnh');
                        }
                    } else {
                        throw new Exception('Loại file không được phép. Chỉ chấp nhận: JPG, PNG, GIF, WEBP');
                    }
                }
                
                $data = [
                    'email' => $_POST['email'],
                    'ho_ten' => $_POST['ho_ten'] ?? '',
                    'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                    'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
                    'so_dien_thoai' => $_POST['so_dien_thoai'] ?? '',
                    'anh_dai_dien' => $anh_dai_dien,
                    'tong_diem' => $_POST['tong_diem'] ?? 0,
                    'cap_do' => $_POST['cap_do'] ?? 1,
                    'trang_thai' => $_POST['trang_thai'] ?? 'hoat_dong',
                ];
                // Chỉ cập nhật mật khẩu nếu có nhập
                if(!empty($_POST['mat_khau'])) {
                    $data['mat_khau'] = $_POST['mat_khau'];
                }
                if($nguoiDungModel->update($_POST['ma_nguoi_dung'], $data)) {
                    $_SESSION['flash_message'] = 'Cập nhật người dùng thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi cập nhật!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: nguoidung.php');
                exit;
                
            case 'toggle_status':
                $user = $nguoiDungModel->getById($_POST['ma_nguoi_dung']);
                if($user) {
                    $newStatus = $user['trang_thai'] === 'hoat_dong' ? 'bi_khoa' : 'hoat_dong';
                    if($nguoiDungModel->update($_POST['ma_nguoi_dung'], ['trang_thai' => $newStatus])) {
                        $_SESSION['flash_message'] = $newStatus === 'hoat_dong' ? 'Đã mở khóa người dùng!' : 'Đã khóa người dùng!';
                        $_SESSION['flash_type'] = 'success';
                    }
                }
                header('Location: nguoidung.php');
                exit;
                
            case 'delete':
                // Lấy thông tin người dùng để xóa ảnh
                $user = $nguoiDungModel->getById($_POST['ma_nguoi_dung']);
                if ($user && $user['anh_dai_dien'] && file_exists(__DIR__ . '/../FrontendUser/' . $user['anh_dai_dien'])) {
                    @unlink(__DIR__ . '/../FrontendUser/' . $user['anh_dai_dien']);
                }
                
                if($nguoiDungModel->delete($_POST['ma_nguoi_dung'])) {
                    $_SESSION['flash_message'] = 'Xóa người dùng thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: nguoidung.php');
                exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: nguoidung.php');
        exit;
    }
}

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách người dùng
$users = $nguoiDungModel->getAll(100);
if(!is_array($users)) {
    $users = [];
}

// Format ngày tạo - KHÔNG dùng reference để tránh lỗi trùng lặp
$processedUsers = [];
foreach($users as $user) {
    if(isset($user['ngay_tao'])) {
        $user['ngay_tao_fmt'] = date('d/m/Y H:i', strtotime($user['ngay_tao']));
    }
    if(isset($user['lan_dang_nhap_cuoi']) && $user['lan_dang_nhap_cuoi']) {
        $user['lan_dang_nhap_cuoi_fmt'] = date('d/m/Y H:i', strtotime($user['lan_dang_nhap_cuoi']));
    }
    $processedUsers[] = $user;
}
$users = $processedUsers;

// Thống kê
$total_users = $nguoiDungModel->count();
$active_users = $nguoiDungModel->count('hoat_dong');
$locked_users = $nguoiDungModel->count('bi_khoa');
$new_users_month = count(array_filter($users, function($u) {
    return isset($u['ngay_tao']) && strtotime($u['ngay_tao']) >= strtotime('-30 days');
}));

// Đếm thông báo chưa đọc
$unread_notifications = $db->querySingle(
    "SELECT COUNT(*) as count FROM thong_bao WHERE (ma_qtv = ? OR ma_qtv IS NULL) AND trang_thai = 'chua_doc'",
    [$_SESSION['admin_id']]
)['count'] ?? 0;

// Đếm tin nhắn chưa đọc
$unread_messages = $db->querySingle(
    "SELECT COUNT(*) as count FROM tin_nhan WHERE nguoi_nhan = ? AND trang_thai = 'chua_doc'",
    [$_SESSION['admin_id']]
)['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="description" content="Quản lý người dùng">
<meta name="theme-color" content="#6366f1">
<title>Quản lý Người dùng</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="admin-common-styles.css">
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif;}
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #818cf8;
    --secondary: #ec4899;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --dark: #1e293b;
    --dark-light: #334155;
    --gray: #64748b;
    --gray-light: #f1f5f9;
    --white: #ffffff;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
body {background:var(--gray-light); color:var(--dark); line-height:1.6;}

/* Layout */
.admin-wrapper {display:flex; min-height:100vh;}

/* Sidebar */
.sidebar {
    width:280px;
    background:var(--white);
    position:fixed;
    height:100vh;
    overflow-y:auto;
    box-shadow:var(--shadow-lg);
    z-index:1000;
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar::-webkit-scrollbar {width:6px;}
.sidebar::-webkit-scrollbar-track {background:transparent;}
.sidebar::-webkit-scrollbar-thumb {background:var(--gray); border-radius:10px;}
.sidebar-header {
    padding:28px 24px;
    border-bottom:1px solid var(--gray-light);
    background:var(--gradient-primary);
}
.sidebar-logo {
    display:flex;
    align-items:center;
    gap:14px;
}
.sidebar-logo-icon {
    width:48px;
    height:48px;
    background:var(--white);
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.5rem;
    color:var(--primary);
    box-shadow:var(--shadow);
}
.sidebar-logo-icon i {
    animation:spin 8s linear infinite;
}
@keyframes spin {
    from { transform:rotate(0deg); }
    to { transform:rotate(360deg); }
}
.sidebar-logo-text h2 {
    font-size:1.3rem;
    font-weight:800;
    color:var(--white);
    letter-spacing:-0.5px;
}
.sidebar-logo-text p {
    font-size:0.75rem;
    color:rgba(255,255,255,0.8);
    font-weight:500;
}
.sidebar-menu {padding:20px 12px;}
.menu-section {margin-bottom:28px;}
.menu-section-title {
    padding:0 16px 12px;
    font-size:0.7rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:1px;
    color:var(--gray);
}
.menu-item {
    padding:12px 16px;
    display:flex;
    align-items:center;
    gap:14px;
    cursor:pointer;
    transition:all 0.3s ease;
    border-radius:12px;
    margin-bottom:6px;
}
.menu-item:hover {
    background:var(--gray-light);
    transform:translateX(4px);
}
.menu-item.active {
    background:var(--gradient-primary);
    color:var(--white);
    box-shadow:var(--shadow);
}
.menu-item i {
    font-size:1.15rem;
    width:24px;
    text-align:center;
}
.menu-item span {
    font-size:0.95rem;
    font-weight:600;
}

/* Main Content */
.main-content {
    margin-left:280px;
    flex:1;
    min-height:100vh;
}

/* Topbar */
.topbar {
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(0,0,0,0.05);
    padding:20px 32px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:999;
    box-shadow:0 4px 20px rgba(0,0,0,0.04);
}
.topbar-left {
    display:flex;
    align-items:center;
    gap:20px;
}
.topbar-search {
    position:relative;
    width:420px;
}
.topbar-search i {
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);
    color:var(--gray);
}
.topbar-search input {
    width:100%;
    padding:14px 18px 14px 48px;
    border:2px solid transparent;
    border-radius:14px;
    background:var(--gray-light);
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.topbar-search input:focus {
    outline:none;
    border-color:var(--primary);
    background:var(--white);
    box-shadow:0 8px 24px rgba(99,102,241,0.12);
    transform:translateY(-1px);
}
.topbar-right {
    display:flex;
    align-items:center;
    gap:8px;
}
.topbar-action-icon {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:4px;
    padding:10px 16px;
    cursor:pointer;
    border-radius:14px;
    transition:all 0.3s ease;
}
.topbar-action-icon:hover {
    background:var(--gray-light);
    transform:translateY(-2px);
}
.topbar-action-icon .icon-wrapper {
    position:relative;
    width:44px;
    height:44px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius:12px;
    box-shadow:0 4px 12px rgba(102,126,234,0.25);
    transition:all 0.3s ease;
}
.topbar-action-icon:hover .icon-wrapper {
    transform:scale(1.08) rotate(-5deg);
    box-shadow:0 8px 20px rgba(102,126,234,0.4);
}
.topbar-action-icon .icon-wrapper i {
    font-size:1.1rem;
    color:var(--white);
}
.topbar-action-icon .icon-label {
    font-size:0.7rem;
    font-weight:600;
    color:var(--gray);
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.notification-badge {
    position:absolute;
    top:-6px;
    right:-6px;
    min-width:20px;
    height:20px;
    padding:0 6px;
    background:linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color:var(--white);
    font-size:0.7rem;
    font-weight:800;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    border:2.5px solid var(--white);
    box-shadow:0 2px 8px rgba(255,65,108,0.4);
}
.topbar-divider {
    width:1px;
    height:40px;
    background:linear-gradient(to bottom, transparent, var(--gray-light), transparent);
    margin:0 8px;
}
.admin-profile-enhanced {
    display:flex;
    align-items:center;
    gap:12px;
    padding:8px 14px 8px 8px;
    background:var(--white);
    border:2px solid var(--gray-light);
    border-radius:16px;
    cursor:pointer;
}
.profile-avatar-wrapper {position:relative;}
.profile-avatar {
    width:46px;
    height:46px;
    border-radius:14px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:1.05rem;
    box-shadow:0 4px 16px rgba(102,126,234,0.35);
    border:3px solid var(--white);
}
.online-status {
    position:absolute;
    bottom:0;
    right:0;
    width:14px;
    height:14px;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    border:3px solid var(--white);
    border-radius:50%;
    box-shadow:0 2px 6px rgba(16,185,129,0.4);
}
.profile-info {
    display:flex;
    flex-direction:column;
    gap:6px;
}
.profile-name {
    font-size:0.95rem;
    font-weight:700;
    color:var(--dark);
}
.profile-role {
    font-size:0.7rem;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:8px;
    text-transform:uppercase;
    letter-spacing:0.6px;
}
.profile-role.role-super-admin {
    background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color:#8b4513;
    border:1.5px solid #ffa500;
}
.profile-role.role-admin {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
}
.profile-role.role-editor {
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    color:var(--white);
}
.profile-arrow {
    font-size:0.75rem;
    color:var(--gray);
    margin-left:4px;
}

/* Content Area */
.content-area {padding:32px; max-width:1600px; margin:0 auto;}

/* Page Header */
.page-header {
    padding:40px 48px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius:24px;
    margin-bottom:32px;
    color:var(--white);
    position:relative;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(102, 126, 234, 0.3);
}
.page-header::before {
    content:'';
    position:absolute;
    right:-80px;
    top:-80px;
    width:250px;
    height:250px;
    background:radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius:50%;
}
.page-header::after {
    content:'';
    position:absolute;
    left:-60px;
    bottom:-60px;
    width:180px;
    height:180px;
    background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius:50%;
}
.page-header-content {
    position:relative;
    z-index:1;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.page-title-wrapper {
    display:flex;
    align-items:center;
    gap:20px;
}
.page-icon-wrapper {
    width:70px;
    height:70px;
    background:rgba(255, 255, 255, 0.2);
    backdrop-filter:blur(10px);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    box-shadow:0 8px 24px rgba(0, 0, 0, 0.15);
    animation:float 3s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform:translateY(0); }
    50% { transform:translateY(-10px); }
}
.page-title-wrapper h1 {
    font-size:2rem;
    font-weight:800;
    margin-bottom:8px;
    line-height:1.2;
}
.page-title-wrapper p {
    font-size:1rem;
    opacity:0.95;
    font-weight:500;
}

/* Stats Grid */
.stats-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:24px;
    margin-bottom:32px;
}
.stat-card {
    background:var(--white);
    border-radius:20px;
    padding:24px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor:pointer;
    position:relative;
    overflow:hidden;
}
.stat-card::before {
    content:'';
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;
    background:inherit;
}
.stat-card:hover {
    transform:translateY(-8px);
    box-shadow:0 12px 40px rgba(0,0,0,0.15);
}
.stat-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:16px;
}
.stat-label {
    font-size:0.8rem;
    color:var(--gray);
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.8px;
    margin-bottom:8px;
}
.stat-icon-modern {
    width:60px;
    height:60px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--white);
    box-shadow:0 8px 24px rgba(0,0,0,0.2);
    transition:all 0.3s ease;
}
.stat-card:hover .stat-icon-modern {
    transform:scale(1.1) rotate(5deg);
}
.stat-icon-modern i {
    font-size:1.6rem;
}
.stat-number {
    font-size:2.8rem;
    font-weight:900;
    color:var(--dark);
    line-height:1;
}
.stat-footer {
    margin-top:16px;
    padding-top:16px;
    border-top:1px solid var(--gray-light);
}
.stat-badge {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 12px;
    border-radius:10px;
    font-size:0.8rem;
    font-weight:700;
}
.stat-badge i {
    font-size:0.9rem;
}

/* Table Card */
.card {
    background:var(--white);
    border-radius:20px;
    padding:28px;
    box-shadow:var(--shadow);
}
.card-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
    padding-bottom:16px;
    border-bottom:2px solid var(--gray-light);
}
.card-header h3 {
    font-size:1.2rem;
    font-weight:800;
    display:flex;
    align-items:center;
    gap:10px;
}
.btn-add-new {
    padding:14px 28px;
    background:var(--white);
    color:var(--primary);
    border:none;
    border-radius:14px;
    font-weight:700;
    font-size:1.05rem;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:12px;
    transition:all 0.3s ease;
    box-shadow:0 2px 8px rgba(99,102,241,0.15);
}
.btn-add-new:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(99,102,241,0.3);
    background:var(--primary);
    color:var(--white);
}
.btn-add-new i {
    font-size:1.15rem;
    transition:transform 0.3s ease;
}
.btn-add-new:hover i {
    transform:rotate(90deg) scale(1.3);
}
.btn-export {
    padding:14px 28px;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    color:var(--white);
    border:none;
    border-radius:14px;
    font-weight:700;
    font-size:1.05rem;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:12px;
    transition:all 0.3s ease;
    box-shadow:0 4px 12px rgba(16,185,129,0.25);
}
.btn-export:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(16,185,129,0.4);
    background:linear-gradient(135deg, #059669 0%, #047857 100%);
}
.btn-export i {
    font-size:1.15rem;
    transition:transform 0.3s ease;
}
.btn-export:hover i {
    transform:translateX(3px);
}

/* Filter Bar */
.filter-bar {
    display:flex;
    gap:16px;
    margin-bottom:24px;
    flex-wrap:wrap;
}
.filter-item {
    flex:1;
    min-width:200px;
}
.filter-item select,
.filter-item input {
    width:100%;
    padding:12px 16px;
    border:2px solid var(--gray-light);
    border-radius:12px;
    font-size:0.95rem;
    transition:all 0.3s ease;
}
.filter-item select:focus,
.filter-item input:focus {
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(99,102,241,0.1);
}

/* Table */
.table-wrapper {
    overflow-x:auto;
}
.data-table {
    width:100%;
    border-collapse:collapse;
}
.data-table thead {
    background:var(--gradient-primary);
    color:var(--white);
}
.data-table th {
    padding:16px;
    text-align:left;
    font-weight:700;
    font-size:0.9rem;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.data-table tbody tr {
    border-bottom:1px solid var(--gray-light);
    transition:all 0.3s ease;
}
.data-table tbody tr:hover {
    background:var(--gray-light);
}
.data-table td {
    padding:16px;
    font-size:0.95rem;
}
.article-image {
    width:60px;
    height:60px;
    border-radius:12px;
    object-fit:cover;
}
.user-avatar {
    width:60px;
    height:60px;
    border-radius:12px;
    object-fit:cover;
}
.user-avatar-placeholder {
    width:60px;
    height:60px;
    border-radius:12px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.5rem;
    font-weight:800;
}
.level-badge {
    padding:6px 12px;
    border-radius:20px;
    font-size:0.8rem;
    font-weight:700;
    text-transform:uppercase;
}
.level-badge.level-1 {
    background:rgba(16,185,129,0.1);
    color:#10b981;
}
.level-badge.level-2 {
    background:rgba(245,158,11,0.1);
    color:#f59e0b;
}
.level-badge.level-3 {
    background:rgba(239,68,68,0.1);
    color:#ef4444;
}
.status-badge {
    padding:6px 12px;
    border-radius:20px;
    font-size:0.8rem;
    font-weight:700;
    text-transform:uppercase;
}
.status-badge.published {
    background:rgba(16,185,129,0.1);
    color:var(--success);
}
.status-badge.draft {
    background:rgba(245,158,11,0.1);
    color:var(--warning);
}
.action-buttons {
    display:flex;
    gap:8px;
}
.btn-action {
    width:36px;
    height:36px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all 0.3s ease;
}
.btn-action:hover {
    transform:scale(1.1);
}
.btn-edit {
    background:rgba(59,130,246,0.1);
    color:#3b82f6;
}
.btn-edit:hover {
    background:#3b82f6;
    color:var(--white);
}
.btn-delete {
    background:rgba(239,68,68,0.1);
    color:var(--danger);
}
.btn-delete:hover {
    background:var(--danger);
    color:var(--white);
}
.btn-view {
    background:rgba(99,102,241,0.1);
    color:#6366f1;
}
.btn-view:hover {
    background:#6366f1;
    color:var(--white);
}
.btn-points {
    background:rgba(234,179,8,0.1);
    color:#eab308;
}
.btn-points:hover {
    background:#eab308;
    color:var(--white);
}
.btn-notify {
    background:rgba(147,51,234,0.1);
    color:#9333ea;
}
.btn-notify:hover {
    background:#9333ea;
    color:var(--white);
}

/* Modal */
.modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    backdrop-filter:blur(8px);
    z-index:9999;
    align-items:center;
    justify-content:center;
    animation:fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from { opacity:0; }
    to { opacity:1; }
}
.modal-content {
    background:var(--white);
    border-radius:24px;
    padding:36px;
    width:800px;
    max-width:90%;
    max-height:90vh;
    overflow-y:auto;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    animation:slideUp 0.3s ease;
}
@keyframes slideUp {
    from { 
        transform:translateY(50px);
        opacity:0;
    }
    to { 
        transform:translateY(0);
        opacity:1;
    }
}
.modal-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
    padding-bottom:20px;
    border-bottom:2px solid var(--gray-light);
}
.modal-header h3 {
    font-size:1.5rem;
    font-weight:800;
    color:var(--dark);
    display:flex;
    align-items:center;
    gap:12px;
}
.modal-header h3 i {
    color:var(--primary);
}
.modal-close {
    width:40px;
    height:40px;
    background:var(--gray-light);
    border:none;
    border-radius:12px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all 0.3s ease;
    font-size:1.2rem;
}
.modal-close:hover {
    background:var(--danger);
    color:var(--white);
    transform:rotate(90deg);
}

/* User Detail Modal Styles */
.user-detail-header {
    display:flex;
    align-items:center;
    gap:24px;
    padding:28px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius:20px;
    margin-bottom:28px;
    color:var(--white);
    box-shadow:0 10px 30px rgba(102,126,234,0.3);
}
.user-detail-avatar {
    position:relative;
}
.user-detail-avatar img,
.user-detail-avatar-placeholder {
    width:120px;
    height:120px;
    border-radius:20px;
    object-fit:cover;
    border:4px solid rgba(255,255,255,0.3);
    box-shadow:0 8px 24px rgba(0,0,0,0.2);
}
.user-detail-avatar-placeholder {
    background:rgba(255,255,255,0.2);
    backdrop-filter:blur(10px);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:3rem;
    font-weight:900;
}
.user-detail-info h2 {
    font-size:2rem;
    font-weight:900;
    margin:0 0 8px 0;
    text-shadow:0 2px 4px rgba(0,0,0,0.1);
}
.user-detail-username {
    font-size:1.1rem;
    opacity:0.9;
    font-weight:500;
    margin-bottom:12px;
}
.user-detail-badges {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.user-badge {
    padding:8px 16px;
    border-radius:12px;
    font-size:0.85rem;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:rgba(255,255,255,0.2);
    backdrop-filter:blur(10px);
}
.user-info-grid {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:20px;
    margin-top:24px;
}
.user-info-item {
    padding:20px;
    background:var(--gray-light);
    border-radius:16px;
    transition:all 0.3s ease;
}
.user-info-item:hover {
    background:linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
.user-info-label {
    font-size:0.85rem;
    color:var(--gray);
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.5px;
    margin-bottom:8px;
    display:flex;
    align-items:center;
    gap:8px;
}
.user-info-value {
    font-size:1.1rem;
    color:var(--dark);
    font-weight:600;
}
.user-info-value.highlight {
    font-size:1.5rem;
    color:var(--primary);
    font-weight:800;
}
.user-stats-row {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:16px;
    margin-top:24px;
    padding-top:24px;
    border-top:2px solid var(--gray-light);
    max-width:500px;
    margin-left:auto;
    margin-right:auto;
}
.user-stat-box {
    text-align:center;
    padding:20px;
    background:var(--white);
    border:2px solid var(--gray-light);
    border-radius:16px;
    transition:all 0.3s ease;
}
.user-stat-box:hover {
    border-color:var(--primary);
    transform:translateY(-4px);
    box-shadow:0 8px 20px rgba(99,102,241,0.15);
}
.user-stat-icon {
    width:50px;
    height:50px;
    margin:0 auto 12px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.5rem;
    color:var(--white);
}
.user-stat-value {
    font-size:1.8rem;
    font-weight:900;
    color:var(--dark);
    margin-bottom:4px;
}
.user-stat-label {
    font-size:0.85rem;
    color:var(--gray);
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
.form-group {
    display:flex;
    flex-direction:column;
    gap:8px;
}
.form-group.full-width {
    grid-column:1 / -1;
}
.form-group label {
    font-weight:700;
    font-size:0.95rem;
    color:var(--dark);
}
.form-group input,
.form-group select,
.form-group textarea {
    padding:12px 16px;
    border:2px solid var(--gray-light);
    border-radius:12px;
    font-size:0.95rem;
    transition:all 0.3s ease;
}
.form-group textarea {
    min-height:200px;
    resize:vertical;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(99,102,241,0.1);
}
.form-actions {
    display:flex;
    gap:12px;
    justify-content:flex-end;
    margin-top:24px;
}
.btn-submit {
    padding:12px 32px;
    background:var(--gradient-primary);
    color:var(--white);
    border:none;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    transition:all 0.3s ease;
}
.btn-submit:hover {
    transform:translateY(-2px);
    box-shadow:var(--shadow-lg);
}
.btn-cancel {
    padding:12px 32px;
    background:var(--gray-light);
    color:var(--dark);
    border:none;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
}

/* Toast */
.toast {
    position:fixed;
    top:90px;
    right:32px;
    background:var(--white);
    padding:16px 24px;
    border-radius:12px;
    box-shadow:var(--shadow-lg);
    display:none;
    align-items:center;
    gap:12px;
    z-index:10000;
    animation:slideInRight 0.3s ease;
}
.toast.success {border-left:4px solid var(--success);}
.toast.error {border-left:4px solid var(--danger);}
@keyframes slideInRight {
    from {
        transform:translateX(400px);
        opacity:0;
    }
    to {
        transform:translateX(0);
        opacity:1;
    }
}

/* Responsive */
@media(max-width:1200px){
    .stats-grid {grid-template-columns:repeat(2, 1fr);}
    .form-grid {grid-template-columns:1fr;}
}
@media(max-width:768px){
    .sidebar {
        left:-280px;
        width:280px;
    }
    .sidebar.mobile-show {left:0;}
    .main-content {margin-left:0;}
    .stats-grid {grid-template-columns:1fr;}
    .page-header-content {flex-direction:column; text-align:center; gap:20px;}
}

/* Additional button styles */
.btn-view {
    background:rgba(59,130,246,0.1);
    color:#3b82f6;
}
.btn-view:hover {
    background:#3b82f6;
    color:var(--white);
}
.btn-points {
    background:rgba(245,158,11,0.1);
    color:#f59e0b;
}
.btn-points:hover {
    background:#f59e0b;
    color:var(--white);
}
.btn-lock {
    background:rgba(239,68,68,0.1);
    color:#ef4444;
}
.btn-lock:hover {
    background:#ef4444;
    color:var(--white);
}
.btn-unlock {
    background:rgba(16,185,129,0.1);
    color:#10b981;
}
.btn-unlock:hover {
    background:#10b981;
    color:var(--white);
}
.btn-notify {
    background:rgba(236,72,153,0.1);
    color:#ec4899;
}
.btn-notify:hover {
    background:#ec4899;
    color:var(--white);
}
.status-badge.active {
    background:rgba(16,185,129,0.1);
    color:#10b981;
}
.status-badge.locked {
    background:rgba(239,68,68,0.1);
    color:#ef4444;
}
</style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                     <i class="fas fa-dharmachakra"></i>
                </div>
                <div class="sidebar-logo-text">
                    <h2>Lâm Nhật Hào</h2>
                    <p>Văn hóa Khmer Nam Bộ</p>
                </div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Menu chính</div>
                <div class="menu-item" onclick="location.href='index.php'">
                    <i class="fas fa-home"></i>
                    <span>Trang chủ</span>
                </div>
                <div class="menu-item" onclick="location.href='vanhoa.php'">
                    <i class="fas fa-book-open"></i>
                    <span>Văn hóa Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='chua.php'">
                    <i class="fas fa-place-of-worship"></i>
                    <span>Chùa Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='lehoi.php'">
                    <i class="fas fa-calendar-check"></i>
                    <span>Lễ hội</span>
                </div>
                <div class="menu-item" onclick="location.href='hoctiengkhmer.php'">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Học tiếng Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='truyendangian.php'">
                    <i class="fas fa-book-reader"></i>
                    <span>Truyện dân gian</span>
                </div>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Quản lý</div>
                <div class="menu-item active" onclick="location.href='nguoidung.php'">
                    <i class="fas fa-users"></i>
                    <span>Người dùng</span>
                </div>
                <div class="menu-item" onclick="location.href='binhluan.php'">
                    <i class="fas fa-comments"></i>
                    <span>Bình luận</span>
                </div>
            </div>
            <div class="menu-section">
                <div class="menu-item" onclick="logout()" style="color:var(--danger);">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </div>
            </div>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm người dùng..." autocomplete="off">
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h1>Quản lý Người dùng</h1>
                            <p>Quản lý tài khoản và hoạt động của người dùng hệ thống</p>
                        </div>
                    </div>
                    <button class="btn-export" onclick="exportUsers()">
                        <i class="fas fa-file-export"></i>
                        Xuất danh sách
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #4facfe;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Tổng người dùng</span>
                            <div class="stat-number"><?php echo number_format($total_users); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(79, 172, 254, 0.1); color: #4facfe;">
                            <i class="fas fa-database"></i> Tất cả
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #10b981;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Đang hoạt động</span>
                            <div class="stat-number"><?php echo number_format($active_users); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-check-circle"></i> Hoạt động
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ef4444;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Bị khóa</span>
                            <div class="stat-number"><?php echo number_format($locked_users); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-user-lock"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fas fa-ban"></i> Bị khóa
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #f59e0b;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Mới tháng này</span>
                            <div class="stat-number"><?php echo number_format($new_users_month); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="fas fa-calendar"></i> 30 ngày
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        Danh sách người dùng
                    </h3>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="filter-item">
                        <select id="filterStatus" onchange="filterUsers()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="hoat_dong">Hoạt động</option>
                            <option value="bi_khoa">Bị khóa</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select id="filterLevel" onchange="filterUsers()">
                            <option value="">Tất cả cấp độ</option>
                            <option value="1">Dễ</option>
                            <option value="2">Trung bình</option>
                            <option value="3">Khó</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <input type="text" id="filterSearch" placeholder="Tìm kiếm..." onkeyup="filterUsers()">
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Avatar</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Điểm</th>
                                <th>Cấp độ</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="10" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-inbox" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có người dùng nào</strong>
                                    <p style="margin-top:8px;">Hãy thêm người dùng đầu tiên!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($users as $index => $user): ?>
                                <tr data-status="<?php echo $user['trang_thai']; ?>" data-level="<?php echo $user['cap_do'] ?? 1; ?>">
                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                    <td>
                                        <?php if(!empty($user['anh_dai_dien'])): 
                                            $avatarPath = $user['anh_dai_dien'];
                                            // Nếu đường dẫn chưa có uploads/, thêm vào
                                            if (strpos($avatarPath, 'uploads/') !== 0) {
                                                $avatarPath = 'uploads/avatar/' . $avatarPath;
                                            }
                                        ?>
                                            <img src="/DoAn_ChuyenNganh/<?php echo htmlspecialchars($avatarPath); ?>" class="user-avatar" alt="Avatar">
                                        <?php else: ?>
                                            <div class="user-avatar-placeholder">
                                                <?php 
                                                $name = $user['ho_ten'] ?? $user['ten_dang_nhap'];
                                                echo mb_strtoupper(mb_substr($name, 0, 1));
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($user['ten_dang_nhap']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['ho_ten'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <i class="fas fa-star" style="color:#f59e0b;"></i>
                                        <?php echo number_format($user['tong_diem'] ?? 0); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $level = $user['cap_do'] ?? 1;
                                        $level_text = [
                                            1 => 'Dễ',
                                            2 => 'Trung bình',
                                            3 => 'Khó'
                                        ];
                                        ?>
                                        <span class="level-badge level-<?php echo $level; ?>">
                                            <?php echo $level_text[$level] ?? 'Dễ'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $user['trang_thai'] === 'hoat_dong' ? 'active' : 'locked'; ?>">
                                            <?php echo $user['trang_thai'] === 'hoat_dong' ? 'Hoạt động' : 'Bị khóa'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['ngay_tao_fmt'] ?? '-'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" 
                                                    data-user='<?php echo json_encode($user); ?>'
                                                    onclick="viewUserDetail(JSON.parse(this.getAttribute('data-user')))" 
                                                    title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-points" 
                                                    data-user='<?php echo json_encode($user); ?>'
                                                    onclick="openPointsModal(JSON.parse(this.getAttribute('data-user')))" 
                                                    title="Cộng điểm">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <button class="btn-action <?php echo $user['trang_thai'] === 'hoat_dong' ? 'btn-lock' : 'btn-unlock'; ?>" 
                                                    onclick="toggleStatus(<?php echo $user['ma_nguoi_dung']; ?>)" 
                                                    title="<?php echo $user['trang_thai'] === 'hoat_dong' ? 'Khóa' : 'Mở khóa'; ?>">
                                                <i class="fas fa-<?php echo $user['trang_thai'] === 'hoat_dong' ? 'lock' : 'unlock'; ?>"></i>
                                            </button>
                                            <button class="btn-action btn-delete" 
                                                    onclick="deleteUser(<?php echo $user['ma_nguoi_dung']; ?>, '<?php echo htmlspecialchars($user['ho_ten'] ?? $user['ten_dang_nhap'], ENT_QUOTES); ?>')" 
                                                    title="Xóa người dùng">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- VIEW DETAIL MODAL -->
<div class="modal" id="viewDetailModal">
    <div class="modal-content" style="max-width:900px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> Thông tin chi tiết người dùng</h3>
            <button class="modal-close" onclick="closeModal('viewDetailModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="userDetailContent" style="padding:20px 0;">
            <!-- Content will be dynamically loaded -->
        </div>
    </div>
</div>

<!-- POINTS MODAL -->
<div class="modal" id="pointsModal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fas fa-star"></i> Cộng điểm người dùng</h3>
            <button class="modal-close" onclick="closeModal('pointsModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_points">
            <input type="hidden" name="ma_nguoi_dung" id="points_ma_nguoi_dung">
            <div class="form-group">
                <label>Người dùng</label>
                <input type="text" id="points_user_name" disabled style="background:#f5f5f5;">
            </div>
            <div class="form-group">
                <label>Điểm hiện tại</label>
                <input type="text" id="points_current" disabled style="background:#f5f5f5;">
            </div>
            <div class="form-group">
                <label>Số điểm cộng thêm <span style="color:red;">*</span></label>
                <input type="number" name="diem_them" min="1" required placeholder="Nhập số điểm muốn cộng">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('pointsModal')">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> Cộng điểm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i> Xác nhận xóa</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p style="margin-bottom:24px;">Bạn có chắc chắn muốn xóa người dùng <strong id="delete_user_name"></strong>? Hành động này không thể hoàn tác!</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ma_nguoi_dung" id="delete_ma_nguoi_dung">
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Hủy</button>
                <button type="submit" class="btn-submit" style="background:var(--danger);">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Chỉnh sửa người dùng</h3>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ma_nguoi_dung" id="edit_ma_nguoi_dung">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" id="edit_ten_dang_nhap" disabled style="background:#f5f5f5;">
                </div>
                <div class="form-group">
                    <label>Email <span style="color:red;">*</span></label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group full-width">
                    <label>Mật khẩu mới <small>(Để trống nếu không đổi)</small></label>
                    <input type="password" name="mat_khau" id="edit_mat_khau" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="ho_ten" id="edit_ho_ten">
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="ngay_sinh" id="edit_ngay_sinh">
                </div>
                <div class="form-group">
                    <label>Giới tính</label>
                    <select name="gioi_tinh" id="edit_gioi_tinh">
                        <option value="">-- Chọn --</option>
                        <option value="nam">Nam</option>
                        <option value="nu">Nữ</option>
                        <option value="khac">Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="so_dien_thoai" id="edit_so_dien_thoai">
                </div>
                <div class="form-group">
                    <label>Tổng điểm</label>
                    <input type="number" name="tong_diem" id="edit_tong_diem" min="0">
                </div>
                <div class="form-group">
                    <label>Cấp độ</label>
                    <select name="cap_do" id="edit_cap_do">
                        <option value="1">Dễ</option>
                        <option value="2">Trung bình</option>
                        <option value="3">Khó</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="anh_dai_dien" id="edit_anh_dai_dien" accept="image/*" onchange="previewImage(this, 'edit_preview')">
                    <img id="edit_preview" style="display:none; margin-top:12px; max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="edit_trang_thai">
                        <option value="hoat_dong">Hoạt động</option>
                        <option value="bi_khoa">Bị khóa</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST NOTIFICATION -->
<?php if($message): ?>
<div class="toast <?php echo $messageType; ?>" id="toast" style="display:flex;">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>" style="font-size:1.5rem;"></i>
    <span><?php echo htmlspecialchars($message); ?></span>
</div>
<?php endif; ?>

<script>
// Show toast
<?php if($message): ?>
setTimeout(() => {
    const toast = document.getElementById('toast');
    if(toast) toast.style.display = 'none';
}, 3000);
<?php endif; ?>

// Preview image function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// Modal functions
function viewUserDetail(user) {
    const content = document.getElementById('userDetailContent');
    
    // Format giới tính
    let genderText = '-';
    let genderIcon = 'fa-question';
    if(user.gioi_tinh === 'nam') {
        genderText = 'Nam';
        genderIcon = 'fa-mars';
    } else if(user.gioi_tinh === 'nu') {
        genderText = 'Nữ';
        genderIcon = 'fa-venus';
    } else if(user.gioi_tinh === 'khac') {
        genderText = 'Khác';
        genderIcon = 'fa-genderless';
    }
    
    // Format ngày sinh
    let birthDate = user.ngay_sinh || '-';
    if(user.ngay_sinh && user.ngay_sinh !== '-') {
        const date = new Date(user.ngay_sinh);
        birthDate = date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    
    // Tính tuổi nếu có ngày sinh
    let age = '';
    if(user.ngay_sinh && user.ngay_sinh !== '-') {
        const today = new Date();
        const birth = new Date(user.ngay_sinh);
        let ageCalc = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if(monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            ageCalc--;
        }
        age = ageCalc > 0 ? ` (${ageCalc} tuổi)` : '';
    }
    
    // Level text
    const levelText = {1: 'Dễ', 2: 'Trung bình', 3: 'Khó'};
    const levelColor = {1: '#10b981', 2: '#f59e0b', 3: '#ef4444'};
    
    // Xử lý đường dẫn avatar
    let avatarPath = user.anh_dai_dien || '';
    if (avatarPath && !avatarPath.startsWith('uploads/')) {
        avatarPath = 'uploads/avatar/' + avatarPath;
    }
    
    content.innerHTML = `
        <!-- Header với avatar và thông tin cơ bản -->
        <div class="user-detail-header">
            <div class="user-detail-avatar">
                ${user.anh_dai_dien ? 
                    `<img src="/DoAn_ChuyenNganh/${avatarPath}" alt="Avatar">` :
                    `<div class="user-detail-avatar-placeholder">${(user.ho_ten || user.ten_dang_nhap).charAt(0).toUpperCase()}</div>`
                }
            </div>
            <div class="user-detail-info">
                <h2>${user.ho_ten || user.ten_dang_nhap}</h2>
                <div class="user-detail-badges">
                    <span class="user-badge">
                        <i class="fas fa-shield-alt"></i>
                        ${user.trang_thai === 'hoat_dong' ? 'Hoạt động' : 'Bị khóa'}
                    </span>
                    <span class="user-badge">
                        <i class="fas fa-layer-group"></i>
                        Level ${user.cap_do || 1}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Thống kê nhanh -->
        <div class="user-stats-row">
            <div class="user-stat-box">
                <div class="user-stat-icon" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-star"></i>
                </div>
                <div class="user-stat-value">${Number(user.tong_diem || 0).toLocaleString()}</div>
                <div class="user-stat-label">Tổng điểm</div>
            </div>
            <div class="user-stat-box">
                <div class="user-stat-icon" style="background:linear-gradient(135deg, ${levelColor[user.cap_do || 1]} 0%, ${levelColor[user.cap_do || 1]}dd 100%);">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="user-stat-value">${user.cap_do || 1}</div>
                <div class="user-stat-label">${levelText[user.cap_do || 1]}</div>
            </div>
        </div>
        
        <!-- Thông tin chi tiết -->
        <div class="user-info-grid">
            <div class="user-info-item">
                <div class="user-info-label">
                    <i class="fas fa-envelope"></i>
                    Email
                </div>
                <div class="user-info-value">${user.email}</div>
            </div>
            
            <div class="user-info-item">
                <div class="user-info-label">
                    <i class="fas fa-phone"></i>
                    Số điện thoại
                </div>
                <div class="user-info-value">${user.so_dien_thoai || 'Chưa cập nhật'}</div>
            </div>
            
            <div class="user-info-item">
                <div class="user-info-label">
                    <i class="fas fa-birthday-cake"></i>
                    Ngày sinh
                </div>
                <div class="user-info-value">${birthDate}${age}</div>
            </div>
            
            <div class="user-info-item">
                <div class="user-info-label">
                    <i class="fas ${genderIcon}"></i>
                    Giới tính
                </div>
                <div class="user-info-value">${genderText}</div>
            </div>
            
        </div>
    `;
    document.getElementById('viewDetailModal').style.display = 'flex';
}

function openPointsModal(user) {
    document.getElementById('points_ma_nguoi_dung').value = user.ma_nguoi_dung;
    document.getElementById('points_user_name').value = user.ho_ten || user.ten_dang_nhap;
    document.getElementById('points_current').value = Number(user.tong_diem || 0).toLocaleString() + ' điểm';
    document.getElementById('pointsModal').style.display = 'flex';
}

function deleteUser(userId, userName) {
    document.getElementById('delete_ma_nguoi_dung').value = userId;
    document.getElementById('delete_user_name').textContent = userName;
    document.getElementById('deleteModal').style.display = 'flex';
}

function openEditModal(user) {
    document.getElementById('edit_ma_nguoi_dung').value = user.ma_nguoi_dung;
    document.getElementById('edit_ten_dang_nhap').value = user.ten_dang_nhap;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_ho_ten').value = user.ho_ten || '';
    document.getElementById('edit_ngay_sinh').value = user.ngay_sinh || '';
    document.getElementById('edit_gioi_tinh').value = user.gioi_tinh || '';
    document.getElementById('edit_so_dien_thoai').value = user.so_dien_thoai || '';
    document.getElementById('edit_tong_diem').value = user.tong_diem || 0;
    document.getElementById('edit_cap_do').value = user.cap_do || 1;
    document.getElementById('edit_anh_dai_dien').value = user.anh_dai_dien || '';
    document.getElementById('edit_trang_thai').value = user.trang_thai;
    document.getElementById('edit_mat_khau').value = '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Toggle user status (lock/unlock)
function toggleStatus(id) {
    if(confirm('Bạn có chắc chắn muốn thay đổi trạng thái người dùng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="ma_nguoi_dung" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Export users to CSV
function exportUsers() {
    // Lấy tất cả dữ liệu người dùng hiển thị
    const rows = document.querySelectorAll('#usersTableBody tr');
    let csvContent = "STT,Tên đăng nhập,Họ tên,Email,Điểm,Cấp độ,Trạng thái,Ngày tạo\n";
    
    rows.forEach((row, index) => {
        if(row.cells.length > 1 && row.style.display !== 'none') {
            const cells = row.cells;
            const data = [
                index + 1,
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim().replace(/[^\d]/g, ''),
                cells[6].textContent.trim(),
                cells[7].textContent.trim(),
                cells[8].textContent.trim()
            ];
            csvContent += data.map(d => `"${d}"`).join(',') + "\n";
        }
    });
    
    // Tạo file và download
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "danh_sach_nguoi_dung_" + new Date().toISOString().split('T')[0] + ".csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    alert('Đã xuất danh sách người dùng thành công!');
}

// Filter users
function filterUsers() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const level = document.getElementById('filterLevel').value;
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        if(row.cells.length === 1) return; // Skip empty row
        
        const rowStatus = row.dataset.status || '';
        const rowLevel = row.dataset.level || '';
        const rowText = row.textContent.toLowerCase();
        
        const matchStatus = !status || rowStatus === status;
        const matchLevel = !level || rowLevel === level;
        const matchSearch = !search || rowText.includes(search);
        
        row.style.display = (matchStatus && matchLevel && matchSearch) ? '' : 'none';
    });
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        if(row.cells.length === 1) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});

// Close modal on outside click
window.onclick = function(event) {
    if(event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Logout function
function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        location.href = 'dangxuat.php';
    }
}

// Profile menu toggle
function toggleProfileMenu() {
    // Add your profile menu logic here
    console.log('Profile menu clicked');
}
</script>

<!-- VIEW DETAIL MODAL -->
<div class="modal" id="viewDetailModal">
    <div class="modal-content" style="max-width:700px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> Thông tin chi tiết người dùng</h3>
            <button class="modal-close" onclick="closeModal('viewDetailModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="userDetailContent" style="padding:20px;">
            <!-- Content will be loaded by JavaScript -->
        </div>
    </div>
</div>

</body>
</html>
