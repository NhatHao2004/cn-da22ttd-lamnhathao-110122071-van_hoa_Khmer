<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách hoạt động từ bảng nhat_ky_hoat_dong
$sql = "SELECT * FROM nhat_ky_hoat_dong ORDER BY ngay_tao DESC LIMIT 100";
$logs = $db->query($sql) ?: [];

// Chuyển đổi dữ liệu từ bảng nhat_ky_hoat_dong
$activities = [];
foreach($logs as $log) {
    // Xác định icon và màu sắc theo loại hoạt động
    $icon_map = [
        'create' => ['icon' => 'fa-plus-circle', 'color' => 'success'],
        'update' => ['icon' => 'fa-edit', 'color' => 'info'],
        'delete' => ['icon' => 'fa-trash', 'color' => 'danger'],
        'login' => ['icon' => 'fa-sign-in-alt', 'color' => 'success'],
        'logout' => ['icon' => 'fa-sign-out-alt', 'color' => 'warning'],
        'view' => ['icon' => 'fa-eye', 'color' => 'info'],
        'chua_le_hoi' => ['icon' => 'fa-place-of-worship', 'color' => 'info'],
        'bai_hoc' => ['icon' => 'fa-graduation-cap', 'color' => 'success'],
        'bai_tap' => ['icon' => 'fa-tasks', 'color' => 'warning'],
        'truyen' => ['icon' => 'fa-book-reader', 'color' => 'info'],
    ];
    
    $action_info = $icon_map[$log['hanh_dong']] ?? ['icon' => 'fa-circle', 'color' => 'info'];
    
    // Lấy tên người dùng
    $user_name = 'Hệ thống';
    if($log['loai_nguoi_dung'] === 'user' && $log['ma_nguoi_dung']) {
        $user = $db->querySingle("SELECT ho_ten, ten_dang_nhap FROM nguoi_dung WHERE ma_nguoi_dung = ?", [$log['ma_nguoi_dung']]);
        $user_name = $user ? ($user['ho_ten'] ?: $user['ten_dang_nhap']) : 'Người dùng #' . $log['ma_nguoi_dung'];
    } elseif($log['loai_nguoi_dung'] === 'admin' && $log['ma_nguoi_dung']) {
        $admin = $db->querySingle("SELECT ho_ten FROM quan_tri_vien WHERE ma_qtv = ?", [$log['ma_nguoi_dung']]);
        $user_name = $admin ? $admin['ho_ten'] : 'Admin #' . $log['ma_nguoi_dung'];
    }
    
    // Tạo tiêu đề và mô tả
    $title_map = [
        'create' => 'Tạo mới',
        'update' => 'Cập nhật',
        'delete' => 'Xóa',
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'view' => 'Xem',
    ];
    
    $title = $title_map[$log['hanh_dong']] ?? ucfirst($log['hanh_dong']);
    $title .= ' ' . ($log['loai_doi_tuong'] ?? 'dữ liệu');
    
    $activities[] = [
        'icon' => $action_info['icon'],
        'color' => $action_info['color'],
        'title' => $title,
        'description' => $log['mo_ta'] ?? 'Không có mô tả',
        'time' => $log['ngay_tao'],
        'user' => $user_name,
        'ip' => $log['ip_address'] ?? 'N/A'
    ];
}

// Format thời gian
foreach($activities as &$activity) {
    $time = strtotime($activity['time']);
    $diff = time() - $time;
    
    if($diff < 60) {
        $activity['time_ago'] = 'Vừa xong';
    } elseif($diff < 3600) {
        $activity['time_ago'] = floor($diff / 60) . ' phút trước';
    } elseif($diff < 86400) {
        $activity['time_ago'] = floor($diff / 3600) . ' giờ trước';
    } elseif($diff < 604800) {
        $activity['time_ago'] = floor($diff / 86400) . ' ngày trước';
    } else {
        $activity['time_ago'] = date('d/m/Y H:i', $time);
    }
}

// Thống kê hoạt động
$today_start = date('Y-m-d 00:00:00');

// Đếm người dùng mới hôm nay
$today_users_result = $db->querySingle("SELECT COUNT(*) as count FROM nguoi_dung WHERE ngay_tao >= ?", [$today_start]);
$today_users = $today_users_result['count'] ?? 0;

// Đếm bài viết mới hôm nay (từ các bảng văn hóa, chùa, lễ hội, truyện)
$today_articles = 0;
$tables = ['van_hoa_khmer', 'chua_khmer', 'le_hoi', 'truyen_dan_gian'];
foreach($tables as $table) {
    $result = $db->querySingle("SELECT COUNT(*) as count FROM $table WHERE ngay_tao >= ?", [$today_start]);
    $today_articles += $result['count'] ?? 0;
}

// Đếm hoạt động hôm nay
$today_activities_result = $db->querySingle("SELECT COUNT(*) as count FROM nhat_ky_hoat_dong WHERE ngay_tao >= ?", [$today_start]);
$today_activities = $today_activities_result['count'] ?? 0;

$stats = [
    'today_users' => $today_users,
    'today_articles' => $today_articles,
    'total_activities' => count($activities)
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hoạt động Hệ thống - Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="admin-common-styles.css">
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif;}
:root {
    --primary: #6366f1;
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
.sidebar-logo-text h2 {font-size:1.3rem; font-weight:800; color:var(--white);}
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

.stats-grid {display:grid; grid-template-columns:repeat(2, 1fr); gap:20px; margin-bottom:32px; max-width:900px; margin-left:auto; margin-right:auto;}
.stat-card {
    padding:24px; background:var(--white); border-radius:20px;
    box-shadow:var(--shadow); display:flex; align-items:center; gap:20px;
    transition:all 0.3s ease; cursor:pointer;
}
.stat-card:hover {transform:translateY(-4px); box-shadow:var(--shadow-lg);}
.stat-icon {
    width:64px; height:64px; border-radius:16px; display:flex;
    align-items:center; justify-content:center; font-size:1.8rem; color:var(--white);
}
.stat-info h3 {font-size:0.85rem; color:var(--gray); font-weight:600; text-transform:uppercase; margin-bottom:8px;}
.stat-info p {font-size:2.2rem; font-weight:900; color:var(--dark);}
.activity-container {background:var(--white); border-radius:20px; padding:32px; box-shadow:var(--shadow);}
.activity-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid var(--gray-light);
}
.activity-header h2 {font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:12px;}
.activity-timeline {position:relative; padding-left:40px;}
.activity-timeline::before {
    content:''; position:absolute; left:16px; top:0; bottom:0;
    width:2px; background:var(--gray-light);
}
.activity-item {
    position:relative; padding:20px; margin-bottom:16px;
    border-radius:16px; background:var(--gray-light);
    transition:all 0.3s ease;
}
.activity-item:hover {
    background:linear-gradient(135deg, rgba(99,102,241,0.05) 0%, rgba(118,75,162,0.05) 100%);
    transform:translateX(4px);
}
.activity-icon-wrapper {
    position:absolute; left:-24px; top:20px;
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    color:var(--white); box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
.activity-icon-wrapper.success {background:linear-gradient(135deg, #10b981 0%, #059669 100%);}
.activity-icon-wrapper.info {background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);}
.activity-icon-wrapper.warning {background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);}
.activity-icon-wrapper.danger {background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);}
.activity-content {display:flex; justify-content:space-between; align-items:flex-start;}
.activity-info {flex:1;}
.activity-title {font-size:1rem; font-weight:700; color:var(--dark); margin-bottom:6px;}
.activity-description {font-size:0.9rem; color:var(--gray); margin-bottom:8px;}
.activity-meta {display:flex; align-items:center; gap:16px; font-size:0.85rem; color:var(--gray);}
.activity-meta i {font-size:0.8rem;}
.activity-time {
    padding:6px 12px; background:var(--white); border-radius:8px;
    font-size:0.85rem; font-weight:600; color:var(--gray);
}
.empty-state {text-align:center; padding:60px 20px;}
.empty-state i {font-size:5rem; color:var(--gray-light); margin-bottom:20px;}
.empty-state h3 {font-size:1.5rem; font-weight:700; color:var(--dark); margin-bottom:12px;}
.empty-state p {font-size:1rem; color:var(--gray);}
@media(max-width:768px){
    .sidebar {left:-280px;}
    .main-content {margin-left:0;}
    .stats-grid {grid-template-columns:1fr;}
    .page-header {padding:32px 24px;}
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
                <div class="menu-item" onclick="location.href='tinnhan.php'"><i class="fas fa-comments"></i><span>Tin nhắn</span></div>
                <div class="menu-item active" onclick="location.href='hoatdong.php'"><i class="fas fa-history"></i><span>Hoạt động</span></div>
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
                <h2><i class="fas fa-history" style="color:var(--primary); margin-right:12px;"></i>Hoạt động Hệ thống</h2>
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
                        <div class="page-icon-wrapper"><i class="fas fa-history"></i></div>
                        <div>
                            <h1>Nhật ký Hoạt động</h1>
                            <p class="page-subtitle">Theo dõi tất cả hoạt động và thay đổi trong hệ thống</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Người dùng mới hôm nay</h3>
                        <p><?php echo $stats['today_users']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tổng hoạt động</h3>
                        <p><?php echo $stats['total_activities']; ?></p>
                    </div>
                </div>
            </div>

            <div class="activity-container">
                <div class="activity-header">
                    <h2><i class="fas fa-stream"></i>Dòng thời gian hoạt động</h2>
                </div>

                <div class="activity-timeline">
                    <?php if(empty($activities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>Chưa có hoạt động</h3>
                        <p>Chưa có hoạt động nào được ghi nhận trong hệ thống</p>
                    </div>
                    <?php else: ?>
                        <?php foreach($activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon-wrapper <?php echo $activity['color']; ?>">
                                <i class="fas <?php echo $activity['icon']; ?>"></i>
                            </div>
                            
                            <div class="activity-content">
                                <div class="activity-info">
                                    <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                    <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                                    <div class="activity-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($activity['user']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo $activity['time_ago']; ?></span>
                                    </div>
                                </div>
                                <div class="activity-time">
                                    <?php echo $activity['time_ago']; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

// Auto refresh every 60 seconds
setInterval(() => {
    location.reload();
}, 60000);
</script>

</body>
</html>
