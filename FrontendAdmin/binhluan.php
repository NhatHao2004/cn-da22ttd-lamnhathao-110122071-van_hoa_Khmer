<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();
refreshAdminInfo();

$db = Database::getInstance();

// Xử lý các hành động với bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        $ma_binh_luan = $_POST['ma_binh_luan'] ?? 0;
        
        switch($action) {
            case 'delete':
                $sql = "DELETE FROM binh_luan WHERE ma_binh_luan = ?";
                $db->execute($sql, [$ma_binh_luan]);
                $_SESSION['flash_message'] = 'Đã xóa bình luận!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'approve':
                $sql = "UPDATE binh_luan SET trang_thai = 'duyet' WHERE ma_binh_luan = ?";
                $db->execute($sql, [$ma_binh_luan]);
                $_SESSION['flash_message'] = 'Đã duyệt bình luận!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'pending':
                $sql = "UPDATE binh_luan SET trang_thai = 'cho_duyet' WHERE ma_binh_luan = ?";
                $db->execute($sql, [$ma_binh_luan]);
                $_SESSION['flash_message'] = 'Đã chuyển về chờ duyệt!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'spam':
                $sql = "UPDATE binh_luan SET trang_thai = 'spam' WHERE ma_binh_luan = ?";
                $db->execute($sql, [$ma_binh_luan]);
                $_SESSION['flash_message'] = 'Đã đánh dấu spam!';
                $_SESSION['flash_type'] = 'warning';
                break;
        }
        
        header('Location: binhluan.php' . (isset($_POST['search']) && $_POST['search'] ? '?search=' . urlencode($_POST['search']) : ''));
        exit;
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy tìm kiếm
$search = $_GET['search'] ?? '';

// Build query
$where_sql = '';
$params = [];

if ($search) {
    $where_sql = "WHERE (bl.noi_dung LIKE ? OR nd.ho_ten LIKE ? OR nd.ten_dang_nhap LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

$sql = "SELECT bl.*, nd.ho_ten, nd.ten_dang_nhap, nd.email, nd.anh_dai_dien 
        FROM binh_luan bl 
        LEFT JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung 
        $where_sql 
        ORDER BY bl.ngay_tao DESC 
        LIMIT 100";

$comments = $db->query($sql, $params) ?: [];

// Thống kê
$total_comments = $db->count('binh_luan');

// Format thời gian - KHÔNG dùng reference để tránh lỗi
$processedComments = [];
foreach($comments as $comment) {
    $time = strtotime($comment['ngay_tao']);
    $diff = time() - $time;
    
    if($diff < 60) {
        $comment['time_ago'] = 'Vừa xong';
    } elseif($diff < 3600) {
        $comment['time_ago'] = floor($diff / 60) . ' phút trước';
    } elseif($diff < 86400) {
        $comment['time_ago'] = floor($diff / 3600) . ' giờ trước';
    } elseif($diff < 604800) {
        $comment['time_ago'] = floor($diff / 86400) . ' ngày trước';
    } else {
        $comment['time_ago'] = date('d/m/Y H:i', $time);
    }
    $processedComments[] = $comment;
}
$comments = $processedComments;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Bình luận - Admin</title>
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
.stats-grid {display:flex; gap:20px; margin-bottom:32px;}
.stat-card {
    flex:1; padding:24px; background:var(--white); border-radius:20px;
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
.filter-bar {
    background:var(--white); border-radius:20px; padding:24px;
    box-shadow:var(--shadow); margin-bottom:24px; display:flex;
    justify-content:space-between; align-items:center; gap:20px;
}
.filter-tabs {display:flex; gap:12px;}
.filter-tab {
    padding:12px 24px; border:2px solid var(--gray-light); border-radius:12px;
    font-weight:700; cursor:pointer; transition:all 0.3s ease;
    display:flex; align-items:center; gap:8px;
}
.filter-tab:hover {border-color:var(--primary); background:rgba(99,102,241,0.05);}
.filter-tab.active {background:var(--gradient-primary); color:var(--white); border-color:transparent;}
.search-box {
    display:flex; gap:12px; align-items:center;
}
.search-input {
    padding:12px 18px; border:2px solid var(--gray-light); border-radius:12px;
    font-size:0.95rem; width:300px; transition:all 0.3s ease;
}
.search-input:focus {
    outline:none; border-color:var(--primary); box-shadow:0 0 0 4px rgba(99,102,241,0.1);
}
.btn-search {
    padding:12px 24px; background:var(--gradient-primary); color:var(--white);
    border:none; border-radius:12px; font-weight:700; cursor:pointer;
    transition:all 0.3s ease;
}
.btn-search:hover {transform:translateY(-2px); box-shadow:0 8px 20px rgba(99,102,241,0.3);}
.comments-container {background:var(--white); border-radius:20px; padding:32px; box-shadow:var(--shadow);}
.comments-header {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:28px; padding-bottom:20px; border-bottom:2px solid var(--gray-light);
}
.comments-header h2 {font-size:1.5rem; font-weight:800; display:flex; align-items:center; gap:12px;}
.table-wrapper {overflow-x:auto;}
.data-table {width:100%; border-collapse:collapse;}
.data-table thead {background:var(--gradient-primary); color:var(--white);}
.data-table th {
    padding:16px; text-align:left; font-weight:700; font-size:0.9rem;
    text-transform:uppercase; letter-spacing:0.5px;
}
.data-table tbody tr {border-bottom:1px solid var(--gray-light); transition:all 0.3s ease;}
.data-table tbody tr:hover {background:var(--gray-light);}
.data-table td {padding:16px; font-size:0.95rem;}
.user-avatar {
    width:48px; height:48px; border-radius:12px; object-fit:cover;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
.user-avatar-placeholder {
    width:48px; height:48px; border-radius:12px; display:flex;
    align-items:center; justify-content:center; font-size:1.3rem;
    color:var(--white); font-weight:800; background:var(--gradient-primary);
}
.action-buttons {display:flex; gap:8px;}
.btn-action {
    width:36px; height:36px; border:none; border-radius:10px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all 0.3s ease;
}
.btn-action:hover {transform:scale(1.1);}
.btn-delete {background:rgba(239,68,68,0.1); color:var(--danger);}
.btn-delete:hover {background:var(--danger); color:var(--white);}
.empty-state {text-align:center; padding:60px 20px;}
.empty-state i {font-size:5rem; color:var(--gray-light); margin-bottom:20px;}
.empty-state h3 {font-size:1.5rem; font-weight:700; color:var(--dark); margin-bottom:12px;}
.empty-state p {font-size:1rem; color:var(--gray);}
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
    .stats-grid {flex-direction:column;}
    .filter-bar {flex-direction:column; align-items:stretch;}
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
                <div class="menu-item active" onclick="location.href='binhluan.php'"><i class="fas fa-comments"></i><span>Bình luận</span></div>
            </div>
            <div class="menu-section">
                <div class="menu-item" onclick="logout()" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i><span>Đăng xuất</span></div>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-area">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon-wrapper"><i class="fas fa-comments"></i></div>
                        <div>
                            <h1>Quản lý Bình luận</h1>
                            <p class="page-subtitle">Xem và quản lý bình luận người dùng</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tổng bình luận</h3>
                        <p><?php echo number_format($total_comments); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đã duyệt</h3>
                        <p><?php 
                            $approved = $db->querySingle("SELECT COUNT(*) as count FROM binh_luan WHERE trang_thai = 'duyet'")['count'] ?? 0;
                            echo number_format($approved);
                        ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Chờ duyệt</h3>
                        <p><?php 
                            $pending = $db->querySingle("SELECT COUNT(*) as count FROM binh_luan WHERE trang_thai = 'cho_duyet'")['count'] ?? 0;
                            echo number_format($pending);
                        ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Spam</h3>
                        <p><?php 
                            $spam = $db->querySingle("SELECT COUNT(*) as count FROM binh_luan WHERE trang_thai = 'spam'")['count'] ?? 0;
                            echo number_format($spam);
                        ?></p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" class="search-box" style="flex:1;">
                    <input type="text" name="search" class="search-input" placeholder="🔍 Tìm kiếm bình luận, người dùng..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if($search): ?>
                    <a href="binhluan.php" class="btn-search" style="background:var(--gray-light); color:var(--dark); text-decoration:none;">
                        <i class="fas fa-times"></i> Xóa
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Comments Table -->
            <div class="comments-container">
                <div class="comments-header">
                    <h2><i class="fas fa-list"></i> Danh sách bình luận</h2>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Avatar</th>
                                <th>Người dùng</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($comments)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-inbox" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có bình luận nào</strong>
                                    <p style="margin-top:8px;"><?php echo $search ? 'Không tìm thấy kết quả phù hợp' : 'Chưa có bình luận trong hệ thống'; ?></p>
                                    <?php if($search): ?>
                                    <p style="margin-top:12px;"><a href="binhluan.php" style="color:var(--primary); text-decoration:none; font-weight:600;">← Xem tất cả bình luận</a></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($comments as $index => $comment): ?>
                                <tr>
                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                    <td>
                                        <?php 
                                        $displayName = $comment['ho_ten'] ?: $comment['ten_dang_nhap'] ?: 'U';
                                        $avatarPath = $comment['anh_dai_dien'] ?? '';
                                        if(!empty($avatarPath) && file_exists('../uploads/avatar/' . $avatarPath)): 
                                        ?>
                                            <img src="../uploads/avatar/<?php echo htmlspecialchars($avatarPath); ?>" class="user-avatar" alt="Avatar">
                                        <?php else: ?>
                                            <div class="user-avatar-placeholder">
                                                <?php echo mb_strtoupper(mb_substr($displayName, 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($displayName); ?></strong>
                                        <?php if(!empty($comment['email'])): ?>
                                        <br><small style="color:var(--gray);"><?php echo htmlspecialchars($comment['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="max-width:400px; overflow:hidden; text-overflow:ellipsis;">
                                            <?php echo nl2br(htmlspecialchars(mb_substr($comment['noi_dung'] ?? '', 0, 150))); ?>
                                            <?php if(mb_strlen($comment['noi_dung'] ?? '') > 150): ?>...<?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $comment['trang_thai'] ?? 'cho_duyet';
                                        $statusConfig = [
                                            'duyet' => ['text' => 'Đã duyệt', 'color' => 'var(--success)', 'bg' => 'rgba(16,185,129,0.1)', 'icon' => 'check-circle'],
                                            'cho_duyet' => ['text' => 'Chờ duyệt', 'color' => 'var(--warning)', 'bg' => 'rgba(245,158,11,0.1)', 'icon' => 'clock'],
                                            'spam' => ['text' => 'Spam', 'color' => 'var(--danger)', 'bg' => 'rgba(239,68,68,0.1)', 'icon' => 'ban']
                                        ];
                                        $config = $statusConfig[$status] ?? $statusConfig['cho_duyet'];
                                        ?>
                                        <span style="padding:6px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; background:<?php echo $config['bg']; ?>; color:<?php echo $config['color']; ?>; display:inline-flex; align-items:center; gap:6px;">
                                            <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                                            <?php echo $config['text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-clock" style="color:var(--gray);"></i>
                                        <?php echo $comment['time_ago']; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if($status !== 'duyet'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="ma_binh_luan" value="<?php echo $comment['ma_binh_luan']; ?>">
                                                <?php if($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                                                <button type="submit" class="btn-action" style="background:rgba(16,185,129,0.1); color:var(--success);" title="Duyệt bình luận">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <?php if($status !== 'spam'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="spam">
                                                <input type="hidden" name="ma_binh_luan" value="<?php echo $comment['ma_binh_luan']; ?>">
                                                <?php if($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                                                <button type="submit" class="btn-action" style="background:rgba(239,68,68,0.1); color:var(--danger);" title="Đánh dấu spam">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa bình luận này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ma_binh_luan" value="<?php echo $comment['ma_binh_luan']; ?>">
                                                <?php if($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                                                <button type="submit" class="btn-action btn-delete" title="Xóa bình luận">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

<?php if($message): ?>
<div class="toast <?php echo $messageType; ?>" id="toast" style="display:flex;">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <span style="font-weight:600;"><?php echo htmlspecialchars($message); ?></span>
</div>
<?php endif; ?>

<script>
const toast = document.getElementById('toast');
if(toast) {
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}

function logout() {
    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

// Debug: Log khi form được submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[method="POST"]');
    console.log('Tìm thấy ' + forms.length + ' forms');
    
    forms.forEach((form, index) => {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('input[name="action"]')?.value;
            const id = form.querySelector('input[name="ma_binh_luan"]')?.value;
            console.log('Form ' + index + ' submitted: action=' + action + ', id=' + id);
        });
    });
});
</script>

</body>
</html>
