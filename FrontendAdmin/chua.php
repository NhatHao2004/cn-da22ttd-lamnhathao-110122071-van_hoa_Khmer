<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/ChuaKhmer.php';
require_once 'includes/upload.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();
$chuaModel = new ChuaKhmer();

// Xử lý các hành động CRUD với PRG Pattern (Post-Redirect-Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add':
                // Xử lý upload ảnh
                $anh_dai_dien = '';
                if(isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('chua');
                    $anh_dai_dien = $uploader->upload($_FILES['anh_dai_dien']);
                    if (!$anh_dai_dien) {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                $data = [
                    'ten_chua' => $_POST['ten_chua'],
                    'ten_chua_khmer' => $_POST['ten_chua_khmer'] ?? '',
                    'dia_chi' => $_POST['dia_chi'] ?? '',
                    'tinh_thanh' => $_POST['tinh_thanh'] ?? '',
                    'quan_huyen' => $_POST['quan_huyen'] ?? '',
                    'loai_chua' => $_POST['loai_chua'] ?? 'Theravada',
                    'so_dien_thoai' => $_POST['so_dien_thoai'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'website' => $_POST['website'] ?? '',
                    'mo_ta' => $_POST['mo_ta'] ?? '',
                    'lich_su' => $_POST['lich_su'] ?? '',
                    'anh_dai_dien' => $anh_dai_dien,
                    'nam_thanh_lap' => !empty($_POST['nam_thanh_lap']) ? (int)$_POST['nam_thanh_lap'] : null,
                    'so_luong_nha_su' => !empty($_POST['so_luong_nha_su']) ? (int)$_POST['so_luong_nha_su'] : 0,
                    'trang_thai' => $_POST['trang_thai'] ?? 'hoat_dong'
                ];
                
                if($chuaModel->create($data)) {
                    $_SESSION['flash_message'] = 'Thêm chùa Khmer thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi thêm chùa. Vui lòng thử lại!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: chua.php');
                exit;
                
            case 'edit':
                // Lấy thông tin chùa hiện tại
                $currentTemple = $chuaModel->getById($_POST['ma_chua']);
                $anh_dai_dien = $currentTemple['anh_dai_dien'] ?? '';
                
                // Xử lý upload ảnh mới
                if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('chua');
                    $newImagePath = $uploader->upload($_FILES['anh_dai_dien']);
                    if ($newImagePath) {
                        // Xóa ảnh cũ nếu có
                        if ($anh_dai_dien && file_exists(__DIR__ . '/' . $anh_dai_dien)) {
                            @unlink(__DIR__ . '/' . $anh_dai_dien);
                        }
                        $anh_dai_dien = $newImagePath;
                    } else {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                $data = [
                    'ten_chua' => $_POST['ten_chua'],
                    'ten_chua_khmer' => $_POST['ten_chua_khmer'] ?? '',
                    'dia_chi' => $_POST['dia_chi'] ?? '',
                    'tinh_thanh' => $_POST['tinh_thanh'] ?? '',
                    'quan_huyen' => $_POST['quan_huyen'] ?? '',
                    'loai_chua' => $_POST['loai_chua'] ?? 'Theravada',
                    'so_dien_thoai' => $_POST['so_dien_thoai'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'website' => $_POST['website'] ?? '',
                    'mo_ta' => $_POST['mo_ta'] ?? '',
                    'lich_su' => $_POST['lich_su'] ?? '',
                    'anh_dai_dien' => $anh_dai_dien,
                    'nam_thanh_lap' => $_POST['nam_thanh_lap'] ?? null,
                    'so_luong_nha_su' => $_POST['so_luong_nha_su'] ?? 0,
                    'trang_thai' => $_POST['trang_thai'] ?? 'hoat_dong'
                ];
                if($chuaModel->update($_POST['ma_chua'], $data)) {
                    $_SESSION['flash_message'] = 'Cập nhật thông tin chùa thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi cập nhật. Vui lòng thử lại!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: chua.php');
                exit;
                
            case 'delete':
                // Lấy thông tin chùa để xóa ảnh
                $temple = $chuaModel->getById($_POST['ma_chua']);
                if ($temple && $temple['anh_dai_dien'] && file_exists(__DIR__ . '/' . $temple['anh_dai_dien'])) {
                    @unlink(__DIR__ . '/' . $temple['anh_dai_dien']);
                }
                
                if($chuaModel->delete($_POST['ma_chua'])) {
                    $_SESSION['flash_message'] = 'Xóa chùa thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa. Vui lòng thử lại!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: chua.php');
                exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: chua.php');
        exit;
    }
}

// Lấy thông báo từ session (nếu có)
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách chùa
$temples = $chuaModel->getAll(100);

// Format ngày tạo
foreach($temples as &$temple) {
    if(isset($temple['ngay_tao'])) {
        $temple['ngay_tao_fmt'] = date('d/m/Y H:i', strtotime($temple['ngay_tao']));
    }
}

// Lấy danh sách tỉnh thành
$provinces = $chuaModel->getProvinces();

// Thống kê
$total_temples = $chuaModel->count();
$active_temples = $chuaModel->count('`trang_thai` = ?', ['hoat_dong']);
$inactive_temples = $chuaModel->count('`trang_thai` = ?', ['ngung_hoat_dong']);
$total_views = $db->querySingle("SELECT SUM(luot_xem) as total FROM chua_khmer")['total'] ?? 0;

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
<meta name="description" content="Quản lý chùa Khmer Nam Bộ">
<meta name="theme-color" content="#6366f1">
<title>Quản lý Chùa Khmer</title>
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
    --gradient-temple: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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
    background:var(--gradient-temple);
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

/* Modal */
.modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    z-index:9999;
    align-items:center;
    justify-content:center;
}
.modal-content {
    background:var(--white);
    border-radius:24px;
    padding:36px;
    width:800px;
    max-width:90%;
    max-height:90vh;
    overflow-y:auto;
}
.modal-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
}
.modal-header h3 {
    font-size:1.5rem;
    font-weight:800;
}
.modal-close {
    width:36px;
    height:36px;
    background:var(--gray-light);
    border:none;
    border-radius:10px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}
.modal-close:hover {
    background:var(--danger);
    color:var(--white);
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
    background:var(--gradient-temple);
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
                <div class="menu-item active" onclick="location.href='chua.php'">
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
                <div class="menu-item" onclick="location.href='nguoidung.php'">
                    <i class="fas fa-users"></i>
                    <span>Người dùng</span>
                </div>
                <div class="menu-item" onclick="location.href='thongbao.php'">
                    <i class="fas fa-bell"></i>
                    <span>Thông báo</span>
                </div>
                <div class="menu-item" onclick="location.href='tinnhan.php'">
                    <i class="fas fa-comments"></i>
                    <span>Tin nhắn</span>
                </div>
                <div class="menu-item" onclick="location.href='hoatdong.php'">
                    <i class="fas fa-history"></i>
                    <span>Hoạt động</span>
                </div>
                <div class="menu-item" onclick="location.href='caidat.php'">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
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
                    <input type="text" id="searchInput" placeholder="Tìm kiếm chùa Khmer..." autocomplete="off">
                </div>
            </div>
            <div class="topbar-right"> 
                <div class="admin-profile-enhanced" onclick="toggleProfileMenu()">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            <?php 
                            $name = $_SESSION['admin_name'] ?? 'Admin';
                            $words = explode(' ', $name);
                            if(count($words) >= 2) {
                                $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words)-1], 0, 1));
                            } else {
                                $initials = mb_strtoupper(mb_substr($name, 0, 2));
                            }
                            echo $initials;
                            ?>
                        </div>
                        <div class="online-status"></div>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                        <?php 
                        $role = $_SESSION['admin_role'] ?? 'bien_tap_vien';
                        $role_display = [
                            'sieu_quan_tri' => ['text' => 'Siêu Quản Trị', 'icon' => 'fa-crown', 'class' => 'role-super-admin'],
                            'quan_tri' => ['text' => 'Quản Trị Viên', 'icon' => 'fa-user-shield', 'class' => 'role-admin'],
                            'bien_tap_vien' => ['text' => 'Biên Tập Viên', 'icon' => 'fa-pen-fancy', 'class' => 'role-editor']
                        ];
                        $role_info = $role_display[$role] ?? $role_display['bien_tap_vien'];
                        ?>
                        <span class="profile-role <?php echo $role_info['class']; ?>">
                            <i class="fas <?php echo $role_info['icon']; ?>"></i>
                            <?php echo $role_info['text']; ?>
                        </span>
                    </div>
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
                            <i class="fas fa-place-of-worship"></i>
                        </div>
                        <div>
                            <h1>Quản lý Chùa Khmer Nam Bộ</h1>
                            <p>Kho tàng tâm linh - Di sản văn hóa Phật giáo Nam Tông</p>
                        </div>
                    </div>
                    <button class="btn-add-new" onclick="openAddModal()">
                        <i class="fas fa-plus-circle"></i>
                        Thêm chùa mới
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #667eea;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Tổng số chùa</span>
                            <div class="stat-number"><?php echo number_format($total_temples); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-place-of-worship"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                            <i class="fas fa-database"></i> Tất cả chùa
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #10b981;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Đang hoạt động</span>
                            <div class="stat-number"><?php echo number_format($active_temples); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-praying-hands"></i> Đang mở cửa
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ef4444;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Ngừng hoạt động</span>
                            <div class="stat-number"><?php echo number_format($inactive_temples); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fas fa-door-closed"></i> Đóng cửa
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ec4899;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Lượt xem</span>
                            <div class="stat-number"><?php echo number_format($total_views); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                            <i class="fas fa-chart-line"></i> Tổng lượt truy cập
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        Danh sách chùa Khmer
                    </h3>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="filter-item">
                        <select id="provinceFilter">
                            <option value="">Tất cả tỉnh thành</option>
                            <?php foreach($provinces as $province): ?>
                            <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="hoat_dong">Đang hoạt động</option>
                            <option value="ngung_hoat_dong">Ngừng hoạt động</option>
                        </select>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table" id="templesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tên chùa</th>
                                <th>Tên tiếng Khmer</th>
                                <th>Địa chỉ</th>
                                <th>Tỉnh thành</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($temples)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-place-of-worship" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có chùa nào</strong>
                                    <p style="margin-top:8px;">Hãy thêm chùa đầu tiên!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($temples as $temple): ?>
                            <tr data-province="<?php echo $temple['tinh_thanh'] ?? ''; ?>" 
                                data-status="<?php echo $temple['trang_thai'] ?? 'hoat_dong'; ?>">
                                <td>#<?php echo $temple['ma_chua']; ?></td>
                                <td>
                                    <?php if(!empty($temple['hinh_anh_chinh'])): ?>
                                    <img src="<?php echo htmlspecialchars($temple['hinh_anh_chinh']); ?>" alt="<?php echo htmlspecialchars($temple['ten_chua']); ?>" class="article-image" onerror="this.parentElement.innerHTML='<div class=\'article-image\' style=\'background:var(--gray-light); display:flex; align-items:center; justify-content:center;\'><i class=\'fas fa-place-of-worship\' style=\'color:var(--gray);\'></i></div>'">
                                    <?php else: ?>
                                    <div class="article-image" style="background:var(--gray-light); display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-place-of-worship" style="color:var(--gray);"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($temple['ten_chua']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($temple['ten_chua_khmer'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($temple['dia_chi'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($temple['tinh_thanh'] ?? '-'); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch($temple['trang_thai'] ?? 'hoat_dong') {
                                        case 'hoat_dong':
                                            $status_class = 'published';
                                            $status_text = 'Hoạt động';
                                            break;
                                        default:
                                            $status_class = 'draft';
                                            $status_text = 'Ngừng hoạt động';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-eye"></i> <?php echo number_format($temple['luot_xem'] ?? 0); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick='editTemple(<?php echo json_encode($temple, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteTemple(<?php echo $temple['ma_chua']; ?>)" title="Xóa">
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

<!-- ADD MODAL -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Thêm chùa mới</h3>
            <button class="modal-close" onclick="closeAddModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="addForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tên chùa *</label>
                    <input type="text" name="ten_chua" required placeholder="Nhập tên chùa">
                </div>
                <div class="form-group">
                    <label>Tên tiếng Khmer</label>
                    <input type="text" name="ten_chua_khmer" placeholder="ឈ្មោះវត្ត (nếu có)">
                </div>
                <div class="form-group full-width">
                    <label>Địa chỉ</label>
                    <input type="text" name="dia_chi" placeholder="Số nhà, đường, xã/phường">
                </div>
                <div class="form-group">
                    <label>Tỉnh/Thành phố *</label>
                    <input type="text" name="tinh_thanh" required placeholder="VD: Sóc Trăng">
                </div>
                <div class="form-group">
                    <label>Quận/Huyện</label>
                    <input type="text" name="quan_huyen" placeholder="Nhập quận/huyện">
                </div>
                <div class="form-group">
                    <label>Loại chùa</label>
                    <select name="loai_chua">
                        <option value="Theravada">Theravada (Nam Tông)</option>
                        <option value="Mahayana">Mahayana (Bắc Tông)</option>
                        <option value="Vajrayana">Vajrayana (Mật Tông)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai">
                        <option value="hoat_dong">Đang hoạt động</option>
                        <option value="ngung_hoat_dong">Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="so_dien_thoai" placeholder="0xxx xxx xxx">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="contact@chua.com">
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" placeholder="https://chua.com">
                </div>
                <div class="form-group">
                    <label>Năm thành lập</label>
                    <input type="number" name="nam_thanh_lap" placeholder="VD: 1850" min="1000" max="<?php echo date('Y'); ?>">
                </div>
                <div class="form-group">
                    <label>Số lượng nhà sư</label>
                    <input type="number" name="so_luong_nha_su" placeholder="0" min="0" value="0">
                </div>
                <div class="form-group full-width">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="anh_dai_dien" accept="image/*" onchange="previewImage(this, 'addPreview')">
                    <div id="addPreview" style="margin-top:12px;"></div>
                </div>
                <div class="form-group full-width">
                    <label>Mô tả ngắn</label>
                    <textarea name="mo_ta" rows="3" placeholder="Mô tả ngắn gọn về chùa"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Lịch sử</label>
                    <textarea name="lich_su" rows="4" placeholder="Lịch sử hình thành và phát triển"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu thông tin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Chỉnh sửa thông tin chùa</h3>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ma_chua" id="edit_ma_chua">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tên chùa *</label>
                    <input type="text" name="ten_chua" id="edit_ten_chua" required>
                </div>
                <div class="form-group">
                    <label>Tên tiếng Khmer</label>
                    <input type="text" name="ten_chua_khmer" id="edit_ten_chua_khmer">
                </div>
                <div class="form-group full-width">
                    <label>Địa chỉ</label>
                    <input type="text" name="dia_chi" id="edit_dia_chi">
                </div>
                <div class="form-group">
                    <label>Tỉnh/Thành phố *</label>
                    <input type="text" name="tinh_thanh" id="edit_tinh_thanh" required>
                </div>
                <div class="form-group">
                    <label>Quận/Huyện</label>
                    <input type="text" name="quan_huyen" id="edit_quan_huyen">
                </div>
                <div class="form-group">
                    <label>Loại chùa</label>
                    <select name="loai_chua" id="edit_loai_chua">
                        <option value="Theravada">Theravada (Nam Tông)</option>
                        <option value="Mahayana">Mahayana (Bắc Tông)</option>
                        <option value="Vajrayana">Vajrayana (Mật Tông)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="edit_trang_thai">
                        <option value="hoat_dong">Đang hoạt động</option>
                        <option value="ngung_hoat_dong">Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="so_dien_thoai" id="edit_so_dien_thoai">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email">
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" id="edit_website">
                </div>
                <div class="form-group">
                    <label>Năm thành lập</label>
                    <input type="number" name="nam_thanh_lap" id="edit_nam_thanh_lap" min="1000" max="<?php echo date('Y'); ?>">
                </div>
                <div class="form-group full-width">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="anh_dai_dien" accept="image/*" onchange="previewImage(this, 'editPreview')">
                    <div id="editPreview" style="margin-top:12px;"></div>
                    <input type="hidden" name="anh_dai_dien_cu" id="edit_anh_dai_dien_cu">
                </div>
                <div class="form-group full-width">
                    <label>Mô tả ngắn</label>
                    <textarea name="mo_ta" id="edit_mo_ta" rows="3"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Lịch sử</label>
                    <textarea name="lich_su" id="edit_lich_su" rows="4"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Cập nhật
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
            <button class="modal-close" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p style="margin-bottom:24px;">Bạn có chắc chắn muốn xóa chùa này? Hành động này không thể hoàn tác!</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ma_chua" id="delete_ma_chua">
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Hủy</button>
                <button type="submit" class="btn-submit" style="background:var(--danger);">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<div class="toast <?php echo $messageType; ?>" id="toast" <?php if($message): ?>style="display:flex;"<?php endif; ?>>
    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
    <span><?php echo $message; ?></span>
</div>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function openEditModal() {
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Edit temple
function editTemple(temple) {
    document.getElementById('edit_ma_chua').value = temple.ma_chua;
    document.getElementById('edit_ten_chua').value = temple.ten_chua;
    document.getElementById('edit_ten_chua_khmer').value = temple.ten_chua_khmer || '';
    document.getElementById('edit_dia_chi').value = temple.dia_chi || '';
    document.getElementById('edit_tinh_thanh').value = temple.tinh_thanh || '';
    document.getElementById('edit_quan_huyen').value = temple.quan_huyen || '';
    document.getElementById('edit_loai_chua').value = temple.loai_chua || 'Theravada';
    document.getElementById('edit_trang_thai').value = temple.trang_thai || 'hoat_dong';
    document.getElementById('edit_so_dien_thoai').value = temple.so_dien_thoai || '';
    document.getElementById('edit_email').value = temple.email || '';
    document.getElementById('edit_website').value = temple.website || '';
    document.getElementById('edit_nam_thanh_lap').value = temple.nam_thanh_lap || '';
    document.getElementById('edit_anh_dai_dien_cu').value = temple.anh_dai_dien || '';
    document.getElementById('edit_mo_ta').value = temple.mo_ta || '';
    document.getElementById('edit_lich_su').value = temple.lich_su || '';
    
    // Show current image if exists
    const editPreview = document.getElementById('editPreview');
    if(temple.hinh_anh_chinh) {
        editPreview.innerHTML = `
            <div style="margin-top:12px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--gray);">Ảnh hiện tại:</label>
                <img src="${temple.hinh_anh_chinh}" style="max-width:200px; max-height:200px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=&quot;padding:20px; background:var(--gray-light); border-radius:12px; text-align:center;&quot;><i class=&quot;fas fa-image-slash&quot; style=&quot;color:var(--gray); font-size:2rem;&quot;></i><br><span style=&quot;color:var(--gray);&quot;>Ảnh không tồn tại</span></div>'">
            </div>
        `;
    } else {
        editPreview.innerHTML = '<div style="margin-top:12px; padding:15px; background:var(--gray-light); border-radius:12px; text-align:center; color:var(--gray);"><i class="fas fa-image"></i> Chưa có ảnh</div>';
    }
    
    openEditModal();
}

// Preview image function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="position:relative; display:inline-block;">
                    <img src="${e.target.result}" style="max-width:200px; border-radius:12px; box-shadow:var(--shadow);">
                    <div style="margin-top:8px; font-size:0.85rem; color:var(--success); font-weight:600;">
                        <i class="fas fa-check-circle"></i> Ảnh đã chọn
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}

// Delete temple
function deleteTemple(id) {
    document.getElementById('delete_ma_chua').value = id;
    openDeleteModal();
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#templesTable tbody tr');
    rows.forEach(row => {
        const name = row.cells[2].textContent.toLowerCase();
        const nameKhmer = row.cells[3].textContent.toLowerCase();
        const address = row.cells[4].textContent.toLowerCase();
        const match = name.includes(query) || nameKhmer.includes(query) || address.includes(query);
        row.style.display = match ? '' : 'none';
    });
});

// Filter by province
document.getElementById('provinceFilter').addEventListener('change', function() {
    applyFilters();
});

// Filter by status
document.getElementById('statusFilter').addEventListener('change', function() {
    applyFilters();
});

function applyFilters() {
    const provinceFilter = document.getElementById('provinceFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#templesTable tbody tr');
    
    rows.forEach(row => {
        const province = row.dataset.province;
        const status = row.dataset.status;
        
        const provinceMatch = !provinceFilter || province === provinceFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        row.style.display = (provinceMatch && statusMatch) ? '' : 'none';
    });
}

// Toast auto-hide
<?php if($message): ?>
setTimeout(() => {
    const toast = document.getElementById('toast');
    toast.style.animation = 'slideInRight 0.3s ease reverse';
    setTimeout(() => toast.style.display = 'none', 300);
}, 3000);
<?php endif; ?>

function logout() {
    if(confirm('Bạn có chắc muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

// Placeholder functions
function toggleNotifications(e) {
    alert('Chức năng thông báo đang được phát triển');
}

function toggleMessages(e) {
    alert('Chức năng tin nhắn đang được phát triển');
}

function toggleProfileMenu() {
    alert('Chức năng menu profile đang được phát triển');
}
</script>

</body>
</html>

