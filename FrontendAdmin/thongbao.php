<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'mark_read':
                $ma_thong_bao = $_POST['ma_thong_bao'] ?? 0;
                $sql = "UPDATE thong_bao SET trang_thai = 'da_doc' WHERE ma_thong_bao = ?";
                $db->execute($sql, [$ma_thong_bao]);
                $_SESSION['flash_message'] = 'Đã đánh dấu đã đọc!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'mark_all_read':
                $sql = "UPDATE thong_bao SET trang_thai = 'da_doc' WHERE ma_qtv = ? AND trang_thai = 'chua_doc'";
                $db->execute($sql, [$_SESSION['admin_id']]);
                $_SESSION['flash_message'] = 'Đã đánh dấu tất cả đã đọc!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'delete':
                $ma_thong_bao = $_POST['ma_thong_bao'] ?? 0;
                $sql = "DELETE FROM thong_bao WHERE ma_thong_bao = ?";
                $db->execute($sql, [$ma_thong_bao]);
                $_SESSION['flash_message'] = 'Đã xóa thông báo!';
                $_SESSION['flash_type'] = 'success';
                break;
        }
        header('Location: thongbao.php');
        exit;
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách thông báo
$sql = "SELECT * FROM thong_bao WHERE (ma_qtv = ? OR ma_qtv IS NULL) ORDER BY ngay_tao DESC LIMIT 50";
$notifications = $db->query($sql, [$_SESSION['admin_id']]) ?: [];

// Đếm thông báo chưa đọc
$unread_count = count(array_filter($notifications, function($n) {
    return $n['trang_thai'] === 'chua_doc';
}));

// Format thời gian
foreach($notifications as &$notif) {
    $time = strtotime($notif['ngay_tao']);
    $diff = time() - $time;
    
    if($diff < 60) {
        $notif['time_ago'] = 'Vừa xong';
    } elseif($diff < 3600) {
        $notif['time_ago'] = floor($diff / 60) . ' phút trước';
    } elseif($diff < 86400) {
        $notif['time_ago'] = floor($diff / 3600) . ' giờ trước';
    } elseif($diff < 604800) {
        $notif['time_ago'] = floor($diff / 86400) . ' ngày trước';
    } else {
        $notif['time_ago'] = date('d/m/Y H:i', $time);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thông báo - Admin</title>
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

/* Layout */
.admin-wrapper {display:flex; min-height:100vh;}

/* Sidebar */
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
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    border:3px solid var(--white); border-radius:50%;
}
.profile-info {display:flex; flex-direction:column; gap:4px;}
.profile-name {font-size:0.95rem; font-weight:700; color:var(--dark);}
.profile-role {
    font-size:0.7rem; font-weight:700; display:inline-flex; align-items:center;
    gap:5px; padding:4px 10px; border-radius:8px; text-transform:uppercase; letter-spacing:0.6px;
}
.profile-role.role-super-admin {background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color:#8b4513;}
.profile-role.role-admin {background:var(--gradient-primary); color:var(--white);}
.profile-role.role-editor {background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:var(--white);}

/* Content Area */
.content-area {padding:32px; max-width:1400px; margin:0 auto;}

/* Page Header */
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
    box-shadow:0 8px 24px rgba(0, 0, 0, 0.15); animation:float 3s ease-in-out infinite;
}
@keyframes float {0%, 100% {transform:translateY(0);} 50% {transform:translateY(-10px);}}
.page-title-wrapper h1 {font-size:2.5rem; font-weight:900; line-height:1.2;}
.page-subtitle {font-size:1.1rem; opacity:0.95; font-weight:500; margin-top:8px;}

/* Notification Stats */
.notif-stats {display:flex; gap:20px; margin-bottom:32px;}
.notif-stat-card {
    flex:1; padding:24px; background:var(--white); border-radius:20px;
    box-shadow:var(--shadow); display:flex; align-items:center; gap:20px;
    transition:all 0.3s ease; cursor:pointer;
}
.notif-stat-card:hover {transform:translateY(-4px); box-shadow:var(--shadow-lg);}
.notif-stat-icon {
    width:64px; height:64px; border-radius:16px; display:flex;
    align-items:center; justify-content:center; font-size:1.8rem; color:var(--white);
}
.notif-stat-info h3 {font-size:0.85rem; color:var(--gray); font-weight:600; text-transform:uppercase; margin-bottom:8px;}
.notif-stat-info p {font-size:2.2rem; font-weight:900; color:var(--dark);}

/* Notifications Container */
.notifications-container {background:var(--white); border-radius:20px; padding:32px; box-shadow:var(--shadow);}
.notifications-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid var(--gray-light);
}
.notifications-header h2 {font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:12px;}
.btn-mark-all {
    padding:12px 24px; background:var(--gradient-primary); color:var(--white);
    border:none; border-radius:12px; font-weight:700; cursor:pointer;
    transition:all 0.3s ease; display:flex; align-items:center; gap:8px;
}
.btn-mark-all:hover {transform:translateY(-2px); box-shadow:0 8px 20px rgba(99,102,241,0.3);}

/* Notification List */
.notification-list {display:flex; flex-direction:column; gap:16px;}
.notification-item {
    padding:20px; border-radius:16px; border:2px solid var(--gray-light);
    transition:all 0.3s ease; display:flex; gap:16px; align-items:flex-start;
    position:relative; overflow:hidden;
}
.notification-item::before {
    content:''; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:var(--primary); opacity:0; transition:opacity 0.3s ease;
}
.notification-item:hover {
    border-color:var(--primary); transform:translateX(4px);
    box-shadow:0 4px 12px rgba(99,102,241,0.15);
}
.notification-item:hover::before {opacity:1;}
.notification-item.unread {background:linear-gradient(135deg, rgba(99,102,241,0.05) 0%, rgba(118,75,162,0.05) 100%);}
.notification-item.unread::before {opacity:1;}
.notif-icon {
    width:48px; height:48px; border-radius:12px; display:flex;
    align-items:center; justify-content:center; font-size:1.3rem;
    color:var(--white); flex-shrink:0;
}
.notif-content {flex:1;}
.notif-title {font-size:1.05rem; font-weight:700; color:var(--dark); margin-bottom:6px;}
.notif-message {font-size:0.95rem; color:var(--gray); line-height:1.6; margin-bottom:8px;}
.notif-time {font-size:0.85rem; color:var(--gray); display:flex; align-items:center; gap:6px;}
.notif-time i {font-size:0.8rem;}
.notif-actions {display:flex; gap:8px; flex-shrink:0;}
.btn-notif-action {
    width:36px; height:36px; border:none; border-radius:10px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all 0.3s ease; font-size:0.9rem;
}
.btn-notif-action:hover {transform:scale(1.1);}
.btn-mark-read {background:rgba(16,185,129,0.1); color:var(--success);}
.btn-mark-read:hover {background:var(--success); color:var(--white);}
.btn-delete {background:rgba(239,68,68,0.1); color:var(--danger);}
.btn-delete:hover {background:var(--danger); color:var(--white);}

/* Empty State */
.empty-state {text-align:center; padding:60px 20px;}
.empty-state i {font-size:5rem; color:var(--gray-light); margin-bottom:20px;}
.empty-state h3 {font-size:1.5rem; font-weight:700; color:var(--dark); margin-bottom:12px;}
.empty-state p {font-size:1rem; color:var(--gray);}

/* Toast */
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

/* Responsive */
@media(max-width:768px){
    .sidebar {left:-280px;}
    .main-content {margin-left:0;}
    .notif-stats {flex-direction:column;}
    .page-header {padding:32px 24px;}
    .notifications-container {padding:20px;}
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
                <div class="menu-item active" onclick="location.href='thongbao.php'">
                    <i class="fas fa-bell"></i><span>Thông báo</span>
                </div>
                <div class="menu-item" onclick="location.href='tinnhan.php'">
                    <i class="fas fa-comments"></i><span>Tin nhắn</span>
                </div>
                <div class="menu-item" onclick="location.href='hoatdong.php'">
                    <i class="fas fa-history"></i><span>Hoạt động</span>
                </div>
                <div class="menu-item" onclick="location.href='caidat.php'">
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
                <h2><i class="fas fa-bell" style="color:var(--primary); margin-right:12px;"></i>Thông báo</h2>
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
                            'sieu_quan_tri' => ['text' => 'Siêu Quản Trị', 'class' => 'role-super-admin'],
                            'quan_tri' => ['text' => 'Quản Trị Viên', 'class' => 'role-admin'],
                            'bien_tap_vien' => ['text' => 'Biên Tập Viên', 'class' => 'role-editor']
                        ];
                        $role_info = $role_display[$role] ?? $role_display['bien_tap_vien'];
                        ?>
                        <span class="profile-role <?php echo $role_info['class']; ?>">
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
                        <div class="page-icon-wrapper"><i class="fas fa-bell"></i></div>
                        <div>
                            <h1>Trung tâm Thông báo</h1>
                            <p class="page-subtitle">Quản lý và theo dõi tất cả thông báo hệ thống</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NOTIFICATION STATS -->
            <div class="notif-stats">
                <div class="notif-stat-card">
                    <div class="notif-stat-icon" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notif-stat-info">
                        <h3>Tổng thông báo</h3>
                        <p><?php echo count($notifications); ?></p>
                    </div>
                </div>
                
                <div class="notif-stat-card">
                    <div class="notif-stat-icon" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="notif-stat-info">
                        <h3>Chưa đọc</h3>
                        <p><?php echo $unread_count; ?></p>
                    </div>
                </div>
                
                <div class="notif-stat-card">
                    <div class="notif-stat-icon" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="notif-stat-info">
                        <h3>Đã đọc</h3>
                        <p><?php echo count($notifications) - $unread_count; ?></p>
                    </div>
                </div>
            </div>

            <!-- NOTIFICATIONS CONTAINER -->
            <div class="notifications-container">
                <div class="notifications-header">
                    <h2>
                        <i class="fas fa-list"></i>
                        Danh sách thông báo
                    </h2>
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

                <div class="notification-list">
                    <?php if(empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h3>Chưa có thông báo</h3>
                        <p>Bạn chưa có thông báo nào trong hệ thống</p>
                    </div>
                    <?php else: ?>
                        <?php foreach($notifications as $notif): ?>
                        <div class="notification-item <?php echo $notif['trang_thai'] === 'chua_doc' ? 'unread' : ''; ?>">
                            <div class="notif-icon" style="background:<?php 
                                $colors = [
                                    'thanh_cong' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                                    'canh_bao' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                                    'loi' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                                    'thong_tin' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)'
                                ];
                                echo $colors[$notif['loai'] ?? 'thong_tin'] ?? $colors['thong_tin'];
                            ?>;">
                                <i class="fas fa-<?php 
                                    $icons = [
                                        'thanh_cong' => 'check-circle',
                                        'canh_bao' => 'exclamation-triangle',
                                        'loi' => 'times-circle',
                                        'thong_tin' => 'info-circle'
                                    ];
                                    echo $icons[$notif['loai'] ?? 'thong_tin'] ?? 'bell';
                                ?>"></i>
                            </div>
                            
                            <div class="notif-content">
                                <div class="notif-title"><?php echo htmlspecialchars($notif['tieu_de'] ?? 'Thông báo'); ?></div>
                                <div class="notif-message"><?php echo htmlspecialchars($notif['noi_dung'] ?? ''); ?></div>
                                <div class="notif-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo $notif['time_ago']; ?>
                                </div>
                            </div>
                            
                            <div class="notif-actions">
                                <?php if($notif['trang_thai'] === 'chua_doc'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="ma_thong_bao" value="<?php echo $notif['ma_thong_bao']; ?>">
                                    <button type="submit" class="btn-notif-action btn-mark-read" title="Đánh dấu đã đọc">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="ma_thong_bao" value="<?php echo $notif['ma_thong_bao']; ?>">
                                    <button type="submit" class="btn-notif-action btn-delete" title="Xóa">
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

// Logout function
function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

</body>
</html>
