<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();

// Xử lý cập nhật cài đặt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if($action === 'update_settings') {
            // Lấy tất cả settings từ POST
            $settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_description' => $_POST['site_description'] ?? '',
                'site_keywords' => $_POST['site_keywords'] ?? '',
                'admin_email' => $_POST['admin_email'] ?? '',
                'contact_phone' => $_POST['contact_phone'] ?? '',
                'contact_address' => $_POST['contact_address'] ?? '',
                'facebook_url' => $_POST['facebook_url'] ?? '',
                'youtube_url' => $_POST['youtube_url'] ?? '',
                'twitter_url' => $_POST['twitter_url'] ?? '',
                'instagram_url' => $_POST['instagram_url'] ?? '',
                'items_per_page' => $_POST['items_per_page'] ?? 10,
                'enable_comments' => isset($_POST['enable_comments']) ? 1 : 0,
                'enable_registration' => isset($_POST['enable_registration']) ? 1 : 0,
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            ];
            
            // Cập nhật từng setting
            foreach($settings as $key => $value) {
                $sql = "INSERT INTO cai_dat_he_thong (khoa, gia_tri) VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE gia_tri = ?";
                $db->execute($sql, [$key, $value, $value]);
            }
            
            $_SESSION['flash_message'] = 'Cập nhật cài đặt thành công!';
            $_SESSION['flash_type'] = 'success';
            header('Location: caidat.php');
            exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy tất cả cài đặt hiện tại
$settings_query = "SELECT khoa, gia_tri FROM cai_dat_he_thong";
$settings_data = $db->query($settings_query) ?: [];
$settings = [];
foreach($settings_data as $row) {
    $settings[$row['khoa']] = $row['gia_tri'];
}

// Thống kê hệ thống
$system_stats = [
    'total_users' => $db->count('nguoi_dung'),
    'total_articles' => $db->count('bai_viet'),
    'total_temples' => $db->count('chua_khmer'),
    'total_lessons' => $db->count('bai_hoc'),
    'total_festivals' => $db->count('le_hoi'),
    'total_stories' => $db->count('truyen_dan_gian'),
];

// Tính kích thước database
try {
    $dbName = $db->getConnection()->query('SELECT DATABASE()')->fetchColumn();
    $sizeQuery = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size 
                  FROM information_schema.TABLES 
                  WHERE table_schema = ?";
    $sizeResult = $db->querySingle($sizeQuery, [$dbName]);
    $system_stats['database_size'] = ($sizeResult['size'] ?? 0) . ' MB';
} catch(Exception $e) {
    $system_stats['database_size'] = 'N/A';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="description" content="Cài đặt hệ thống">
<meta name="theme-color" content="#6366f1">
<title>Cài đặt Hệ thống - Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
    --info: #3b82f6;
    --dark: #1e293b;
    --dark-light: #334155;
    --gray: #64748b;
    --gray-light: #f1f5f9;
    --white: #ffffff;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    --gradient-info: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}
body {background:var(--gray-light); color:var(--dark); line-height:1.6;}

/* Layout */
.admin-wrapper {display:flex; min-height:100vh;}

/* Sidebar */
.sidebar {
    width:280px; background:var(--white); position:fixed; height:100vh;
    overflow-y:auto; box-shadow:var(--shadow-lg); z-index:1000;
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar::-webkit-scrollbar {width:6px;}
.sidebar::-webkit-scrollbar-track {background:transparent;}
.sidebar::-webkit-scrollbar-thumb {background:var(--gray); border-radius:10px;}
.sidebar-header {
    padding:28px 24px; border-bottom:1px solid var(--gray-light);
    background:var(--gradient-primary);
}
.sidebar-logo {display:flex; align-items:center; gap:14px;}
.sidebar-logo-icon {
    width:48px; height:48px; background:var(--white); border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:1.5rem;
    color:var(--primary); box-shadow:var(--shadow);
}
.sidebar-logo-icon i {animation:spin 8s linear infinite;}
@keyframes spin {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
.sidebar-logo-text h2 {
    font-size:1.3rem; font-weight:800; color:var(--white); letter-spacing:-0.5px;
}
.sidebar-logo-text p {
    font-size:0.75rem; color:rgba(255,255,255,0.8); font-weight:500;
}
.sidebar-menu {padding:20px 12px;}
.menu-section {margin-bottom:28px;}
.menu-section-title {
    padding:0 16px 12px; font-size:0.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:1px; color:var(--gray);
}
.menu-item {
    padding:12px 16px; display:flex; align-items:center; gap:14px;
    cursor:pointer; transition:all 0.3s ease; border-radius:12px; margin-bottom:6px;
}
.menu-item:hover {background:var(--gray-light); transform:translateX(4px);}
.menu-item.active {
    background:var(--gradient-primary); color:var(--white); box-shadow:var(--shadow);
}
.menu-item i {font-size:1.15rem; width:24px; text-align:center;}
.menu-item span {font-size:0.95rem; font-weight:600;}

/* Main Content */
.main-content {margin-left:280px; flex:1; min-height:100vh;}

/* Topbar */
.topbar {
    background:rgba(255,255,255,0.95); backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(0,0,0,0.05); padding:20px 32px;
    display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; z-index:999; box-shadow:0 4px 20px rgba(0,0,0,0.04);
}
.topbar-left h2 {font-size:1.5rem; font-weight:800; color:var(--dark);}
.topbar-right {display:flex; align-items:center; gap:12px;}
.admin-profile-enhanced {
    display:flex; align-items:center; gap:12px; padding:8px 14px 8px 8px;
    background:var(--white); border:2px solid var(--gray-light);
    border-radius:16px; cursor:pointer; transition:all 0.3s ease;
}
.admin-profile-enhanced:hover {box-shadow:var(--shadow); transform:translateY(-2px);}
.profile-avatar-wrapper {position:relative;}
.profile-avatar {
    width:46px; height:46px; border-radius:14px;
    background:var(--gradient-primary); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:1.05rem; box-shadow:0 4px 16px rgba(102,126,234,0.35);
    border:3px solid var(--white);
}
.online-status {
    position:absolute; bottom:0; right:0; width:14px; height:14px;
    background:var(--gradient-success); border:3px solid var(--white);
    border-radius:50%; box-shadow:0 2px 6px rgba(16,185,129,0.4);
}
.profile-info {display:flex; flex-direction:column; gap:4px;}
.profile-name {font-size:0.95rem; font-weight:700; color:var(--dark);}
.profile-role {
    font-size:0.7rem; font-weight:700; display:inline-flex; align-items:center;
    gap:5px; padding:4px 10px; border-radius:8px; text-transform:uppercase; letter-spacing:0.6px;
}
.profile-role.role-super-admin {
    background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color:#8b4513; border:1.5px solid #ffa500;
}
.profile-role.role-admin {background:var(--gradient-primary); color:var(--white);}
.profile-role.role-editor {background:var(--gradient-success); color:var(--white);}

/* Content Area */
.content-area {padding:32px; max-width:1600px; margin:0 auto;}

/* Page Header */
.page-header {
    padding:48px; background:var(--gradient-primary); border-radius:24px;
    margin-bottom:32px; color:var(--white); position:relative; overflow:hidden;
    box-shadow:0 10px 40px rgba(102, 126, 234, 0.3);
}
.page-header::before {
    content:''; position:absolute; right:-100px; top:-100px;
    width:300px; height:300px;
    background:radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius:50%;
}
.page-header::after {
    content:''; position:absolute; left:-80px; bottom:-80px;
    width:250px; height:250px;
    background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius:50%;
}
.page-header-content {position:relative; z-index:1;}
.page-title-wrapper {display:flex; align-items:center; gap:24px; margin-bottom:16px;}
.page-icon-wrapper {
    width:80px; height:80px; background:rgba(255, 255, 255, 0.2);
    backdrop-filter:blur(10px); border-radius:20px; display:flex;
    align-items:center; justify-content:center; font-size:2.5rem;
    box-shadow:0 8px 24px rgba(0, 0, 0, 0.15);
    animation:float 3s ease-in-out infinite;
}
@keyframes float {0%, 100% {transform:translateY(0);} 50% {transform:translateY(-10px);}}
.page-title-wrapper h1 {font-size:2.5rem; font-weight:900; line-height:1.2;}
.page-subtitle {font-size:1.1rem; opacity:0.95; font-weight:500; margin-left:104px;}

/* Settings Layout */
.settings-container {display:grid; grid-template-columns:320px 1fr; gap:28px;}

/* Settings Sidebar */
.settings-sidebar {
    background:var(--white); border-radius:20px; padding:24px;
    box-shadow:var(--shadow); height:fit-content; position:sticky; top:100px;
}
.settings-sidebar-title {
    font-size:0.85rem; font-weight:800; text-transform:uppercase;
    letter-spacing:1px; color:var(--gray); margin-bottom:20px;
    padding-bottom:12px; border-bottom:2px solid var(--gray-light);
}
.settings-nav-item {
    padding:16px 18px; display:flex; align-items:center; gap:14px;
    cursor:pointer; transition:all 0.3s ease; border-radius:14px;
    margin-bottom:8px; font-weight:600; font-size:0.95rem; color:var(--dark);
}
.settings-nav-item:hover {
    background:var(--gray-light); transform:translateX(6px);
}
.settings-nav-item.active {
    background:var(--gradient-primary); color:var(--white);
    box-shadow:0 4px 12px rgba(102,126,234,0.3);
}
.settings-nav-item i {font-size:1.2rem; width:28px; text-align:center;}
.settings-nav-badge {
    margin-left:auto; padding:4px 10px; background:rgba(99,102,241,0.1);
    color:var(--primary); border-radius:8px; font-size:0.75rem; font-weight:800;
}
.settings-nav-item.active .settings-nav-badge {
    background:rgba(255,255,255,0.2); color:var(--white);
}

/* Settings Content */
.settings-content {
    background:var(--white); border-radius:20px; padding:40px;
    box-shadow:var(--shadow); min-height:600px;
}
.settings-section {display:none; animation:fadeIn 0.4s ease;}
.settings-section.active {display:block;}
@keyframes fadeIn {from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);}}

.section-header {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:32px; padding-bottom:20px; border-bottom:3px solid var(--gray-light);
}
.section-title {
    font-size:1.8rem; font-weight:900; color:var(--dark);
    display:flex; align-items:center; gap:14px;
}
.section-title i {
    width:50px; height:50px; border-radius:14px; display:flex;
    align-items:center; justify-content:center; font-size:1.4rem;
    color:var(--white); background:var(--gradient-primary);
    box-shadow:0 4px 12px rgba(102,126,234,0.3);
}
.section-description {
    font-size:1rem; color:var(--gray); margin-top:8px; margin-left:64px;
}

/* Form Styles */
.form-grid {display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;}
.form-group {display:flex; flex-direction:column; gap:10px;}
.form-group.full-width {grid-column:1 / -1;}
.form-label {
    font-weight:700; font-size:0.95rem; color:var(--dark);
    display:flex; align-items:center; gap:8px;
}
.form-label i {color:var(--primary); font-size:1rem;}
.form-label .required {color:var(--danger); margin-left:4px;}
.form-input, .form-select, .form-textarea {
    padding:14px 18px; border:2px solid var(--gray-light);
    border-radius:14px; font-size:0.95rem; transition:all 0.3s ease;
    font-family:'Plus Jakarta Sans', sans-serif;
}
.form-textarea {min-height:120px; resize:vertical;}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline:none; border-color:var(--primary);
    box-shadow:0 0 0 4px rgba(99,102,241,0.1);
    transform:translateY(-2px);
}
.form-hint {
    font-size:0.85rem; color:var(--gray); display:flex;
    align-items:center; gap:6px; margin-top:4px;
}
.form-hint i {font-size:0.8rem;}

/* Switch Toggle */
.switch-container {
    display:grid; gap:16px; margin-bottom:32px;
}
.switch-group {
    display:flex; align-items:center; justify-content:space-between;
    padding:20px 24px; background:var(--gray-light); border-radius:16px;
    transition:all 0.3s ease;
}
.switch-group:hover {
    background:linear-gradient(135deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
    transform:translateX(4px);
}
.switch-label-wrapper {display:flex; flex-direction:column; gap:6px;}
.switch-label {
    font-weight:700; font-size:1rem; color:var(--dark);
    display:flex; align-items:center; gap:10px;
}
.switch-label i {
    width:36px; height:36px; border-radius:10px; display:flex;
    align-items:center; justify-content:center; font-size:1rem;
    color:var(--white); background:var(--gradient-primary);
}
.switch-description {font-size:0.85rem; color:var(--gray); margin-left:46px;}
.switch {position:relative; display:inline-block; width:60px; height:32px;}
.switch input {opacity:0; width:0; height:0;}
.slider {
    position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#cbd5e1; transition:0.4s; border-radius:32px;
}
.slider:before {
    position:absolute; content:""; height:24px; width:24px;
    left:4px; bottom:4px; background:white; transition:0.4s;
    border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .slider {background:var(--primary);}
input:checked + .slider:before {transform:translateX(28px);}

/* Stats Cards */
.stats-grid {display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:32px;}
.stat-card {
    padding:24px; background:var(--white); border:2px solid var(--gray-light);
    border-radius:16px; transition:all 0.3s ease; cursor:pointer;
}
.stat-card:hover {
    border-color:var(--primary); transform:translateY(-4px);
    box-shadow:0 8px 24px rgba(99,102,241,0.15);
}
.stat-card-header {display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;}
.stat-card-icon {
    width:56px; height:56px; border-radius:14px; display:flex;
    align-items:center; justify-content:center; font-size:1.5rem;
    color:var(--white); box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
.stat-card-value {font-size:2.2rem; font-weight:900; color:var(--dark); margin-bottom:8px;}
.stat-card-label {
    font-size:0.9rem; color:var(--gray); font-weight:600;
    text-transform:uppercase; letter-spacing:0.5px;
}

/* System Info */
.system-info-grid {display:grid; gap:16px; margin-bottom:32px;}
.system-info-item {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 24px; background:var(--gray-light); border-radius:14px;
    transition:all 0.3s ease;
}
.system-info-item:hover {
    background:linear-gradient(135deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
    transform:translateX(4px);
}
.system-info-label {
    font-weight:700; font-size:0.95rem; color:var(--dark);
    display:flex; align-items:center; gap:10px;
}
.system-info-label i {
    width:36px; height:36px; border-radius:10px; display:flex;
    align-items:center; justify-content:center; font-size:1rem;
    color:var(--white); background:var(--gradient-info);
}
.system-info-value {
    font-weight:700; font-size:1rem; color:var(--primary);
    padding:8px 16px; background:var(--white); border-radius:10px;
}

/* Buttons */
.btn-save {
    padding:16px 40px; background:var(--gradient-primary); color:var(--white);
    border:none; border-radius:14px; font-weight:700; font-size:1.05rem;
    cursor:pointer; transition:all 0.3s ease; display:inline-flex;
    align-items:center; gap:12px; box-shadow:0 4px 12px rgba(102,126,234,0.3);
}
.btn-save:hover {
    transform:translateY(-3px); box-shadow:0 8px 24px rgba(102,126,234,0.4);
}
.btn-save i {font-size:1.2rem;}
.btn-save:disabled {
    opacity:0.6; cursor:not-allowed; transform:none;
}

/* Toast */
.toast {
    position:fixed; top:90px; right:32px; background:var(--white);
    padding:20px 28px; border-radius:16px; box-shadow:var(--shadow-lg);
    display:none; align-items:center; gap:14px; z-index:10000;
    animation:slideInRight 0.3s ease; min-width:320px;
}
.toast.success {border-left:5px solid var(--success);}
.toast.error {border-left:5px solid var(--danger);}
.toast i {font-size:1.8rem;}
.toast.success i {color:var(--success);}
.toast.error i {color:var(--danger);}
.toast-content {flex:1;}
.toast-title {font-weight:800; font-size:1rem; margin-bottom:4px;}
.toast-message {font-size:0.9rem; color:var(--gray);}
@keyframes slideInRight {
    from {transform:translateX(400px); opacity:0;}
    to {transform:translateX(0); opacity:1;}
}

/* Responsive */
@media(max-width:1200px){
    .settings-container {grid-template-columns:1fr;}
    .settings-sidebar {position:static;}
    .form-grid {grid-template-columns:1fr;}
    .stats-grid {grid-template-columns:repeat(2, 1fr);}
}
@media(max-width:768px){
    .sidebar {left:-280px; width:280px;}
    .sidebar.mobile-show {left:0;}
    .main-content {margin-left:0;}
    .stats-grid {grid-template-columns:1fr;}
    .page-header {padding:32px 24px;}
    .page-title-wrapper h1 {font-size:1.8rem;}
    .settings-content {padding:24px;}
}
</style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon"><i class="fas fa-dharmachakra"></i></div>
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
                    <i class="fas fa-home"></i><span>Trang chủ</span>
                </div>
                <div class="menu-item" onclick="location.href='vanhoa.php'">
                    <i class="fas fa-book-open"></i><span>Văn hóa Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='chua.php'">
                    <i class="fas fa-place-of-worship"></i><span>Chùa Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='lehoi.php'">
                    <i class="fas fa-calendar-check"></i><span>Lễ hội</span>
                </div>
                <div class="menu-item" onclick="location.href='hoctiengkhmer.php'">
                    <i class="fas fa-graduation-cap"></i><span>Học tiếng Khmer</span>
                </div>
                <div class="menu-item" onclick="location.href='truyendangian.php'">
                    <i class="fas fa-book-reader"></i><span>Truyện dân gian</span>
                </div>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Quản lý</div>
                <div class="menu-item" onclick="location.href='nguoidung.php'">
                    <i class="fas fa-users"></i><span>Người dùng</span>
                </div>
                <div class="menu-item" onclick="location.href='thongbao.php'">
                    <i class="fas fa-bell"></i><span>Thông báo</span>
                </div>
                <div class="menu-item" onclick="location.href='tinnhan.php'">
                    <i class="fas fa-comments"></i><span>Tin nhắn</span>
                </div>
                <div class="menu-item" onclick="location.href='hoatdong.php'">
                    <i class="fas fa-history"></i><span>Hoạt động</span>
                </div>
                <div class="menu-item active" onclick="location.href='caidat.php'">
                    <i class="fas fa-cog"></i><span>Cài đặt</span>
                </div>
            </div>
            <div class="menu-section">
                <div class="menu-item" onclick="logout()" style="color:var(--danger);">
                    <i class="fas fa-sign-out-alt"></i><span>Đăng xuất</span>
                </div>
            </div>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h2><i class="fas fa-cog" style="color:var(--primary); margin-right:12px;"></i>Cài đặt Hệ thống</h2>
            </div>
            <div class="topbar-right">
                <div class="admin-profile-enhanced">
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
                        <div class="page-icon-wrapper"><i class="fas fa-cog"></i></div>
                        <div>
                            <h1>Cài đặt & Cấu hình</h1>
                        </div>
                    </div>
                    <p class="page-subtitle">Quản lý cấu hình hệ thống, thông tin website và các tùy chỉnh nâng cao</p>
                </div>
            </div>

            <!-- SETTINGS CONTAINER -->
            <div class="settings-container">
                <!-- SETTINGS SIDEBAR -->
                <div class="settings-sidebar">
                    <div class="settings-sidebar-title">Danh mục cài đặt</div>
                    <div class="settings-nav-item active" data-section="general">
                        <i class="fas fa-globe"></i>
                        <span>Thông tin chung</span>
                    </div>
                    <div class="settings-nav-item" data-section="contact">
                        <i class="fas fa-address-book"></i>
                        <span>Liên hệ</span>
                    </div>
                    <div class="settings-nav-item" data-section="social">
                        <i class="fas fa-share-alt"></i>
                        <span>Mạng xã hội</span>
                    </div>
                    <div class="settings-nav-item" data-section="system">
                        <i class="fas fa-sliders-h"></i>
                        <span>Hệ thống</span>
                    </div>
                    <div class="settings-nav-item" data-section="stats">
                        <i class="fas fa-chart-bar"></i>
                        <span>Thống kê</span>
                        <span class="settings-nav-badge"><?php echo $system_stats['total_users']; ?></span>
                    </div>
                </div>

                <!-- SETTINGS CONTENT -->
                <div class="settings-content">
                    <form method="POST" id="settingsForm">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <!-- THÔNG TIN CHUNG -->
                        <div class="settings-section active" id="section-general">
                            <div class="section-header">
                                <div>
                                    <h2 class="section-title">
                                        <i class="fas fa-globe"></i>
                                        Thông tin chung
                                    </h2>
                                    <p class="section-description">Cấu hình thông tin cơ bản của website</p>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i>
                                        Tên website
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="site_name" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Văn Hóa Khmer Nam Bộ'); ?>" 
                                           required placeholder="Nhập tên website">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Tên hiển thị trên thanh tiêu đề trình duyệt và kết quả tìm kiếm
                                    </span>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i>
                                        Mô tả website
                                    </label>
                                    <textarea name="site_description" class="form-textarea" 
                                              placeholder="Nhập mô tả ngắn gọn về website"><?php echo htmlspecialchars($settings['site_description'] ?? 'Hệ thống quản lý và học tập văn hóa Khmer Nam Bộ'); ?></textarea>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Mô tả ngắn gọn giúp tối ưu SEO và hiển thị trên kết quả tìm kiếm
                                    </span>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <i class="fas fa-tags"></i>
                                        Từ khóa SEO
                                    </label>
                                    <input type="text" name="site_keywords" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['site_keywords'] ?? 'văn hóa khmer, khmer nam bộ, học tiếng khmer'); ?>" 
                                           placeholder="văn hóa, khmer, giáo dục...">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Các từ khóa cách nhau bởi dấu phẩy, giúp tối ưu hóa công cụ tìm kiếm
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Lưu thay đổi
                            </button>
                        </div>

                        <!-- LIÊN HỆ -->
                        <div class="settings-section" id="section-contact">
                            <div class="section-header">
                                <div>
                                    <h2 class="section-title">
                                        <i class="fas fa-address-book"></i>
                                        Thông tin liên hệ
                                    </h2>
                                    <p class="section-description">Cấu hình thông tin liên hệ hiển thị trên website</p>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email liên hệ
                                    </label>
                                    <input type="email" name="admin_email" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@khmer.vn'); ?>" 
                                           placeholder="email@example.com">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Email chính để người dùng liên hệ
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Số điện thoại
                                    </label>
                                    <input type="text" name="contact_phone" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '0123456789'); ?>" 
                                           placeholder="0123456789">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Số điện thoại hỗ trợ khách hàng
                                    </span>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Địa chỉ
                                    </label>
                                    <textarea name="contact_address" class="form-textarea" 
                                              placeholder="Nhập địa chỉ đầy đủ"><?php echo htmlspecialchars($settings['contact_address'] ?? 'TP. Hồ Chí Minh, Việt Nam'); ?></textarea>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Địa chỉ văn phòng hoặc trụ sở chính
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Lưu thay đổi
                            </button>
                        </div>

                        <!-- MẠNG XÃ HỘI -->
                        <div class="settings-section" id="section-social">
                            <div class="section-header">
                                <div>
                                    <h2 class="section-title">
                                        <i class="fas fa-share-alt"></i>
                                        Mạng xã hội
                                    </h2>
                                    <p class="section-description">Liên kết các trang mạng xã hội của bạn</p>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-facebook"></i>
                                        Facebook
                                    </label>
                                    <input type="url" name="facebook_url" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" 
                                           placeholder="https://facebook.com/yourpage">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Link đến trang Facebook của bạn
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-youtube"></i>
                                        YouTube
                                    </label>
                                    <input type="url" name="youtube_url" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" 
                                           placeholder="https://youtube.com/yourchannel">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Link đến kênh YouTube của bạn
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-twitter"></i>
                                        Twitter / X
                                    </label>
                                    <input type="url" name="twitter_url" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" 
                                           placeholder="https://twitter.com/yourprofile">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Link đến tài khoản Twitter/X
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-instagram"></i>
                                        Instagram
                                    </label>
                                    <input type="url" name="instagram_url" class="form-input" 
                                           value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" 
                                           placeholder="https://instagram.com/yourprofile">
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Link đến tài khoản Instagram
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Lưu thay đổi
                            </button>
                        </div>

                        <!-- HỆ THỐNG -->
                        <div class="settings-section" id="section-system">
                            <div class="section-header">
                                <div>
                                    <h2 class="section-title">
                                        <i class="fas fa-sliders-h"></i>
                                        Cài đặt hệ thống
                                    </h2>
                                    <p class="section-description">Cấu hình các tùy chọn hoạt động của hệ thống</p>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-list-ol"></i>
                                        Số mục trên mỗi trang
                                    </label>
                                    <select name="items_per_page" class="form-select">
                                        <option value="10" <?php echo ($settings['items_per_page'] ?? 10) == 10 ? 'selected' : ''; ?>>10 mục</option>
                                        <option value="20" <?php echo ($settings['items_per_page'] ?? 10) == 20 ? 'selected' : ''; ?>>20 mục</option>
                                        <option value="50" <?php echo ($settings['items_per_page'] ?? 10) == 50 ? 'selected' : ''; ?>>50 mục</option>
                                        <option value="100" <?php echo ($settings['items_per_page'] ?? 10) == 100 ? 'selected' : ''; ?>>100 mục</option>
                                    </select>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Số lượng mục hiển thị trên mỗi trang danh sách
                                    </span>
                                </div>
                            </div>
                            
                            <div class="switch-container">
                                <div class="switch-group">
                                    <div class="switch-label-wrapper">
                                        <span class="switch-label">
                                            <i class="fas fa-comments"></i>
                                            Cho phép bình luận
                                        </span>
                                        <span class="switch-description">Người dùng có thể bình luận trên bài viết</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="enable_comments" <?php echo ($settings['enable_comments'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="switch-group">
                                    <div class="switch-label-wrapper">
                                        <span class="switch-label">
                                            <i class="fas fa-user-plus"></i>
                                            Cho phép đăng ký
                                        </span>
                                        <span class="switch-description">Người dùng mới có thể tạo tài khoản</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="enable_registration" <?php echo ($settings['enable_registration'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="switch-group">
                                    <div class="switch-label-wrapper">
                                        <span class="switch-label">
                                            <i class="fas fa-tools"></i>
                                            Chế độ bảo trì
                                        </span>
                                        <span class="switch-description">Tạm khóa website để bảo trì (chỉ admin truy cập được)</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="maintenance_mode" <?php echo ($settings['maintenance_mode'] ?? 0) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Lưu thay đổi
                            </button>
                        </div>

                        <!-- THỐNG KÊ -->
                        <div class="settings-section" id="section-stats">
                            <div class="section-header">
                                <div>
                                    <h2 class="section-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Thống kê hệ thống
                                    </h2>
                                    <p class="section-description">Tổng quan về dữ liệu và hoạt động của hệ thống</p>
                                </div>
                            </div>
                            
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_users']); ?></div>
                                    <div class="stat-card-label">Người dùng</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                            <i class="fas fa-newspaper"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_articles']); ?></div>
                                    <div class="stat-card-label">Bài viết</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                            <i class="fas fa-place-of-worship"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_temples']); ?></div>
                                    <div class="stat-card-label">Chùa Khmer</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_lessons']); ?></div>
                                    <div class="stat-card-label">Bài học</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_festivals']); ?></div>
                                    <div class="stat-card-label">Lễ hội</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div class="stat-card-icon" style="background:linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                                            <i class="fas fa-book-reader"></i>
                                        </div>
                                    </div>
                                    <div class="stat-card-value"><?php echo number_format($system_stats['total_stories']); ?></div>
                                    <div class="stat-card-label">Truyện dân gian</div>
                                </div>
                            </div>
                            
                            <div style="margin-top:32px;">
                                <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
                                    <i class="fas fa-server" style="color:var(--primary);"></i>
                                    Thông tin hệ thống
                                </h3>
                                <div class="system-info-grid">
                                    <div class="system-info-item">
                                        <span class="system-info-label">
                                            <i class="fab fa-php"></i>
                                            Phiên bản PHP
                                        </span>
                                        <span class="system-info-value"><?php echo phpversion(); ?></span>
                                    </div>
                                    <div class="system-info-item">
                                        <span class="system-info-label">
                                            <i class="fas fa-database"></i>
                                            Database
                                        </span>
                                        <span class="system-info-value">MySQL <?php echo $db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                                    </div>
                                    <div class="system-info-item">
                                        <span class="system-info-label">
                                            <i class="fas fa-hdd"></i>
                                            Kích thước Database
                                        </span>
                                        <span class="system-info-value"><?php echo $system_stats['database_size']; ?></span>
                                    </div>
                                    <div class="system-info-item">
                                        <span class="system-info-label">
                                            <i class="fas fa-server"></i>
                                            Server
                                        </span>
                                        <span class="system-info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- TOAST NOTIFICATION -->
<?php if($message): ?>
<div class="toast <?php echo $messageType; ?>" id="toast" style="display:flex;">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <div class="toast-content">
        <div class="toast-title"><?php echo $messageType === 'success' ? 'Thành công!' : 'Lỗi!'; ?></div>
        <div class="toast-message"><?php echo htmlspecialchars($message); ?></div>
    </div>
</div>
<?php endif; ?>

<script>
// Tab switching với animation
document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', function() {
        // Remove active class
        document.querySelectorAll('.settings-nav-item').forEach(nav => nav.classList.remove('active'));
        document.querySelectorAll('.settings-section').forEach(section => section.classList.remove('active'));
        
        // Add active class
        this.classList.add('active');
        
        // Show section với animation
        const sectionId = 'section-' + this.dataset.section;
        const section = document.getElementById(sectionId);
        section.classList.add('active');
        
        // Scroll to top
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
});

// Toast notification
const toast = document.getElementById('toast');
if(toast) {
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }, 3000);
}

// Logout function
function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'dangnhap.php?logout=1';
    }
}

// Form validation
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const siteName = document.querySelector('input[name="site_name"]');
    if(siteName && !siteName.value.trim()) {
        e.preventDefault();
        alert('Vui lòng nhập tên website!');
        siteName.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('.btn-save');
    if(submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    }
});

// Auto-save indicator
let saveTimeout;
let hasChanges = false;

document.querySelectorAll('input, textarea, select').forEach(input => {
    input.addEventListener('change', function() {
        hasChanges = true;
        clearTimeout(saveTimeout);
        
        // Update all save buttons
        document.querySelectorAll('.btn-save').forEach(btn => {
            const originalHTML = btn.getAttribute('data-original') || btn.innerHTML;
            if(!btn.getAttribute('data-original')) {
                btn.setAttribute('data-original', originalHTML);
            }
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Có thay đổi chưa lưu';
            btn.style.background = 'var(--gradient-warning)';
        });
    });
});

// Warn before leaving if has unsaved changes
window.addEventListener('beforeunload', function(e) {
    if(hasChanges) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Reset changes flag on form submit
document.getElementById('settingsForm').addEventListener('submit', function() {
    hasChanges = false;
});

// Smooth scroll for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    });
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save
    if((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const activeSection = document.querySelector('.settings-section.active');
        if(activeSection) {
            const form = activeSection.closest('form');
            if(form) form.submit();
        }
    }
});

// Initialize tooltips
document.querySelectorAll('[title]').forEach(element => {
    element.addEventListener('mouseenter', function() {
        const title = this.getAttribute('title');
        if(title) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = title;
            tooltip.style.cssText = `
                position: absolute;
                background: var(--dark);
                color: var(--white);
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 0.85rem;
                z-index: 10000;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, {once: true});
        }
    });
});

// Console log for debugging
console.log('%c⚙️ Cài đặt Hệ thống', 'font-size: 20px; font-weight: bold; color: #6366f1;');
console.log('%cHệ thống đã sẵn sàng!', 'font-size: 14px; color: #10b981;');
</script>

</body>
</html>
