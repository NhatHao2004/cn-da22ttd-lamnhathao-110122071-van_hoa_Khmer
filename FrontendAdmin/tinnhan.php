<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();

// Xử lý các hành động với PRG Pattern (Post-Redirect-Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'send_message':
                $ma_nguoi_nhan = $_POST['ma_nguoi_nhan'] ?? 0;
                $noi_dung = trim($_POST['noi_dung'] ?? '');
                
                // Validate
                if(empty($ma_nguoi_nhan) || empty($noi_dung)) {
                    $_SESSION['flash_message'] = 'Vui lòng điền đầy đủ thông tin!';
                    $_SESSION['flash_type'] = 'error';
                    header('Location: tinnhan.php');
                    exit;
                }
                
                $sql = "INSERT INTO tin_nhan (ma_nguoi_gui, loai_nguoi_gui, ma_nguoi_nhan, loai_nguoi_nhan, noi_dung, trang_thai) 
                        VALUES (?, 'admin', ?, 'user', ?, 'chua_doc')";
                $db->execute($sql, [$_SESSION['admin_id'], $ma_nguoi_nhan, $noi_dung]);
                $_SESSION['flash_message'] = 'Đã gửi tin nhắn!';
                $_SESSION['flash_type'] = 'success';
                header('Location: tinnhan.php');
                exit;
                
            case 'mark_read':
                $ma_tin_nhan = $_POST['ma_tin_nhan'] ?? 0;
                $sql = "UPDATE tin_nhan SET trang_thai = 'da_doc' WHERE ma_tin_nhan = ?";
                $db->execute($sql, [$ma_tin_nhan]);
                $_SESSION['flash_message'] = 'Đã đánh dấu đã đọc!';
                $_SESSION['flash_type'] = 'success';
                header('Location: tinnhan.php');
                exit;
                
            case 'mark_all_read':
                $sql = "UPDATE tin_nhan SET trang_thai = 'da_doc' 
                        WHERE ma_nguoi_nhan = ? AND loai_nguoi_nhan = 'admin' AND trang_thai = 'chua_doc'";
                $db->execute($sql, [$_SESSION['admin_id']]);
                $_SESSION['flash_message'] = 'Đã đánh dấu tất cả đã đọc!';
                $_SESSION['flash_type'] = 'success';
                header('Location: tinnhan.php');
                exit;
                
            case 'delete':
                $ma_tin_nhan = $_POST['ma_tin_nhan'] ?? 0;
                $sql = "DELETE FROM tin_nhan WHERE ma_tin_nhan = ?";
                $db->execute($sql, [$ma_tin_nhan]);
                $_SESSION['flash_message'] = 'Đã xóa tin nhắn!';
                $_SESSION['flash_type'] = 'success';
                header('Location: tinnhan.php');
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

// Test kết nối database
try {

    // Lấy danh sách tin nhắn - Query đơn giản nhất (sắp xếp theo ID)
    $sql = "SELECT * FROM tin_nhan ORDER BY ma_tin_nhan DESC LIMIT 100";
    $messages = $db->query($sql);
    
    // Kiểm tra kết quả
    if($messages === false) {
        // Query failed
        $error_msg = "Query failed - check error log";
        $messages = [];
    } elseif(!is_array($messages)) {
        // Unexpected result
        $error_msg = "Unexpected result type: " . gettype($messages);
        $messages = [];
    } elseif(empty($messages)) {
        // Query succeeded but no data
        $error_msg = "Query succeeded but returned 0 rows";
    } else {
        // Success
        $error_msg = "Query succeeded with " . count($messages) . " rows";
    }
    

} catch(Exception $e) {
    $messages = [];
    $error_msg = "Exception: " . $e->getMessage();
    echo "<!-- ERROR: " . $error_msg . " -->";
}

// Xử lý tất cả logic trong 1 vòng lặp duy nhất
$admin_id = $_SESSION['admin_id'];
$unread_count = 0;
$sent_count = 0;

foreach($messages as &$msg) {
    // 1. Lấy tên người gửi
    if($msg['loai_nguoi_gui'] === 'user') {
        $user = $db->querySingle("SELECT ho_ten, ten_dang_nhap FROM nguoi_dung WHERE ma_nguoi_dung = ?", [$msg['ma_nguoi_gui']]);
        if($user) {
            $msg['ten_nguoi_gui'] = $user['ho_ten'] ?: $user['ten_dang_nhap'] ?: 'Người dùng';
        } else {
            $msg['ten_nguoi_gui'] = 'Người dùng #' . $msg['ma_nguoi_gui'];
        }
    } else {
        $admin = $db->querySingle("SELECT ho_ten FROM quan_tri_vien WHERE ma_qtv = ?", [$msg['ma_nguoi_gui']]);
        if($admin) {
            $msg['ten_nguoi_gui'] = $admin['ho_ten'] ?: 'Admin';
        } else {
            $msg['ten_nguoi_gui'] = 'Admin #' . $msg['ma_nguoi_gui'];
        }
    }
    
    // 2. Lấy tên người nhận
    if($msg['loai_nguoi_nhan'] === 'user') {
        $user = $db->querySingle("SELECT ho_ten, ten_dang_nhap FROM nguoi_dung WHERE ma_nguoi_dung = ?", [$msg['ma_nguoi_nhan']]);
        if($user) {
            $msg['ten_nguoi_nhan'] = $user['ho_ten'] ?: $user['ten_dang_nhap'] ?: 'Người dùng';
        } else {
            $msg['ten_nguoi_nhan'] = 'Người dùng #' . $msg['ma_nguoi_nhan'];
        }
    } else {
        $admin = $db->querySingle("SELECT ho_ten FROM quan_tri_vien WHERE ma_qtv = ?", [$msg['ma_nguoi_nhan']]);
        if($admin) {
            $msg['ten_nguoi_nhan'] = $admin['ho_ten'] ?: 'Admin';
        } else {
            $msg['ten_nguoi_nhan'] = 'Admin #' . $msg['ma_nguoi_nhan'];
        }
    }
    
    // 3. Format thời gian
    $msg['time_ago'] = 'Vừa xong';
    
    // 4. Xác định loại tin nhắn (nhận hay gửi)
    $msg['is_received'] = ($msg['ma_nguoi_nhan'] == $admin_id && $msg['loai_nguoi_nhan'] === 'admin');
    
    // 5. Đếm tin nhắn chưa đọc
    if($msg['trang_thai'] === 'chua_doc' && $msg['is_received']) {
        $unread_count++;
    }
    
    // 6. Đếm tin nhắn đã gửi
    if($msg['ma_nguoi_gui'] == $admin_id && $msg['loai_nguoi_gui'] === 'admin') {
        $sent_count++;
    }
}

// Lấy danh sách người dùng để gửi tin nhắn
$users = $db->query("SELECT ma_nguoi_dung, ho_ten, ten_dang_nhap FROM nguoi_dung WHERE trang_thai = 'hoat_dong' ORDER BY ho_ten") ?: [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tin nhắn - Admin</title>
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
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --dark: #1e293b;
    --gray: #64748b;
    --gray-light: #f1f5f9;
    --white: #ffffff;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
body {background:var(--gray-light); color:var(--dark); line-height:1.6;}
.admin-wrapper {display:flex; min-height:100vh;}
.sidebar {
    width:280px; background:var(--white); position:fixed; height:100vh;
    overflow-y:auto; box-shadow:var(--shadow-lg); z-index:1000;
}
.sidebar::-webkit-scrollbar {width:6px;}
.sidebar::-webkit-scrollbar-thumb {background:var(--gray); border-radius:10px;}
.sidebar-header {padding:28px 24px; border-bottom:1px solid var(--gray-light); background:var(--gradient-primary);}
.sidebar-logo {display:flex; align-items:center; gap:14px;}
.sidebar-logo-icon {
    width:48px; height:48px; background:var(--white); border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:1.5rem;
    color:var(--primary); box-shadow:var(--shadow);
}
.sidebar-logo-icon i {animation:spin 8s linear infinite;}
@keyframes spin {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
.sidebar-logo-text h2 {font-size:1.3rem; font-weight:800; color:var(--white); letter-spacing:-0.5px;}
.sidebar-logo-text p {font-size:0.75rem; color:rgba(255,255,255,0.8); font-weight:500;}
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
.menu-item.active {background:var(--gradient-primary); color:var(--white); box-shadow:var(--shadow);}
.menu-item i {font-size:1.15rem; width:24px; text-align:center;}
.menu-item span {font-size:0.95rem; font-weight:600;}

.main-content {margin-left:280px; flex:1; min-height:100vh;}
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
    background:var(--white); border:2px solid var(--gray-light); border-radius:16px;
}
.profile-avatar-wrapper {position:relative;}
.profile-avatar {
    width:46px; height:46px; border-radius:14px; background:var(--gradient-primary);
    color:var(--white); display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:1.05rem; border:3px solid var(--white);
}
.online-status {
    position:absolute; bottom:0; right:0; width:14px; height:14px;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    border:3px solid var(--white); border-radius:50%;
}
.profile-info {display:flex; flex-direction:column; gap:4px;}
.profile-name {font-size:0.95rem; font-weight:700; color:var(--dark);}
.profile-role {
    font-size:0.7rem; font-weight:700; padding:4px 10px;
    border-radius:8px; text-transform:uppercase;
}
.profile-role.role-super-admin {background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color:#8b4513;}
.profile-role.role-admin {background:var(--gradient-primary); color:var(--white);}
.profile-role.role-editor {background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:var(--white);}
.content-area {padding:32px; max-width:1400px; margin:0 auto;}
.page-header {
    padding:48px; background:var(--gradient-primary); border-radius:24px;
    margin-bottom:32px; color:var(--white); position:relative; overflow:hidden;
    box-shadow:0 10px 40px rgba(102, 126, 234, 0.3);
}
.page-header::before {
    content:''; position:absolute; right:-100px; top:-100px; width:300px; height:300px;
    background:radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius:50%;
}
.page-header-content {position:relative; z-index:1; display:flex; justify-content:space-between; align-items:center;}
.page-title-wrapper {display:flex; align-items:center; gap:24px;}
.page-icon-wrapper {
    width:80px; height:80px; background:rgba(255, 255, 255, 0.2);
    backdrop-filter:blur(10px); border-radius:20px; display:flex;
    align-items:center; justify-content:center; font-size:2.5rem;
    animation:float 3s ease-in-out infinite;
}
@keyframes float {0%, 100% {transform:translateY(0);} 50% {transform:translateY(-10px);}}
.page-title-wrapper h1 {font-size:2.5rem; font-weight:900;}
.page-subtitle {font-size:1.1rem; opacity:0.95; font-weight:500; margin-top:8px;}
.btn-new-message {
    padding:14px 28px; background:var(--white); color:var(--primary);
    border:none; border-radius:14px; font-weight:700; cursor:pointer;
    display:flex; align-items:center; gap:10px; transition:all 0.3s ease;
}
.btn-new-message:hover {transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,255,255,0.3);}

.msg-stats {display:flex; gap:20px; margin-bottom:32px;}
.msg-stat-card {
    flex:1; padding:24px; background:var(--white); border-radius:20px;
    box-shadow:var(--shadow); display:flex; align-items:center; gap:20px;
    transition:all 0.3s ease; cursor:pointer;
}
.msg-stat-card:hover {transform:translateY(-4px); box-shadow:var(--shadow-lg);}
.msg-stat-icon {
    width:64px; height:64px; border-radius:16px; display:flex;
    align-items:center; justify-content:center; font-size:1.8rem; color:var(--white);
}
.msg-stat-info h3 {font-size:0.85rem; color:var(--gray); font-weight:600; text-transform:uppercase; margin-bottom:8px;}
.msg-stat-info p {font-size:2.2rem; font-weight:900; color:var(--dark);}
.messages-container {background:var(--white); border-radius:20px; padding:32px; box-shadow:var(--shadow);}
.messages-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid var(--gray-light);
}
.messages-header h2 {font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:12px;}
.btn-mark-all {
    padding:12px 24px; background:var(--gradient-primary); color:var(--white);
    border:none; border-radius:12px; font-weight:700; cursor:pointer;
    transition:all 0.3s ease; display:flex; align-items:center; gap:8px;
}
.btn-mark-all:hover {transform:translateY(-2px); box-shadow:0 8px 20px rgba(99,102,241,0.3);}
.message-list {display:flex; flex-direction:column; gap:16px;}
.message-item {
    padding:20px; border-radius:16px; border:2px solid var(--gray-light);
    transition:all 0.3s ease; display:flex; gap:16px; align-items:flex-start;
    position:relative;
}
.message-item:hover {border-color:var(--primary); transform:translateX(4px); box-shadow:0 4px 12px rgba(99,102,241,0.15);}
.message-item.unread {background:linear-gradient(135deg, rgba(99,102,241,0.05) 0%, rgba(118,75,162,0.05) 100%);}
.message-item.sent {background:linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(5,150,105,0.05) 100%);}
.msg-avatar {
    width:48px; height:48px; border-radius:12px; display:flex;
    align-items:center; justify-content:center; font-size:1.3rem;
    color:var(--white); flex-shrink:0; font-weight:800;
}
.msg-content {flex:1;}
.msg-header {display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;}
.msg-sender {font-size:1.05rem; font-weight:700; color:var(--dark);}
.msg-badge {
    padding:4px 12px; border-radius:8px; font-size:0.75rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.5px;
}
.msg-badge.received {background:rgba(59,130,246,0.1); color:var(--info);}
.msg-badge.sent {background:rgba(16,185,129,0.1); color:var(--success);}
.msg-text {font-size:0.95rem; color:var(--gray); line-height:1.6; margin-bottom:8px;}
.msg-time {font-size:0.85rem; color:var(--gray); display:flex; align-items:center; gap:6px;}
.msg-actions {display:flex; gap:8px; flex-shrink:0;}
.btn-msg-action {
    width:36px; height:36px; border:none; border-radius:10px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all 0.3s ease;
}
.btn-msg-action:hover {transform:scale(1.1);}
.btn-mark-read {background:rgba(16,185,129,0.1); color:var(--success);}
.btn-mark-read:hover {background:var(--success); color:var(--white);}
.btn-delete {background:rgba(239,68,68,0.1); color:var(--danger);}
.btn-delete:hover {background:var(--danger); color:var(--white);}
.empty-state {text-align:center; padding:60px 20px;}
.empty-state i {font-size:5rem; color:var(--gray-light); margin-bottom:20px;}
.empty-state h3 {font-size:1.5rem; font-weight:700; color:var(--dark); margin-bottom:12px;}
.empty-state p {font-size:1rem; color:var(--gray);}

.modal {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); z-index:9999;
    align-items:center; justify-content:center; animation:fadeIn 0.3s ease;
}
@keyframes fadeIn {from {opacity:0;} to {opacity:1;}}
.modal-content {
    background:var(--white); border-radius:24px; padding:36px;
    width:600px; max-width:90%; max-height:90vh; overflow-y:auto;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:slideUp 0.3s ease;
}
@keyframes slideUp {from {transform:translateY(50px); opacity:0;} to {transform:translateY(0); opacity:1;}}
.modal-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:24px; padding-bottom:20px; border-bottom:2px solid var(--gray-light);
}
.modal-header h3 {font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:12px;}
.modal-close {
    width:40px; height:40px; background:var(--gray-light); border:none;
    border-radius:12px; cursor:pointer; display:flex; align-items:center;
    justify-content:center; transition:all 0.3s ease;
}
.modal-close:hover {background:var(--danger); color:var(--white); transform:rotate(90deg);}
.form-group {display:flex; flex-direction:column; gap:10px; margin-bottom:20px;}
.form-label {font-weight:700; font-size:0.95rem; color:var(--dark);}
.form-select, .form-textarea {
    padding:14px 18px; border:2px solid var(--gray-light); border-radius:14px;
    font-size:0.95rem; transition:all 0.3s ease; font-family:'Plus Jakarta Sans', sans-serif;
}
.form-textarea {min-height:150px; resize:vertical;}
.form-select:focus, .form-textarea:focus {
    outline:none; border-color:var(--primary); box-shadow:0 0 0 4px rgba(99,102,241,0.1);
}
.form-actions {display:flex; gap:12px; justify-content:flex-end; margin-top:24px;}
.btn-submit {
    padding:14px 32px; background:var(--gradient-primary); color:var(--white);
    border:none; border-radius:12px; font-weight:700; cursor:pointer;
    transition:all 0.3s ease; display:flex; align-items:center; gap:10px;
}
.btn-submit:hover {transform:translateY(-2px); box-shadow:var(--shadow-lg);}
.btn-cancel {
    padding:14px 32px; background:var(--gray-light); color:var(--dark);
    border:none; border-radius:12px; font-weight:700; cursor:pointer;
}
.toast {
    position:fixed; top:90px; right:32px; background:var(--white);
    padding:20px 28px; border-radius:16px; box-shadow:var(--shadow-lg);
    display:none; align-items:center; gap:14px; z-index:10000;
    animation:slideInRight 0.3s ease; min-width:320px;
}
.toast.success {border-left:5px solid var(--success);}
.toast.error {border-left:5px solid var(--danger);}
.toast i {font-size:1.5rem;}
.toast.success i {color:var(--success);}
.toast.error i {color:var(--danger);}
@keyframes slideInRight {from {transform:translateX(400px); opacity:0;} to {transform:translateX(0); opacity:1;}}
@media(max-width:768px){
    .sidebar {left:-280px;}
    .main-content {margin-left:0;}
    .msg-stats {flex-direction:column;}
    .page-header {padding:32px 24px;}
    .messages-container {padding:20px;}
}
</style>
</head>
<body>

<div class="admin-wrapper">
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
                <div class="menu-item" onclick="location.href='index.php'"><i class="fas fa-home"></i><span>Trang chủ</span></div>
                <div class="menu-item" onclick="location.href='vanhoa.php'"><i class="fas fa-book-open"></i><span>Văn hóa Khmer</span></div>
                <div class="menu-item" onclick="location.href='chua.php'"><i class="fas fa-place-of-worship"></i><span>Chùa Khmer</span></div>
                <div class="menu-item" onclick="location.href='lehoi.php'"><i class="fas fa-calendar-check"></i><span>Lễ hội</span></div>
                <div class="menu-item" onclick="location.href='hoctiengkhmer.php'"><i class="fas fa-graduation-cap"></i><span>Học tiếng Khmer</span></div>
                <div class="menu-item" onclick="location.href='truyendangian.php'"><i class="fas fa-book-reader"></i><span>Truyện dân gian</span></div>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Quản lý</div>
                <div class="menu-item" onclick="location.href='nguoidung.php'"><i class="fas fa-users"></i><span>Người dùng</span></div>
                <div class="menu-item" onclick="location.href='thongbao.php'"><i class="fas fa-bell"></i><span>Thông báo</span></div>
                <div class="menu-item active" onclick="location.href='tinnhan.php'"><i class="fas fa-comments"></i><span>Tin nhắn</span></div>
                <div class="menu-item" onclick="location.href='hoatdong.php'"><i class="fas fa-history"></i><span>Hoạt động</span></div>
                <div class="menu-item" onclick="location.href='caidat.php'"><i class="fas fa-cog"></i><span>Cài đặt</span></div>
            </div>
            <div class="menu-section">
                <div class="menu-item" onclick="logout()" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i><span>Đăng xuất</span></div>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2><i class="fas fa-comments" style="color:var(--primary); margin-right:12px;"></i>Tin nhắn</h2>
            </div>
            <div class="topbar-right">
                <div class="admin-profile-enhanced">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            <?php 
                            $name = $_SESSION['admin_name'] ?? 'Admin';
                            $words = explode(' ', $name);
                            echo count($words) >= 2 ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words)-1], 0, 1)) : mb_strtoupper(mb_substr($name, 0, 2));
                            ?>
                        </div>
                        <div class="online-status"></div>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                        <?php 
                        $role = $_SESSION['admin_role'] ?? 'bien_tap_vien';
                        $role_display = [
                            'sieu_quan_tri' => ['text' => 'Siêu Quản Trị', 'class' => 'role-super-admin'],
                            'quan_tri' => ['text' => 'Quản Trị Viên', 'class' => 'role-admin'],
                            'bien_tap_vien' => ['text' => 'Biên Tập Viên', 'class' => 'role-editor']
                        ];
                        $role_info = $role_display[$role] ?? $role_display['bien_tap_vien'];
                        ?>
                        <span class="profile-role <?php echo $role_info['class']; ?>"><?php echo $role_info['text']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon-wrapper"><i class="fas fa-comments"></i></div>
                        <div>
                            <h1>Trung tâm Tin nhắn</h1>
                            <p class="page-subtitle">Quản lý và giao tiếp với người dùng</p>
                        </div>
                    </div>
                    <button class="btn-new-message" onclick="openNewMessageModal()">
                        <i class="fas fa-paper-plane"></i>
                        Gửi tin nhắn mới
                    </button>
                </div>
            </div>


            
            <div class="msg-stats">
                <div class="msg-stat-card">
                    <div class="msg-stat-icon" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="msg-stat-info">
                        <h3>Tổng tin nhắn</h3>
                        <p><?php echo count($messages); ?></p>
                    </div>
                </div>
                
                <div class="msg-stat-card">
                    <div class="msg-stat-icon" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="msg-stat-info">
                        <h3>Chưa đọc</h3>
                        <p><?php echo $unread_count; ?></p>
                    </div>
                </div>
                
                <div class="msg-stat-card">
                    <div class="msg-stat-icon" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="msg-stat-info">
                        <h3>Đã gửi</h3>
                        <p><?php echo $sent_count; ?></p>
                    </div>
                </div>
            </div>

            <div class="messages-container">
                <div class="messages-header">
                    <h2><i class="fas fa-list"></i>Danh sách tin nhắn</h2>
                    <?php if($unread_count > 0): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="btn-mark-all">
                            <i class="fas fa-check-double"></i>
                            Đánh dấu tất cả đã đọc
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="message-list">
                    <!-- DEBUG: Total messages = <?php echo count($messages); ?> -->
                    <?php if(empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Chưa có tin nhắn</h3>
                        <p>Bạn chưa có tin nhắn nào. Hãy gửi tin nhắn đầu tiên!</p>
                    </div>
                    <?php else: ?>
                        <?php 
                        // Debug: Show message IDs
                        $msg_ids = array_column($messages, 'ma_tin_nhan');
                        echo "<!-- Message IDs: " . implode(', ', $msg_ids) . " -->";
                        ?>
                        <?php foreach($messages as $msg): ?>
                        <!-- Message ID: <?php echo $msg['ma_tin_nhan']; ?> -->
                        <div class="message-item <?php echo $msg['is_received'] && $msg['trang_thai'] === 'chua_doc' ? 'unread' : ''; ?> <?php echo !$msg['is_received'] ? 'sent' : ''; ?>">
                            <div class="msg-avatar" style="background:<?php echo $msg['is_received'] ? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)'; ?>;">
                                <?php 
                                $displayName = $msg['is_received'] ? ($msg['ten_nguoi_gui'] ?? 'U') : ($msg['ten_nguoi_nhan'] ?? 'U');
                                echo mb_strtoupper(mb_substr($displayName, 0, 1));
                                ?>
                            </div>
                            
                            <div class="msg-content">
                                <div class="msg-header">
                                    <div>
                                        <span class="msg-sender"><?php echo htmlspecialchars($msg['is_received'] ? ($msg['ten_nguoi_gui'] ?? 'Người dùng') : ($msg['ten_nguoi_nhan'] ?? 'Người dùng')); ?></span>
                                        <span class="msg-badge <?php echo $msg['is_received'] ? 'received' : 'sent'; ?>">
                                            <?php echo $msg['is_received'] ? 'Nhận' : 'Gửi'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="msg-text"><?php echo nl2br(htmlspecialchars($msg['noi_dung'] ?? '')); ?></div>
                                <div class="msg-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo $msg['time_ago']; ?>
                                </div>
                            </div>
                            
                            <div class="msg-actions">
                                <?php if($msg['is_received'] && $msg['trang_thai'] === 'chua_doc'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="ma_tin_nhan" value="<?php echo $msg['ma_tin_nhan']; ?>">
                                    <button type="submit" class="btn-msg-action btn-mark-read" title="Đánh dấu đã đọc">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa tin nhắn này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="ma_tin_nhan" value="<?php echo $msg['ma_tin_nhan']; ?>">
                                    <button type="submit" class="btn-msg-action btn-delete" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- NEW MESSAGE MODAL -->
<div class="modal" id="newMessageModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-paper-plane"></i> Gửi tin nhắn mới</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="send_message">
            <div class="form-group">
                <label class="form-label">Người nhận <span style="color:var(--danger);">*</span></label>
                <select name="ma_nguoi_nhan" class="form-select" required>
                    <option value="">-- Chọn người nhận --</option>
                    <?php foreach($users as $user): ?>
                    <option value="<?php echo $user['ma_nguoi_dung']; ?>">
                        <?php echo htmlspecialchars($user['ho_ten'] ?? $user['ten_dang_nhap']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nội dung tin nhắn <span style="color:var(--danger);">*</span></label>
                <textarea name="noi_dung" class="form-textarea" required placeholder="Nhập nội dung tin nhắn..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    <span>Gửi tin nhắn</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST NOTIFICATION -->
<?php if($message): ?>
<div class="toast <?php echo $messageType; ?>" id="toast" style="display:flex;">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <span style="font-weight:600;"><?php echo htmlspecialchars($message); ?></span>
</div>
<?php endif; ?>

<script>
// Toast notification
const toast = document.getElementById('toast');
if(toast) {
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}

// Modal functions
function openNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('newMessageModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('newMessageModal').addEventListener('click', function(e) {
    if(e.target === this) closeModal();
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') closeModal();
});

// Logout function
function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

// Prevent double submission
document.querySelector('#newMessageModal form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    
    // Disable button to prevent double click
    if(submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Đang gửi...</span>';
});

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

</body>
</html>
