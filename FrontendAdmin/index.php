<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database (để phản ánh thay đổi vai trò)
refreshAdminInfo();

$db = Database::getInstance();

// Lấy thống kê từ database
$stats = [
    'users' => [
        'count' => $db->count('nguoi_dung'), 
        'change' => '+' . $db->count('nguoi_dung', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),  
        'trend' => 'up'
    ],
    'temples' => [
        'count' => $db->count('chua_khmer'), 
        'change' => '+' . $db->count('chua_khmer', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"), 
        'trend' => 'up'
    ],
    'festivals' => [
        'count' => $db->count('le_hoi'), 
        'change' => '+' . $db->count('le_hoi', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"), 
        'trend' => 'up'
    ],
    'lessons' => [
        'count' => $db->count('bai_hoc'), 
        'change' => '+' . $db->count('bai_hoc', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"), 
        'trend' => 'up'
    ],
    'articles' => [
        'count' => $db->count('van_hoa'), 
        'change' => '+' . $db->count('van_hoa', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"), 
        'trend' => 'up'
    ],
    'stories' => [
        'count' => $db->count('truyen_dan_gian'), 
        'change' => '+' . $db->count('truyen_dan_gian', "ngay_tao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"), 
        'trend' => 'up'
    ]
];

$quick_actions = [
    ['icon' => 'fa-book', 'label' => 'Thêm bài viết', 'link' => 'vanhoa.php', 'color' => '#007bff'],
    ['icon' => 'fa-place-of-worship', 'label' => 'Thêm chùa', 'link' => 'chua.php', 'color' => '#6c757d'],
    ['icon' => 'fa-calendar-plus', 'label' => 'Thêm lễ hội', 'link' => 'lehoi.php', 'color' => '#dc3545'],
    ['icon' => 'fa-language', 'label' => 'Thêm bài học', 'link' => 'hoctiengkhmer.php', 'color' => '#ffc107'],
    ['icon' => 'fa-book-reader', 'label' => 'Thêm truyện dân gian', 'link' => 'truyendangian.php', 'color' => '#28a745']
];

// Lấy hoạt động gần đây từ bảng nhật ký
$recent_activities_sql = "
    SELECT nk.*, qtv.ho_ten, nk.hanh_dong, nk.loai_doi_tuong, nk.mo_ta,
           CASE 
               WHEN TIMESTAMPDIFF(MINUTE, nk.ngay_tao, NOW()) < 60 
                   THEN CONCAT(TIMESTAMPDIFF(MINUTE, nk.ngay_tao, NOW()), ' phút trước')
               WHEN TIMESTAMPDIFF(HOUR, nk.ngay_tao, NOW()) < 24 
                   THEN CONCAT(TIMESTAMPDIFF(HOUR, nk.ngay_tao, NOW()), ' giờ trước')
               ELSE CONCAT(TIMESTAMPDIFF(DAY, nk.ngay_tao, NOW()), ' ngày trước')
           END as thoi_gian
    FROM nhat_ky_hoat_dong nk
    LEFT JOIN quan_tri_vien qtv ON nk.ma_nguoi_dung = qtv.ma_qtv AND nk.loai_nguoi_dung = 'quan_tri'
    ORDER BY nk.ngay_tao DESC LIMIT 5
";
$recent_activities_data = $db->query($recent_activities_sql) ?: [];

$recent_activities = [];

if($recent_activities_data) {
    foreach($recent_activities_data as $activity) {
        $icon_map = [
            'van_hoa' => ['icon' => 'fa-book', 'color' => '#007bff'],
            'bai_viet' => ['icon' => 'fa-book', 'color' => '#007bff'],
            'chua' => ['icon' => 'fa-place-of-worship', 'color' => '#6c757d'],
            'le_hoi' => ['icon' => 'fa-calendar', 'color' => '#dc3545'],
            'bai_hoc' => ['icon' => 'fa-graduation-cap', 'color' => '#ffc107'],
        ];
        
        $type = $activity['loai_doi_tuong'] ?? 'other';
        $icon_info = $icon_map[$type] ?? ['icon' => 'fa-circle', 'color' => '#6c757d'];
        
        $recent_activities[] = [
            'user' => $activity['ho_ten'] ?? 'Người dùng',
            'action' => $activity['hanh_dong'],
            'item' => $activity['mo_ta'] ?? '',
            'time' => $activity['thoi_gian'],
            'icon' => $icon_info['icon'],
            'color' => $icon_info['color']
        ];
    }
}

// Lấy thông báo
$notifications_query = "
    SELECT 
        n.*,
        CASE 
            WHEN TIMESTAMPDIFF(SECOND, n.ngay_tao, NOW()) < 60 
                THEN 'Vừa xong'
            WHEN TIMESTAMPDIFF(MINUTE, n.ngay_tao, NOW()) < 60 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.ngay_tao, NOW()), ' phút trước')
            WHEN TIMESTAMPDIFF(HOUR, n.ngay_tao, NOW()) < 24 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, n.ngay_tao, NOW()), ' giờ trước')
            WHEN TIMESTAMPDIFF(DAY, n.ngay_tao, NOW()) < 7 
                THEN CONCAT(TIMESTAMPDIFF(DAY, n.ngay_tao, NOW()), ' ngày trước')
            ELSE DATE_FORMAT(n.ngay_tao, '%d/%m/%Y')
        END as thoi_gian_hien_thi
    FROM thong_bao n
    WHERE n.ma_qtv = ? OR n.ma_qtv IS NULL
    ORDER BY n.ngay_tao DESC 
    LIMIT 10
";
$notifications = $db->query($notifications_query, [$_SESSION['admin_id']]) ?: [];

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

// Lấy nội dung xem nhiều nhất
$top_content = [];

// Bài viết xem nhiều
$top_articles = $db->query("SELECT tieu_de as title, luot_xem as views, 'Bài viết' as type FROM van_hoa WHERE luot_xem > 0 ORDER BY luot_xem DESC LIMIT 2");
if($top_articles) $top_content = array_merge($top_content, $top_articles);

// Chùa xem nhiều
$top_temples = $db->query("SELECT ten_chua as title, luot_xem as views, 'Chùa' as type FROM chua_khmer WHERE luot_xem > 0 ORDER BY luot_xem DESC LIMIT 1");
if($top_temples) $top_content = array_merge($top_content, $top_temples);

// Truyện xem nhiều
$top_stories = $db->query("SELECT tieu_de as title, luot_xem as views, 'Truyện' as type FROM truyen_dan_gian WHERE luot_xem > 0 ORDER BY luot_xem DESC LIMIT 1");
if($top_stories) $top_content = array_merge($top_content, $top_stories);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="description" content="Hệ thống quản trị Văn hóa Khmer Nam Bộ">
<meta name="theme-color" content="#6366f1">
<title>Admin Văn Hóa Khmer</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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
    --gradient-success: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}
body {background:var(--gray-light); color:var(--dark); line-height:1.6;}
a {text-decoration:none; color:inherit;}

/* Layout */
.admin-wrapper {display:flex; min-height:100vh; background:var(--gray-light);}

/* Sidebar */
.sidebar {
    width:280px;
    background:var(--white);
    color:var(--dark);
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
    position:relative;
    overflow:hidden;
}
.sidebar-logo-icon::before {
    content:'';
    position:absolute;
    inset:-2px;
    background:linear-gradient(45deg, var(--primary), var(--primary-light), var(--primary));
    border-radius:12px;
    opacity:0;
    transition:opacity 0.3s ease;
}
.sidebar-logo-icon:hover::before {
    opacity:0.2;
    animation:rotate 3s linear infinite;
}
.sidebar-logo-icon i {
    position:relative;
    z-index:1;
    animation:spin 8s linear infinite;
    transition:all 0.3s ease;
}
.sidebar-logo-icon:hover i {
    animation:spin 2s linear infinite;
    transform:scale(1.1);
}
@keyframes spin {
    from {
        transform:rotate(0deg);
    }
    to {
        transform:rotate(360deg);
    }
}
@keyframes rotate {
    from {
        transform:rotate(0deg);
    }
    to {
        transform:rotate(360deg);
    }
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
    margin-top:2px;
}
.sidebar-menu {
    padding:20px 12px;
}
.menu-section {
    margin-bottom:28px;
}
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
    position:relative;
    white-space:nowrap;
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
.menu-item.active::before {
    content:'';
    position:absolute;
    left:0;
    top:50%;
    transform:translateY(-50%);
    width:4px;
    height:24px;
    background:var(--white);
    border-radius:0 4px 4px 0;
}
.menu-item i {
    font-size:1.15rem;
    width:24px;
    flex-shrink:0;
    text-align:center;
}
.menu-item span {
    font-size:0.95rem;
    font-weight:600;
}
.menu-item .badge {
    margin-left:auto;
    padding:2px 8px;
    background:var(--danger);
    color:var(--white);
    font-size:0.7rem;
    font-weight:700;
    border-radius:20px;
}

/* Main Content */
.main-content {
    margin-left:280px;
    flex:1;
    min-height:100vh;
    transition:margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Topbar */
.topbar {
    background:rgba(255, 255, 255, 0.95);
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
.hamburger {
    display:none;
    width:40px;
    height:40px;
    background:var(--gray-light);
    border:none;
    border-radius:10px;
    cursor:pointer;
    align-items:center;
    justify-content:center;
    font-size:1.2rem;
    color:var(--dark);
    transition:all 0.3s ease;
}
.hamburger:hover {
    background:var(--primary);
    color:var(--white);
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
    font-size:1.05rem;
    transition:color 0.3s ease;
}
.topbar-search input {
    width:100%;
    padding:14px 18px 14px 48px;
    border:2px solid transparent;
    border-radius:14px;
    font-size:0.95rem;
    background:var(--gray-light);
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.topbar-search input::placeholder {
    color:#9ca3af;
    font-weight:500;
}
.topbar-search:focus-within i {
    color:var(--primary);
}
.topbar-search input:focus {
    outline:none;
    border-color:var(--primary);
    background:var(--white);
    box-shadow:0 8px 24px rgba(99,102,241,0.12);
    transform:translateY(-1px);
}
.search-results {
    display:none;
    position:absolute;
    top:calc(100% + 8px);
    left:0;
    right:0;
    background:var(--white);
    border-radius:12px;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    max-height:400px;
    overflow-y:auto;
    z-index:1000;
}
.search-result-item {
    padding:12px 16px;
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
    transition:all 0.2s;
    border-bottom:1px solid var(--gray-light);
}
.search-result-item:last-child {
    border-bottom:none;
}
.search-result-item:hover {
    background:var(--gray-light);
}
.search-result-item i {
    width:36px;
    height:36px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--gradient-primary);
    color:var(--white);
    border-radius:10px;
    font-size:0.95rem;
}
.search-result-title {
    font-weight:600;
    color:var(--dark);
    font-size:0.95rem;
}
.search-result-type {
    font-size:0.8rem;
    color:var(--gray);
    margin-top:2px;
}
.topbar-right {
    display:flex;
    align-items:center;
    gap:8px;
}

/* New Action Icon Style */
.topbar-action-icon {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:4px;
    padding:10px 16px;
    cursor:pointer;
    border-radius:14px;
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position:relative;
    background:transparent;
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
    box-shadow:0 4px 12px rgba(102, 126, 234, 0.25);
    transition:all 0.3s ease;
}
.topbar-action-icon:hover .icon-wrapper {
    transform:scale(1.08) rotate(-5deg);
    box-shadow:0 8px 20px rgba(102, 126, 234, 0.4);
}
.topbar-action-icon .icon-wrapper i {
    font-size:1.1rem;
    color:var(--white);
    transition:transform 0.3s ease;
}
.topbar-action-icon:hover .icon-wrapper i {
    transform:scale(1.15);
}
.topbar-action-icon .icon-label {
    font-size:0.7rem;
    font-weight:600;
    color:var(--gray);
    text-transform:uppercase;
    letter-spacing:0.5px;
    transition:color 0.3s ease;
}
.topbar-action-icon:hover .icon-label {
    color:var(--primary);
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
    box-shadow:0 2px 8px rgba(255, 65, 108, 0.4);
}
.notification-badge.pulse {
    animation:badgePulse 2s ease-in-out infinite;
}

/* Topbar Divider */
.topbar-divider {
    width:1px;
    height:40px;
    background:linear-gradient(to bottom, transparent, var(--gray-light), transparent);
    margin:0 8px;
}

/* Enhanced Admin Profile */
.admin-profile-enhanced {
    display:flex;
    align-items:center;
    gap:10px;
    padding:6px 10px 6px 6px;
    background:var(--white);
    border:2px solid var(--gray-light);
    border-radius:14px;
    cursor:pointer;
    position:relative;
    overflow:hidden;
    transition:all 0.3s ease;
}
.admin-profile-enhanced:hover {
    border-color:var(--primary);
    box-shadow:0 4px 12px rgba(102, 126, 234, 0.15);
}
.admin-profile-enhanced > * {
    position:relative;
    z-index:1;
}
.profile-avatar-wrapper {
    position:relative;
}
.profile-avatar {
    width:42px;
    height:42px;
    border-radius:12px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:1rem;
    box-shadow:0 3px 12px rgba(102, 126, 234, 0.3);
    border:2.5px solid var(--white);
}
.online-status {
    position:absolute;
    bottom:0;
    right:0;
    width:12px;
    height:12px;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    border:2.5px solid var(--white);
    border-radius:50%;
    box-shadow:0 2px 6px rgba(16, 185, 129, 0.4);
    animation:statusPulse 2.5s ease-in-out infinite;
}
@keyframes statusPulse {
    0%, 100% {
        transform:scale(1);
        box-shadow:0 2px 6px rgba(16, 185, 129, 0.4);
    }
    50% {
        transform:scale(1.15);
        box-shadow:0 2px 12px rgba(16, 185, 129, 0.6);
    }
}
.profile-info {
    display:flex;
    flex-direction:column;
    gap:4px;
}
.profile-name {
    font-size:0.9rem;
    font-weight:700;
    color:var(--dark);
    line-height:1.2;
}
.profile-role {
    font-size:0.65rem;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:3px 8px;
    border-radius:6px;
    text-transform:uppercase;
    letter-spacing:0.5px;
    border:1.5px solid transparent;
    width:fit-content;
}
.profile-role i {
    font-size:0.7rem;
}
/* Super Admin - Vàng kim */
.profile-role.role-super-admin {
    background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color:#8b4513;
    border-color:#ffa500;
    box-shadow:0 2px 8px rgba(255, 215, 0, 0.4);
}
/* Quản trị viên - Xanh dương */
.profile-role.role-admin {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
    border-color:#5568d3;
    box-shadow:0 2px 8px rgba(102, 126, 234, 0.4);
}
/* Biên tập viên - Xanh lá */
.profile-role.role-editor {
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    color:var(--white);
    border-color:#059669;
    box-shadow:0 2px 8px rgba(16, 185, 129, 0.4);
}
/* Animation cho badge vai trò khi menu mở */
.admin-profile-enhanced.menu-open .profile-role {
    animation:rolePulse 2s ease-in-out infinite;
}
@keyframes rolePulse {
    0%, 100% {
        box-shadow:0 2px 8px rgba(102, 126, 234, 0.4);
    }
    50% {
        box-shadow:0 4px 16px rgba(102, 126, 234, 0.8);
    }
}
.profile-arrow {
    font-size:0.75rem;
    color:var(--gray);
    transition:all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    margin-left:4px;
    position:relative;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:20px;
    height:20px;
}
/* Trạng thái khi menu đang mở */
.admin-profile-enhanced.menu-open .profile-arrow {
    transform:rotate(180deg);
    color:var(--primary);
}

/* Legacy support - hide old elements */
.topbar-icon,
.admin-profile,
.admin-avatar,
.admin-info {
    display:none;
}

/* Content Area */
.content-area {padding:32px; max-width:1600px; margin:0 auto;}

/* Welcome Section */
.welcome-section {
    display:grid;
    grid-template-columns:1fr auto;
    gap:32px;
    align-items:center;
    margin-bottom:32px;
    padding:48px 56px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius:28px;
    box-shadow:0 24px 64px rgba(102, 126, 234, 0.4);
    position:relative;
    overflow:hidden;
}
.welcome-section::before {
    content:'';
    position:absolute;
    right:-120px;
    top:-120px;
    width:350px;
    height:350px;
    background:radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius:50%;
    animation:float 8s ease-in-out infinite;
}
.welcome-section::after {
    content:'';
    position:absolute;
    left:-100px;
    bottom:-100px;
    width:300px;
    height:300px;
    background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius:50%;
    animation:float 10s ease-in-out infinite reverse;
}
@keyframes float {
    0%, 100% { transform:translate(0, 0) scale(1); }
    50% { transform:translate(20px, 20px) scale(1.1); }
}
.welcome-text {
    position:relative;
    z-index:1;
}
.welcome-text h1 {
    font-size:2.5rem;
    font-weight:900;
    margin-bottom:16px;
    color:var(--white);
    display:flex;
    align-items:center;
    gap:16px;
    text-shadow:0 4px 12px rgba(0,0,0,0.15);
    letter-spacing:-0.5px;
}
.welcome-text h1 .wave-emoji {
    font-size:2.8rem;
    display:inline-block;
    animation:wave 2s ease-in-out infinite;
    transform-origin:70% 70%;
}
@keyframes wave {
    0%, 100% { transform:rotate(0deg); }
    10% { transform:rotate(14deg); }
    20% { transform:rotate(-8deg); }
    30% { transform:rotate(14deg); }
    40% { transform:rotate(-4deg); }
    50% { transform:rotate(10deg); }
    60% { transform:rotate(0deg); }
}
.welcome-text p {
    color:rgba(255,255,255,0.95);
    font-size:1.1rem;
    font-weight:500;
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}
.welcome-info-item {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 16px;
    background:rgba(255,255,255,0.15);
    border-radius:12px;
    backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,0.2);
    transition:all 0.3s ease;
}
.welcome-info-item:hover {
    background:rgba(255,255,255,0.25);
    transform:translateY(-2px);
}
.welcome-info-item i {
    font-size:1rem;
}
.welcome-actions {
    display:flex;
    gap:16px;
    position:relative;
    z-index:1;
}
.btn-add-new {
    padding:18px 36px;
    background:var(--white);
    color:#667eea;
    border:none;
    border-radius:18px;
    font-weight:700;
    font-size:1.05rem;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:12px;
    transition:all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
    position:relative;
    overflow:hidden;
    white-space:nowrap;
}
.btn-add-new::before {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity:0;
    transition:all 0.4s ease;
    z-index:-1;
}
.btn-add-new:hover::before {
    opacity:1;
}
.btn-add-new:hover {
    color:var(--white);
    transform:translateY(-6px) scale(1.05);
    box-shadow:0 16px 40px rgba(102, 126, 234, 0.4);
}
.btn-add-new i {
    font-size:1.3rem;
    transition:all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.btn-add-new:hover i {
    transform:rotate(180deg) scale(1.2);
}
.btn-add-new:active {
    transform:translateY(-3px) scale(1.02);
}
.btn-secondary {
    padding:18px 36px;
    background:rgba(255,255,255,0.2);
    color:var(--white);
    border:2px solid rgba(255,255,255,0.3);
    border-radius:18px;
    font-weight:700;
    font-size:1.05rem;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:12px;
    transition:all 0.3s ease;
    backdrop-filter:blur(10px);
    white-space:nowrap;
}
.btn-secondary:hover {
    background:rgba(255,255,255,0.3);
    border-color:rgba(255,255,255,0.5);
    transform:translateY(-4px);
    box-shadow:0 12px 32px rgba(0,0,0,0.2);
}
.btn-secondary i {
    font-size:1.2rem;
    transition:transform 0.3s ease;
}
.btn-secondary:hover i {
    transform:scale(1.2);
}
.quick-add-btn {
    padding:14px 28px;
    background:var(--gradient-primary);
    color:var(--white);
    border:none;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    transition:all 0.3s ease;
    display:flex;
    align-items:center;
    gap:10px;
    box-shadow:var(--shadow);
    font-size:0.95rem;
}
.quick-add-btn:hover {
    transform:translateY(-3px);
    box-shadow:var(--shadow-lg);
}
.quick-add-btn i {
    font-size:1rem;
}

/* Stats Grid */
.stats-grid {
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:24px;
    margin-bottom:32px;
}
.stat-card {
    background:var(--white);
    border-radius:20px;
    padding:28px;
    transition:all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor:pointer;
    position:relative;
    overflow:hidden;
    box-shadow:var(--shadow);
}
.stat-card::before {
    content:'';
    position:absolute;
    top:0;
    right:0;
    width:120px;
    height:120px;
    background:var(--gradient-primary);
    border-radius:0 20px 0 100%;
    opacity:0.1;
    transition:all 0.4s ease;
}
.stat-card:hover {
    transform:translateY(-8px);
    box-shadow:var(--shadow-lg);
}
.stat-card:hover::before {
    width:150px;
    height:150px;
    opacity:0.15;
}
.stat-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:20px;
}
.stat-label {
    font-size:0.9rem;
    color:var(--gray);
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.stat-icon {
    width:56px;
    height:56px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--white);
    box-shadow:var(--shadow);
    position:relative;
}
.stat-icon i {
    font-size:1.5rem;
}
.stat-number {
    font-size:2.5rem;
    font-weight:800;
    margin-bottom:12px;
    color:var(--dark);
    letter-spacing:-1px;
}
.stat-trend {
    font-size:0.9rem;
    display:flex;
    align-items:center;
    gap:6px;
    font-weight:600;
    padding:6px 12px;
    border-radius:8px;
    width:fit-content;
}
.stat-trend.up {
    color:var(--success);
    background:rgba(16, 185, 129, 0.1);
}
.stat-trend.down {
    color:var(--danger);
    background:rgba(239, 68, 68, 0.1);
}
.stat-trend i {
    font-size:0.85rem;
}

/* Dashboard Grid */
.dashboard-grid {
    display:grid;
    grid-template-columns:1.5fr 1fr;
    gap:24px;
}
.dashboard-left, .dashboard-right {
    display:flex;
    flex-direction:column;
    gap:24px;
}

/* Card */
.card {
    background:var(--white);
    border-radius:20px;
    padding:28px;
    box-shadow:var(--shadow);
    transition:all 0.3s ease;
}
.card:hover {
    box-shadow:var(--shadow-lg);
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
    color:var(--dark);
}
.card-header h3 i {
    color:var(--primary);
    font-size:1.3rem;
}
.card-header a {
    font-size:0.9rem;
    color:var(--primary);
    font-weight:600;
    display:flex;
    align-items:center;
    gap:6px;
    transition:all 0.3s ease;
}
.card-header a:hover {
    gap:10px;
    color:var(--primary-dark);
}

/* Activities */
.activity-item {
    display:flex;
    gap:16px;
    padding:16px 0;
    border-bottom:1px solid var(--gray-light);
    transition:all 0.3s ease;
}
.activity-item:last-child {
    border:none;
}
.activity-item:hover {
    background:var(--gray-light);
    margin:0 -16px;
    padding:16px;
    border-radius:12px;
}
.activity-icon {
    width:48px;
    height:48px;
    min-width:48px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--white);
    box-shadow:var(--shadow);
}
.activity-icon i {
    font-size:1.1rem;
}
.activity-content {
    flex:1;
}
.activity-text {
    font-size:0.95rem;
    color:var(--dark);
    margin-bottom:6px;
    font-weight:500;
    line-height:1.5;
}
.activity-item-name {
    color:var(--primary);
    font-weight:700;
}
.activity-time {
    font-size:0.85rem;
    color:var(--gray);
    font-weight:500;
    display:flex;
    align-items:center;
    gap:4px;
}
.activity-time i {
    font-size:0.75rem;
}

/* Top Content */
.top-item {
    display:flex;
    gap:16px;
    padding:16px 0;
    border-bottom:1px solid var(--gray-light);
    transition:all 0.3s ease;
}
.top-item:last-child {
    border:none;
}
.top-item:hover {
    background:var(--gray-light);
    margin:0 -16px;
    padding:16px;
    border-radius:12px;
}
.top-rank {
    width:42px;
    height:42px;
    background:var(--gray-light);
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:1.1rem;
    color:var(--gray);
    box-shadow:var(--shadow);
}
.top-item:first-child .top-rank {
    background:linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color:var(--white);
}
.top-item:nth-child(2) .top-rank {
    background:linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
    color:var(--white);
}
.top-item:nth-child(3) .top-rank {
    background:linear-gradient(135deg, #cd7f32 0%, #e89f65 100%);
    color:var(--white);
}
.top-info {
    flex:1;
}
.top-title {
    font-size:1rem;
    font-weight:700;
    margin-bottom:8px;
    color:var(--dark);
    line-height:1.4;
}
.top-meta {
    display:flex;
    gap:16px;
    font-size:0.85rem;
    color:var(--gray);
    font-weight:600;
}
.top-type {
    background:var(--primary);
    color:var(--white);
    padding:4px 12px;
    border-radius:8px;
    font-size:0.75rem;
    font-weight:700;
}
.top-views {
    display:flex;
    align-items:center;
    gap:6px;
}

/* Quick Stats */
.quick-stats {
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:16px;
}
.quick-stat-item {
    padding:24px 16px;
    background:var(--gradient-primary);
    border-radius:16px;
    text-align:center;
    transition:all 0.3s ease;
    cursor:pointer;
    box-shadow:var(--shadow);
    position:relative;
    overflow:hidden;
    min-width:0;
}
.quick-stat-item::before {
    content:'';
    position:absolute;
    top:-50%;
    right:-50%;
    width:100%;
    height:100%;
    background:radial-gradient(circle, rgba(255,255,255,0.2), transparent);
    opacity:0;
    transition:all 0.5s ease;
}
.quick-stat-item:hover::before {
    opacity:1;
    top:0;
    right:0;
}
.quick-stat-item:hover {
    transform:translateY(-6px) scale(1.02);
    box-shadow:var(--shadow-lg);
}
.quick-stat-item:nth-child(2) {
    background:var(--gradient-success);
}
.quick-stat-item:nth-child(3) {
    background:var(--gradient-info);
}
.quick-stat-label {
    font-size:0.8rem;
    color:rgba(255,255,255,0.85);
    margin-bottom:12px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.8px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.quick-stat-value {
    font-size:2.4rem;
    font-weight:800;
    color:var(--white);
    letter-spacing:-1px;
    text-shadow:0 2px 4px rgba(0,0,0,0.1);
}

/* Quick Menu Modal */
.modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(15, 23, 42, 0.75);
    backdrop-filter:blur(8px);
    z-index:9999;
    align-items:center;
    justify-content:center;
    animation:fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from {opacity:0;}
    to {opacity:1;}
}
.quick-menu {
    background:var(--white);
    border-radius:24px;
    padding:36px;
    width:520px;
    max-width:90%;
    box-shadow:var(--shadow-lg);
    animation:slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes slideUp {
    from {
        opacity:0;
        transform:translateY(40px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}
.quick-menu-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:28px;
}
.quick-menu h3 {
    font-size:1.5rem;
    font-weight:800;
    background:var(--gradient-primary);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}
.modal-close {
    width:36px;
    height:36px;
    background:var(--gray-light);
    border:none;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:all 0.3s ease;
    font-size:1.2rem;
}
.modal-close:hover {
    background:var(--danger);
    color:var(--white);
    transform:rotate(90deg);
}
.quick-actions {
    display:grid;
    gap:14px;
}
.quick-action-btn {
    display:flex;
    align-items:center;
    gap:16px;
    padding:18px 20px;
    background:var(--gray-light);
    border:2px solid transparent;
    border-radius:16px;
    text-decoration:none;
    color:var(--dark);
    transition:all 0.3s ease;
    position:relative;
    overflow:hidden;
}
.quick-action-btn::before {
    content:'';
    position:absolute;
    left:0;
    top:0;
    width:4px;
    height:100%;
    background:var(--primary);
    transform:scaleY(0);
    transition:all 0.3s ease;
}
.quick-action-btn:hover {
    background:var(--white);
    border-color:var(--primary);
    transform:translateX(8px);
    box-shadow:var(--shadow);
}
.quick-action-btn:hover::before {
    transform:scaleY(1);
}
.quick-action-btn i {
    font-size:1.4rem;
    width:48px;
    height:48px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--white);
    border-radius:12px;
    box-shadow:var(--shadow);
}
.quick-action-btn span {
    font-weight:700;
    font-size:1rem;
}

/* Empty State */
.empty-state {
    text-align:center;
    padding:60px 20px;
    color:var(--gray);
}
.empty-state i {
    font-size:4rem;
    color:var(--gray-light);
    margin-bottom:20px;
    opacity:0.5;
}
.empty-state p {
    font-size:1.1rem;
    font-weight:600;
    color:var(--gray);
    margin-bottom:8px;
}
.empty-state span {
    font-size:0.9rem;
    color:var(--gray);
    opacity:0.7;
}

/* Loading State */
.loading-spinner {
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
}
.loading-spinner i {
    font-size:2rem;
    color:var(--primary);
    animation:spin 1s linear infinite;
}
@keyframes spin {
    from { transform:rotate(0deg); }
    to { transform:rotate(360deg); }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width:8px;
    height:8px;
}
::-webkit-scrollbar-track {
    background:var(--gray-light);
    border-radius:4px;
}
::-webkit-scrollbar-thumb {
    background:var(--primary);
    border-radius:4px;
}
::-webkit-scrollbar-thumb:hover {
    background:var(--dark);
}

/* Tooltip */
[data-tooltip] {
    position:relative;
}
[data-tooltip]::before {
    content:attr(data-tooltip);
    position:absolute;
    bottom:calc(100% + 8px);
    left:50%;
    transform:translateX(-50%);
    background:var(--dark);
    color:var(--white);
    padding:6px 12px;
    border-radius:8px;
    font-size:0.8rem;
    white-space:nowrap;
    opacity:0;
    pointer-events:none;
    transition:opacity 0.3s ease;
}
[data-tooltip]:hover::before {
    opacity:1;
}

/* Card Hover Effects */
.card {
    transition:all 0.3s ease;
}
.card:hover {
    box-shadow:0 12px 40px rgba(0,0,0,0.1);
    transform:translateY(-2px);
}

/* Stat Card Animations */
@keyframes pulse {
    0%, 100% {
        transform:scale(1);
    }
    50% {
        transform:scale(1.05);
    }
}
.stat-card:hover .stat-icon {
    animation:pulse 1s ease-in-out infinite;
}

/* Activity Item Animations */
.activity-item {
    animation:fadeInUp 0.5s ease-out backwards;
}
.activity-item:nth-child(1) { animation-delay:0.1s; }
.activity-item:nth-child(2) { animation-delay:0.2s; }
.activity-item:nth-child(3) { animation-delay:0.3s; }
.activity-item:nth-child(4) { animation-delay:0.4s; }
.activity-item:nth-child(5) { animation-delay:0.5s; }

@keyframes fadeInUp {
    from {
        opacity:0;
        transform:translateY(20px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}

/* Top Item Animations */
.top-item {
    animation:fadeInLeft 0.5s ease-out backwards;
}
.top-item:nth-child(1) { animation-delay:0.1s; }
.top-item:nth-child(2) { animation-delay:0.2s; }
.top-item:nth-child(3) { animation-delay:0.3s; }
.top-item:nth-child(4) { animation-delay:0.4s; }

@keyframes fadeInLeft {
    from {
        opacity:0;
        transform:translateX(-20px);
    }
    to {
        opacity:1;
        transform:translateX(0);
    }
}

/* Welcome Section Animation */
.welcome-section {
    animation:fadeInDown 0.6s ease-out;
}
@keyframes fadeInDown {
    from {
        opacity:0;
        transform:translateY(-30px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}

/* Stats Card Entry Animation */
.stat-card {
    animation:scaleIn 0.5s ease-out backwards;
}
.stat-card:nth-child(1) { animation-delay:0.1s; }
.stat-card:nth-child(2) { animation-delay:0.2s; }
.stat-card:nth-child(3) { animation-delay:0.3s; }
.stat-card:nth-child(4) { animation-delay:0.4s; }

@keyframes scaleIn {
    from {
        opacity:0;
        transform:scale(0.8);
    }
    to {
        opacity:1;
        transform:scale(1);
    }
}

/* Badge Pulse Animation */
.badge {
    animation:badgePulse 2s ease-in-out infinite;
}
@keyframes badgePulse {
    0%, 100% {
        box-shadow:0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    50% {
        box-shadow:0 0 0 8px rgba(239, 68, 68, 0);
    }
}

/* Utility Classes */
.text-center { text-align:center; }
.text-right { text-align:right; }
.text-left { text-align:left; }
.mt-1 { margin-top:8px; }
.mt-2 { margin-top:16px; }
.mt-3 { margin-top:24px; }
.mb-1 { margin-bottom:8px; }
.mb-2 { margin-bottom:16px; }
.mb-3 { margin-bottom:24px; }
.p-1 { padding:8px; }
.p-2 { padding:16px; }
.p-3 { padding:24px; }
.fw-bold { font-weight:700; }
.fw-normal { font-weight:400; }
.text-primary { color:var(--primary); }
.text-success { color:var(--success); }
.text-danger { color:var(--danger); }
.text-warning { color:var(--warning); }
.text-muted { color:var(--gray); }

/* Focus Visible for Accessibility */
*:focus-visible {
    outline:3px solid var(--primary);
    outline-offset:2px;
    border-radius:4px;
}

/* Skip to content for Accessibility */
.skip-to-content {
    position:absolute;
    top:-40px;
    left:0;
    background:var(--primary);
    color:var(--white);
    padding:8px 16px;
    text-decoration:none;
    border-radius:0 0 8px 0;
    z-index:10000;
}
.skip-to-content:focus {
    top:0;
}

/* Print Styles */
@media print {
    .sidebar, .topbar, .btn-add-new, .quick-menu {
        display:none;
    }
    .main-content {
        margin-left:0;
    }
    .card {
        break-inside:avoid;
    }
}

/* Responsive */
@media(max-width:1200px){
    .dashboard-grid {
        grid-template-columns:1fr;
    }
    .topbar-search {
        width:280px;
    }
    .stats-grid {
        grid-template-columns:repeat(2, 1fr);
    }
}
@media(max-width:1024px){
    .stats-grid {
        grid-template-columns:repeat(2, 1fr);
    }
    .quick-stats {
        grid-template-columns:repeat(3, 1fr);
        gap:12px;
    }
    .quick-stat-label {
        font-size:0.75rem;
    }
    .quick-stat-value {
        font-size:2rem;
    }
}
/* Page Loader */
.page-loader {
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:var(--white);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
    transition:opacity 0.5s ease, visibility 0.5s ease;
}
.page-loader.hidden {
    opacity:0;
    visibility:hidden;
}
.loader-content {
    text-align:center;
}
.loader-spinner {
    width:50px;
    height:50px;
    border:4px solid var(--gray-light);
    border-top-color:var(--primary);
    border-radius:50%;
    animation:spin 1s linear infinite;
    margin:0 auto 20px;
}
.loader-text {
    color:var(--gray);
    font-weight:600;
}

/* Mobile Overlay */
.mobile-overlay {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    z-index:1000;
    opacity:0;
    transition:opacity 0.3s ease;
}
.mobile-overlay.active {
    display:block;
    opacity:1;
}

@media(max-width:768px){
    .sidebar {
        position:fixed;
        left:-280px;
        width:280px;
        transition:left 0.3s ease;
        z-index:1001;
    }
    .sidebar.mobile-show {
        left:0;
    }
    .main-content {
        margin-left:0;
    }
    .hamburger {
        display:flex;
    }
    .topbar-search {
        display:none;
    }
    .welcome-section {
        grid-template-columns:1fr;
        gap:24px;
        padding:32px 28px;
        text-align:center;
    }
    .welcome-text h1 {
        font-size:1.6rem;
        justify-content:center;
    }
    .welcome-text p {
        justify-content:center;
        font-size:0.95rem;
    }
    .welcome-actions {
        justify-content:center;
    }
    .btn-add-new {
        padding:16px 28px;
        font-size:0.95rem;
    }
    .stats-grid {
        grid-template-columns:repeat(2, 1fr);
        gap:16px;
    }
    .stat-number {
        font-size:2rem;
    }
    .profile-info {
        display:flex;
        gap:4px;
    }
    .profile-name {
        display:none;
    }
    .profile-role {
        font-size:0.65rem;
        padding:3px 8px;
        gap:4px;
    }
    .profile-role i {
        font-size:0.7rem;
    }
    .topbar-action-icon .icon-label {
        display:none;
    }
    .topbar-action-icon {
        padding:8px;
    }
    .topbar-divider {
        display:none;
    }
    .admin-profile-enhanced {
        padding:8px;
    }
    .profile-arrow {
        display:none;
    }
    .content-area {
        padding:20px;
    }
    .card {
        padding:20px;
    }
    .quick-stats {
        grid-template-columns:1fr;
        gap:12px;
    }
    .quick-stat-value {
        font-size:2rem;
    }
}
@media(max-width:480px){
    .quick-stats {
        grid-template-columns:1fr;
    }
}

/* Auth Modal Styles */
.auth-modal {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(15, 23, 42, 0.85);
    backdrop-filter:blur(12px);
    z-index:10000;
    align-items:center;
    justify-content:center;
    padding:20px;
    animation:fadeIn 0.3s ease;
}
.auth-modal-content {
    background:var(--white);
    border-radius:28px;
    padding:48px 40px;
    max-width:480px;
    width:100%;
    position:relative;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    animation:slideUpModal 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    max-height:90vh;
    overflow-y:auto;
}
@keyframes fadeIn {
    from {opacity:0;}
    to {opacity:1;}
}
@keyframes slideUpModal {
    from {
        opacity:0;
        transform:translateY(40px) scale(0.95);
    }
    to {
        opacity:1;
        transform:translateY(0) scale(1);
    }
}
.auth-modal-close {
    position:absolute;
    top:20px;
    right:20px;
    width:40px;
    height:40px;
    border:none;
    background:var(--gray-light);
    border-radius:12px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all 0.3s ease;
    font-size:1.1rem;
    color:var(--gray);
}
.auth-modal-close:hover {
    background:var(--danger);
    color:var(--white);
    transform:rotate(90deg);
}
.auth-modal-header {
    text-align:center;
    margin-bottom:36px;
}
.auth-logo {
    width:80px;
    height:80px;
    background:var(--gradient-primary);
    border-radius:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 24px;
    box-shadow:0 12px 28px rgba(102, 126, 234, 0.4);
    animation:logoFloat 3s ease-in-out infinite;
}
.auth-logo i {
    font-size:2.5rem;
    color:var(--white);
}
.auth-modal-header h2 {
    font-size:1.8rem;
    font-weight:900;
    color:var(--dark);
    margin-bottom:10px;
    letter-spacing:-0.5px;
}
.auth-modal-header p {
    color:var(--gray);
    font-size:1rem;
    font-weight:500;
}
.auth-form {
    display:flex;
    flex-direction:column;
    gap:20px;
}
.auth-form-group {
    display:flex;
    flex-direction:column;
    gap:10px;
}
.auth-form-group label {
    font-weight:700;
    color:var(--dark);
    font-size:0.95rem;
    display:flex;
    align-items:center;
    gap:8px;
}
.auth-form-group label i {
    color:var(--primary);
    font-size:0.9rem;
}
.auth-input-wrapper {
    position:relative;
}
.auth-input-wrapper > i:first-of-type {
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);
    color:var(--gray);
    font-size:1.1rem;
    transition:all 0.3s ease;
    z-index:1;
}
.auth-input-wrapper:focus-within > i:first-of-type {
    color:var(--primary);
    transform:translateY(-50%) scale(1.1);
}
.auth-input-wrapper input {
    width:100%;
    padding:16px 20px 16px 52px;
    border:2px solid var(--gray-light);
    border-radius:14px;
    font-size:1rem;
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background:var(--gray-light);
    font-weight:500;
    color:var(--dark);
}
.auth-input-wrapper input::placeholder {
    color:#9ca3af;
    font-weight:500;
}
.auth-input-wrapper input:hover {
    border-color:#cbd5e1;
}
.auth-input-wrapper input:focus {
    outline:none;
    border-color:var(--primary);
    background:var(--white);
    box-shadow:0 0 0 4px rgba(99,102,241,0.1);
    transform:translateY(-1px);
}
.auth-password-toggle {
    position:absolute !important;
    right:18px !important;
    top:50% !important;
    transform:translateY(-50%) !important;
    left:auto !important;
    cursor:pointer;
    color:var(--gray);
    font-size:1.05rem;
    transition:all 0.3s ease;
    z-index:2;
    width:36px;
    height:36px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
}
.auth-password-toggle:hover {
    color:var(--primary);
    background:rgba(99,102,241,0.1);
}
.auth-submit-btn {
    width:100%;
    padding:18px 28px;
    background:var(--gradient-primary);
    color:var(--white);
    border:none;
    border-radius:14px;
    font-size:1.05rem;
    font-weight:800;
    cursor:pointer;
    transition:all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:0 8px 24px rgba(102, 126, 234, 0.35);
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    text-transform:uppercase;
    letter-spacing:0.5px;
    margin-top:8px;
    position:relative;
    overflow:hidden;
}
.auth-submit-btn::before {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
    opacity:0;
    transition:opacity 0.4s ease;
}
.auth-submit-btn:hover::before {
    opacity:1;
}
.auth-submit-btn:hover {
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 16px 40px rgba(102, 126, 234, 0.5);
}
.auth-submit-btn:active {
    transform:translateY(-1px) scale(0.98);
}
.auth-submit-btn i {
    font-size:1.15rem;
    transition:transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.auth-submit-btn:hover i {
    transform:translateX(5px);
}
.auth-submit-btn:disabled {
    opacity:0.7;
    cursor:not-allowed;
    transform:none;
}
.auth-divider {
    display:flex;
    align-items:center;
    gap:16px;
    margin:28px 0 20px;
    color:var(--gray);
    font-size:0.9rem;
    font-weight:600;
}
.auth-divider::before,
.auth-divider::after {
    content:'';
    flex:1;
    height:1px;
    background:linear-gradient(to right, transparent, var(--gray-light), transparent);
}
.auth-switch {
    text-align:center;
    color:var(--gray);
    font-size:0.95rem;
    font-weight:500;
}
.auth-switch-btn {
    background:none;
    border:none;
    color:var(--primary);
    font-weight:700;
    cursor:pointer;
    font-size:0.95rem;
    transition:all 0.3s ease;
    text-decoration:underline;
    padding:0;
    margin-left:6px;
}
.auth-switch-btn:hover {
    color:var(--primary-dark);
    transform:translateX(3px);
}

/* Auth Modal Responsive */
@media(max-width:768px){
    .auth-modal-content {
        padding:36px 28px;
        max-width:420px;
    }
    .auth-modal-header h2 {
        font-size:1.6rem;
    }
    .auth-input-wrapper input {
        padding:14px 18px 14px 48px;
        font-size:0.95rem;
    }
    .auth-submit-btn {
        padding:16px 24px;
        font-size:1rem;
    }
}
@media(max-width:480px){
    .auth-modal-content {
        padding:32px 24px;
    }
    .auth-logo {
        width:70px;
        height:70px;
    }
    .auth-logo i {
        font-size:2rem;
    }
}
</style>
</head>
<body>

<!-- Skip to Content for Accessibility -->
<a href="#main-content" class="skip-to-content">Bỏ qua đến nội dung chính</a>

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
    <div class="loader-content">
        <div class="loader-spinner"></div>
        <div class="loader-text">Đang tải...</div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

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
                <div class="menu-item active" onclick="location.href='index.php'">
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
    <main class="main-content" id="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="globalSearch" placeholder="Tìm kiếm nội dung..." autocomplete="off">
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
            <div class="topbar-right">
                <!-- Notifications Icon -->
                <div class="topbar-action-icon" onclick="toggleNotifications(event)">
                    <div class="icon-wrapper">
                        <i class="fas fa-bell"></i>
                        <?php if($unread_notifications > 0): ?>
                        <span class="notification-badge pulse"><?php echo $unread_notifications; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-label">Thông báo</span>
                </div>

                <!-- Messages Icon -->
                <div class="topbar-action-icon" onclick="toggleMessages(event)">
                    <div class="icon-wrapper">
                        <i class="fas fa-envelope"></i>
                        <?php if($unread_messages > 0): ?>
                        <span class="notification-badge pulse"><?php echo $unread_messages; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-label">Tin nhắn</span>
                </div>

                <!-- Divider -->
                <div class="topbar-divider"></div>

                <!-- Admin Profile -->
                <div class="admin-profile-enhanced" onclick="toggleProfileMenu()">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            <?php 
                            $name = $_SESSION['admin_name'] ?? 'Quản trị viên';
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
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Quản trị viên'); ?></span>
                        <?php 
                        // Hiển thị vai trò với badge đẹp và icon
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
            <!-- WELCOME SECTION -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>
                        <span class="wave-emoji">👋</span>
                        Chào mừng quản trị viên <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Quản trị viên'); ?>
                    </h1>
                    <p>
                        <span class="welcome-info-item">
                            <i class="fas fa-calendar-day"></i>
                            <?php 
                            $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                            echo $days[date('w')] . ', ' . date('d/m/Y'); 
                            ?>
                        </span>
                        <span class="welcome-info-item">
                            <i class="fas fa-check-circle"></i>
                            Hệ thống hoạt động ổn định
                        </span>
                    </p>
                </div>
                <div class="welcome-actions">
                    <button class="btn-add-new" onclick="document.getElementById('quickMenu').style.display='flex'">
                        <i class="fas fa-plus-circle"></i>
                        Thêm nội dung
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" onclick="location.href='nguoidung.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Người dùng</span>
                        <div class="stat-icon" style="background:#007bff;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['users']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['users']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['users']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['users']['change']; ?> tháng này
                        </div>
                    </div>
                </div>

                <div class="stat-card" onclick="location.href='vanhoa.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Bài viết</span>
                        <div class="stat-icon" style="background:#28a745;">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['articles']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['articles']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['articles']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['articles']['change']; ?> bài mới
                        </div>
                    </div>
                </div>

                <div class="stat-card" onclick="location.href='chua.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Chùa Khmer</span>
                        <div class="stat-icon" style="background:#6c757d;">
                            <i class="fas fa-place-of-worship"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['temples']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['temples']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['temples']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['temples']['change']; ?> mới
                        </div>
                    </div>
                </div>

                <div class="stat-card" onclick="location.href='lehoi.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Lễ hội</span>
                        <div class="stat-icon" style="background:#dc3545;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['festivals']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['festivals']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['festivals']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['festivals']['change']; ?> sắp tới
                        </div>
                    </div>
                </div>

                <div class="stat-card" onclick="location.href='hoctiengkhmer.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Bài học</span>
                        <div class="stat-icon" style="background:#ffc107;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['lessons']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['lessons']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['lessons']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['lessons']['change']; ?> từ vựng mới
                        </div>
                    </div>
                </div>

                <div class="stat-card" onclick="location.href='truyen.php'" style="cursor:pointer;">
                    <div class="stat-header">
                        <span class="stat-label">Truyện dân gian</span>
                        <div class="stat-icon" style="background:#6f42c1;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-number"><?php echo number_format($stats['stories']['count']); ?></div>
                        <div class="stat-trend <?php echo $stats['stories']['trend']; ?>">
                            <i class="fas fa-arrow-<?php echo $stats['stories']['trend']=='up'?'up':'down'; ?>"></i>
                            <?php echo $stats['stories']['change']; ?> tháng này
                        </div>
                    </div>
                </div>
            </div>

            <!-- TWO COLUMNS LAYOUT -->
            <div class="dashboard-grid">
                <!-- LEFT COLUMN -->
                <div class="dashboard-left">
                    <!-- RECENT ACTIVITIES -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock"></i> Hoạt động gần đây</h3>
                            <a href="hoatdong.php" style="font-size:0.9rem; color:#666;">Xem tất cả →</a>
                        </div>
                        <div class="activities-list">
                            <?php if(empty($recent_activities)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Chưa có hoạt động nào</p>
                                <span>Các hoạt động sẽ hiển thị ở đây</span>
                            </div>
                            <?php else: ?>
                                <?php foreach($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background:<?php echo $activity['color']; ?>;">
                                        <i class="fas <?php echo $activity['icon']; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <strong><?php echo $activity['user']; ?></strong> 
                                            <?php echo $activity['action']; ?>: 
                                            <span class="activity-item-name"><?php echo $activity['item']; ?></span>
                                        </div>
                                        <div class="activity-time"><?php echo $activity['time']; ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="dashboard-right">
                    <!-- TOP CONTENT -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-fire"></i> Nội dung nổi bật</h3>
                        </div>
                        <div class="top-content-list">
                            <?php if(empty($top_content)): ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-bar"></i>
                                <p>Chưa có dữ liệu thống kê</p>
                                <span>Nội dung nổi bật sẽ hiển thị ở đây</span>
                            </div>
                            <?php else: ?>
                                <?php foreach($top_content as $index => $content): ?>
                                <div class="top-item">
                                    <div class="top-rank">#<?php echo $index+1; ?></div>
                                    <div class="top-info">
                                        <div class="top-title"><?php echo $content['title']; ?></div>
                                        <div class="top-meta">
                                            <span class="top-type"><?php echo $content['type']; ?></span>
                                            <span class="top-views"><i class="fas fa-eye"></i> <?php echo number_format($content['views']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- QUICK STATS -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Thống kê nhanh</h3>
                        </div>
                        <div class="quick-stats">
                            <div class="quick-stat-item">
                                <div class="quick-stat-label">Nội dung</div>
                                <div class="quick-stat-value"><?php 
                                    $total_content = $stats['articles']['count'] + $stats['temples']['count'] + 
                                                    $stats['festivals']['count'] + $stats['stories']['count'];
                                    echo number_format($total_content);
                                ?></div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="quick-stat-label">Người dùng</div>
                                <div class="quick-stat-value"><?php 
                                    $total_users = $db->query("SELECT COUNT(*) as count FROM nguoi_dung")[0]['count'] ?? 0;
                                    echo number_format($total_users);
                                ?></div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="quick-stat-label">Bài học</div>
                                <div class="quick-stat-value"><?php 
                                    $total_lessons = $db->query("SELECT COUNT(*) as count FROM bai_hoc")[0]['count'] ?? 0;
                                    echo number_format($total_lessons);
                                ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ADD MENU MODAL -->
        <div class="modal" id="quickMenu" onclick="this.style.display='none'">
            <div class="quick-menu" onclick="event.stopPropagation()">
                <div class="quick-menu-header">
                    <h3>Thêm nội dung mới</h3>
                    <button class="modal-close" onclick="document.getElementById('quickMenu').style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="quick-actions">
                    <?php foreach($quick_actions as $qa): ?>
                    <a href="<?php echo $qa['link']; ?>" class="quick-action-btn">
                        <i class="fas <?php echo $qa['icon']; ?>" style="color:<?php echo $qa['color']; ?>;"></i>
                        <span><?php echo $qa['label']; ?></span>
                        <i class="fas fa-arrow-right" style="margin-left:auto; font-size:0.9rem; color:var(--gray);"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- LOGIN MODAL -->
<div class="auth-modal" id="loginModal">
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeLoginModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="auth-modal-header">
            <div class="auth-logo">
                <i class="fas fa-dharmachakra"></i>
            </div>
            <h2>🔐 Đăng nhập</h2>
            <p>Chào mừng bạn quay trở lại!</p>
        </div>
        
        <form onsubmit="handleLogin(event)" class="auth-form">
            <div class="auth-form-group">
                <label for="loginUsername">
                    <i class="fas fa-user"></i>
                    Tên đăng nhập
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" id="loginUsername" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
            </div>
            
            <div class="auth-form-group">
                <label for="loginPassword">
                    <i class="fas fa-lock"></i>
                    Mật khẩu
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-key"></i>
                    <input type="password" id="loginPassword" name="password" placeholder="Nhập mật khẩu" required>
                    <i class="fas fa-eye auth-password-toggle" id="loginPasswordToggle" onclick="togglePasswordVisibility('loginPassword', 'loginPasswordToggle')"></i>
                </div>
            </div>
            
            <button type="submit" class="auth-submit-btn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Đăng nhập</span>
            </button>
        </form>
        
        <div class="auth-divider">
            <span>hoặc</span>
        </div>
        
        <div class="auth-switch">
            <span>Chưa có tài khoản?</span>
            <button onclick="switchToRegister()" class="auth-switch-btn">Đăng ký ngay</button>
        </div>
    </div>
</div>

<!-- REGISTER MODAL -->
<div class="auth-modal" id="registerModal">
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeRegisterModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="auth-modal-header">
            <div class="auth-logo">
                <i class="fas fa-dharmachakra"></i>
            </div>
            <h2>✨ Đăng ký tài khoản</h2>
            <p>Tạo tài khoản mới để bắt đầu</p>
        </div>
        
        <form onsubmit="handleRegister(event)" class="auth-form">
            <div class="auth-form-group">
                <label for="registerFullname">
                    <i class="fas fa-id-card"></i>
                    Họ và tên
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="registerFullname" name="fullname" placeholder="Nhập họ và tên đầy đủ" required>
                </div>
            </div>
            
            <div class="auth-form-group">
                <label for="registerUsername">
                    <i class="fas fa-user"></i>
                    Tên đăng nhập
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" id="registerUsername" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
            </div>
            
            <div class="auth-form-group">
                <label for="registerEmail">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-at"></i>
                    <input type="email" id="registerEmail" name="email" placeholder="Nhập địa chỉ email" required>
                </div>
            </div>
            
            <div class="auth-form-group">
                <label for="registerPassword">
                    <i class="fas fa-lock"></i>
                    Mật khẩu
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-key"></i>
                    <input type="password" id="registerPassword" name="password" placeholder="Nhập mật khẩu" required minlength="6">
                    <i class="fas fa-eye auth-password-toggle" id="registerPasswordToggle" onclick="togglePasswordVisibility('registerPassword', 'registerPasswordToggle')"></i>
                </div>
            </div>
            
            <div class="auth-form-group">
                <label for="registerConfirmPassword">
                    <i class="fas fa-lock"></i>
                    Xác nhận mật khẩu
                </label>
                <div class="auth-input-wrapper">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" id="registerConfirmPassword" name="confirm_password" placeholder="Nhập lại mật khẩu" required minlength="6">
                    <i class="fas fa-eye auth-password-toggle" id="registerConfirmPasswordToggle" onclick="togglePasswordVisibility('registerConfirmPassword', 'registerConfirmPasswordToggle')"></i>
                </div>
            </div>
            
            <button type="submit" class="auth-submit-btn">
                <i class="fas fa-user-plus"></i>
                <span>Đăng ký</span>
            </button>
        </form>
        
        <div class="auth-divider">
            <span>hoặc</span>
        </div>
        
        <div class="auth-switch">
            <span>Đã có tài khoản?</span>
            <button onclick="switchToLogin()" class="auth-switch-btn">Đăng nhập ngay</button>
        </div>
    </div>
</div>

<script>
function logout() {
    if(confirm('Bạn có chắc muốn đăng xuất?')) {
        window.location.href = 'dangxuat.php';
    }
}

function toggleNotifications(e) {
    e.stopPropagation();
    closeAllDropdowns();
    
    const target = e.currentTarget;
    const existing = document.querySelector('.dropdown-notifications');
    if(existing) {
        existing.remove();
        return;
    }
    
    // Show loading state
    const loadingDropdown = document.createElement('div');
    loadingDropdown.className = 'dropdown-notifications dropdown-menu';
    loadingDropdown.innerHTML = `
        <div class="dropdown-header">
            <strong>Thông báo</strong>
        </div>
        <div class="dropdown-body">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
    `;
    target.style.position = 'relative';
    target.appendChild(loadingDropdown);
    addDropdownStyles();
    
    // Fetch notifications from server
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            // Remove loading dropdown
            loadingDropdown.remove();
            
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-notifications dropdown-menu';
            
            let notifHTML = '';
            if(data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notif => {
                    const iconMap = {
                        'success': { icon: 'fa-check-circle', color: '#10b981' },
                        'warning': { icon: 'fa-exclamation-triangle', color: '#f59e0b' },
                        'info': { icon: 'fa-info-circle', color: '#6366f1' },
                        'error': { icon: 'fa-times-circle', color: '#ef4444' },
                        'user': { icon: 'fa-user-plus', color: '#8b5cf6' },
                        'comment': { icon: 'fa-comment', color: '#06b6d4' },
                        'system': { icon: 'fa-cog', color: '#6b7280' }
                    };
                    
                    const iconInfo = iconMap[notif.loai] || iconMap['info'];
                    const isUnread = notif.trang_thai === 'chua_doc';
                    
                    notifHTML += `
                        <div class="dropdown-item ${isUnread ? 'unread' : ''}" onclick="markNotificationRead(${notif.ma_thong_bao}, '${notif.lien_ket || '#'}')">
                            <div class="notif-icon" style="background:${iconInfo.color};">
                                <i class="fas ${iconInfo.icon}"></i>
                            </div>
                            <div class="notif-content">
                                <div class="notif-title">${notif.tieu_de}</div>
                                <div class="notif-message">${notif.noi_dung || ''}</div>
                                <div class="notif-time">${notif.thoi_gian_hien_thi}</div>
                            </div>
                            ${isUnread ? '<div class="notif-unread-dot"></div>' : ''}
                        </div>
                    `;
                });
            } else {
                notifHTML = `
                    <div class="dropdown-empty">
                        <i class="fas fa-bell-slash"></i>
                        <p>Không có thông báo nào</p>
                    </div>
                `;
            }
            
            dropdown.innerHTML = `
                <div class="dropdown-header">
                    <div>
                        <strong>Thông báo</strong>
                        ${data.unread_count > 0 ? `<span class="notif-count">${data.unread_count}</span>` : ''}
                    </div>
                    ${data.unread_count > 0 ? '<a href="#" onclick="markAllNotificationsRead(event)" style="font-size:0.85rem; color:#6366f1;"><i class="fas fa-check-double"></i> Đánh dấu đã đọc</a>' : ''}
                </div>
                <div class="dropdown-body">
                    ${notifHTML}
                </div>
                <div class="dropdown-footer">
                    <a href="thongbao.php">Xem tất cả thông báo →</a>
                </div>
            `;
            
            target.style.position = 'relative';
            target.appendChild(dropdown);
            addDropdownStyles();
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            // Fallback UI
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-notifications dropdown-menu';
            dropdown.innerHTML = `
                <div class="dropdown-body">
                    <div class="dropdown-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không thể tải thông báo</p>
                    </div>
                </div>
            `;
            target.style.position = 'relative';
            target.appendChild(dropdown);
            addDropdownStyles();
        });
}

function toggleMessages(e) {
    e.stopPropagation();
    closeAllDropdowns();
    
    const target = e.currentTarget;
    const existing = document.querySelector('.dropdown-messages');
    if(existing) {
        existing.remove();
        return;
    }
    
    // Show loading state
    const loadingDropdown = document.createElement('div');
    loadingDropdown.className = 'dropdown-messages dropdown-menu';
    loadingDropdown.innerHTML = `
        <div class="dropdown-header">
            <strong>Tin nhắn</strong>
        </div>
        <div class="dropdown-body">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
    `;
    target.style.position = 'relative';
    target.appendChild(loadingDropdown);
    addDropdownStyles();
    
    // Fetch messages from server
    fetch('get_messages.php')
        .then(response => response.json())
        .then(data => {
            // Remove loading dropdown
            loadingDropdown.remove();
            
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-messages dropdown-menu';
            
            let msgHTML = '';
            if(data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const isUnread = msg.trang_thai === 'chua_doc';
                    const initials = getInitials(msg.ten_nguoi_gui);
                    const avatarColor = getAvatarColor(msg.ma_nguoi_gui);
                    
                    msgHTML += `
                        <div class="dropdown-item ${isUnread ? 'unread' : ''}" onclick="openMessage(${msg.ma_tin_nhan}, '${msg.lien_ket || '#'}')">
                            <div class="msg-avatar" style="background:${avatarColor};">${initials}</div>
                            <div class="msg-content">
                                <div class="msg-sender">${msg.ten_nguoi_gui}</div>
                                <div class="msg-preview">${msg.noi_dung_preview}</div>
                                <div class="msg-time">${msg.thoi_gian_hien_thi}</div>
                            </div>
                            ${isUnread ? '<div class="msg-unread-dot"></div>' : ''}
                        </div>
                    `;
                });
            } else {
                msgHTML = `
                    <div class="dropdown-empty">
                        <i class="fas fa-envelope-open"></i>
                        <p>Không có tin nhắn nào</p>
                    </div>
                `;
            }
            
            dropdown.innerHTML = `
                <div class="dropdown-header">
                    <div>
                        <strong>Tin nhắn</strong>
                        ${data.unread_count > 0 ? `<span class="notif-count">${data.unread_count}</span>` : ''}
                    </div>
                </div>
                <div class="dropdown-body">
                    ${msgHTML}
                </div>
                <div class="dropdown-footer">
                    <a href="tinnhan.php">Xem tất cả tin nhắn →</a>
                </div>
            `;
            
            target.style.position = 'relative';
            target.appendChild(dropdown);
            addDropdownStyles();
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            // Fallback UI
            loadingDropdown.remove();
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-messages dropdown-menu';
            dropdown.innerHTML = `
                <div class="dropdown-body">
                    <div class="dropdown-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không thể tải tin nhắn</p>
                    </div>
                </div>
            `;
            target.style.position = 'relative';
            target.appendChild(dropdown);
            addDropdownStyles();
        });
}

function getInitials(name) {
    if(!name) return 'NN';
    const words = name.trim().split(' ');
    if(words.length >= 2) {
        return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

function getAvatarColor(id) {
    const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];
    return colors[id % colors.length];
}

function openMessage(messageId, link) {
    fetch('mark_message_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message_id: messageId })
    }).then(() => {
        // Update badge count
        updateMessageBadge();
        
        // Redirect if has link
        if(link && link !== '#') {
            window.location.href = link;
        } else {
            window.location.href = 'tinnhan.php?id=' + messageId;
        }
    });
}

function composeMessage(e) {
    e.preventDefault();
    e.stopPropagation();
    window.location.href = 'tinnhan.php?action=compose';
}

function updateMessageBadge() {
    fetch('get_message_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.topbar-icon[title="Tin nhắn"] .badge');
            if(data.count > 0) {
                if(badge) {
                    badge.textContent = data.count;
                } else {
                    const icon = document.querySelector('.topbar-icon[title="Tin nhắn"]');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge';
                    newBadge.textContent = data.count;
                    icon.appendChild(newBadge);
                }
            } else {
                badge?.remove();
            }
        });
}

function toggleProfileMenu() {
    const profileBtn = document.querySelector('.admin-profile-enhanced');
    const existingMenu = document.querySelector('.user-menu');
    
    // Nếu menu đang mở, đóng nó lại
    if(existingMenu) {
        existingMenu.style.animation = 'slideUp 0.3s ease-out reverse';
        profileBtn.classList.remove('menu-open');
        setTimeout(() => {
            existingMenu.remove();
        }, 300);
        return;
    }
    
    // Đóng các dropdown khác
    closeAllDropdowns();
    
    // Thêm class menu-open
    profileBtn.classList.add('menu-open');
    
    const dropdown = document.createElement('div');
    dropdown.className = 'user-menu dropdown-menu';
    
    // Get role info from PHP session
    const roleElement = document.querySelector('.profile-role');
    const roleClass = roleElement?.className.match(/role-\w+/)?.[0] || 'role-editor';
    const roleColors = {
        'role-super-admin': 'linear-gradient(135deg, #ffd700 0%, #ffed4e 100%)',
        'role-admin': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'role-editor': 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
    };
    
    dropdown.innerHTML = `
        <div class="user-menu-header" style="background: ${roleColors[roleClass]};">
            <div class="user-menu-avatar">
                ${document.querySelector('.profile-avatar').innerHTML}
            </div>
            <div class="user-menu-info">
                <div class="user-menu-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                <div class="user-menu-email"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?></div>
            </div>
        </div>
        <div class="user-menu-body">
            <div class="user-menu-item" onclick="location.href='caidat.php'">
                <i class="fas fa-user-circle"></i>
                <span>Thông tin cá nhân</span>
            </div>
            <div class="user-menu-item" onclick="location.href='caidat.php#account'">
                <i class="fas fa-key"></i>
                <span>Đổi mật khẩu</span>
            </div>
            <div class="user-menu-item" onclick="location.href='caidat.php'">
                <i class="fas fa-cog"></i>
                <span>Cấu hình hệ thống</span>
            </div>
            <div class="user-menu-divider"></div>
            <div class="user-menu-item logout-item" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </div>
        </div>
    `;
    
    profileBtn.style.position = 'relative';
    profileBtn.appendChild(dropdown);
    addDropdownStyles();
    
    // Thêm ripple effect khi click
    const ripple = document.createElement('span');
    ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        width: 10px;
        height: 10px;
        animation: ripple 0.6s ease-out;
        pointer-events: none;
    `;
    
    // Tạo keyframe animation cho ripple
    if(!document.getElementById('rippleAnimation')) {
        const style = document.createElement('style');
        style.id = 'rippleAnimation';
        style.textContent = `
            @keyframes ripple {
                to {
                    width: 100px;
                    height: 100px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    profileBtn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
    
    // Đóng menu khi click bên ngoài
    setTimeout(() => {
        document.addEventListener('click', function closeMenu(e) {
            if(!profileBtn.contains(e.target)) {
                dropdown.style.animation = 'slideUp 0.3s ease-out reverse';
                profileBtn.classList.remove('menu-open');
                setTimeout(() => {
                    dropdown.remove();
                }, 300);
                document.removeEventListener('click', closeMenu);
            }
        });
    }, 100);
}

function closeAllDropdowns() {
    // Xóa class menu-open từ profile button
    const profileBtn = document.querySelector('.admin-profile-enhanced');
    if(profileBtn) {
        profileBtn.classList.remove('menu-open');
    }
    
    // Xóa tất cả các dropdown với animation
    document.querySelectorAll('.dropdown-menu, .user-menu').forEach(el => {
        el.style.animation = 'slideUp 0.2s ease-out reverse';
        setTimeout(() => el.remove(), 200);
    });
}

function markNotificationRead(notifId, link) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: notifId })
    }).then(() => {
        // Update badge count
        updateNotificationBadge();
        
        // Redirect if has link
        if(link && link !== '#') {
            window.location.href = link;
        }
    });
}

function markAllNotificationsRead(e) {
    e.preventDefault();
    e.stopPropagation();
    
    fetch('mark_all_notifications_read.php', {
        method: 'POST'
    }).then(response => response.json())
    .then(data => {
        if(data.success) {
            // Remove unread styling
            document.querySelectorAll('.dropdown-item.unread').forEach(item => {
                item.classList.remove('unread');
                const dot = item.querySelector('.notif-unread-dot');
                if(dot) dot.remove();
            });
            
            // Update badge
            updateNotificationBadge();
            
            // Update header
            const header = document.querySelector('.dropdown-header');
            if(header) {
                header.querySelector('.notif-count')?.remove();
                header.querySelector('a')?.remove();
            }
        }
    });
}

function updateNotificationBadge() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.topbar-icon[title="Thông báo"] .badge');
            if(data.count > 0) {
                if(badge) {
                    badge.textContent = data.count;
                } else {
                    const icon = document.querySelector('.topbar-icon[title="Thông báo"]');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge';
                    newBadge.textContent = data.count;
                    icon.appendChild(newBadge);
                }
            } else {
                badge?.remove();
            }
        });
}

function addDropdownStyles() {
    if(document.getElementById('dropdownStyles')) return;
    
    const style = document.createElement('style');
    style.id = 'dropdownStyles';
    style.textContent = `
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: -8px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            min-width: 350px;
            max-width: 380px;
            z-index: 10000;
            animation: slideDown 0.2s ease-out;
        }
        @media(max-width: 480px) {
            .dropdown-menu {
                position: fixed;
                top: 70px;
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }
        }
        .dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }
        .dropdown-header > div {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dropdown-body {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        @media(max-width: 768px) {
            .dropdown-body {
                max-height: calc(100vh - 200px);
            }
        }
        .dropdown-item {
            padding: 12px 16px;
            display: flex;
            gap: 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid #f3f4f6;
            position: relative;
            border-radius: 8px;
            margin: 4px 8px;
            width: calc(100% - 16px);
        }
        .dropdown-item:last-child {
            border-bottom: none;
        }
        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: 4px 0 0 4px;
            opacity: 0;
            transform: scaleY(0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dropdown-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(129, 140, 248, 0.05));
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .dropdown-item:hover::before {
            opacity: 1;
            transform: scaleY(1);
        }
        .dropdown-item:hover::after {
            opacity: 1;
        }
        .dropdown-item:hover {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transform: translateX(2px);
            border-color: transparent;
        }
        .dropdown-item:hover .notif-icon,
        .dropdown-item:hover .msg-avatar {
            transform: scale(1.05) rotate(2deg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .dropdown-item.unread {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-left: 3px solid #3b82f6;
        }
        .dropdown-item.unread:hover {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        .dropdown-item:active {
            transform: translateX(2px) scale(0.98);
        }
        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .notif-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }
        .dropdown-item:hover .notif-icon::before {
            transform: scale(1);
        }
        .notif-content {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }
        .notif-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: color 0.2s ease;
        }
        .dropdown-item:hover .notif-title {
            color: #6366f1;
        }
        .notif-message {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 4px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.2s ease;
        }
        .dropdown-item:hover .notif-message {
            color: #4b5563;
        }
        .notif-time {
            font-size: 0.75rem;
            color: #9ca3af;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .notif-time::before {
            content: '•';
            color: #6366f1;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .dropdown-item:hover .notif-time::before {
            opacity: 1;
        }
        .dropdown-item:hover .notif-time {
            color: #6366f1;
        }
        .notif-unread-dot {
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            flex-shrink: 0;
            margin-left: 8px;
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0);
            }
        }
        .notif-count {
            display: inline-block;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 8px;
            font-weight: 700;
        }
        .dropdown-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
        }
        .dropdown-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        .dropdown-empty p {
            font-size: 0.9rem;
        }
        .msg-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .msg-avatar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }
        .dropdown-item:hover .msg-avatar::before {
            transform: scale(1);
        }
        .msg-content {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }
        .msg-sender {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: color 0.2s ease;
        }
        .dropdown-item:hover .msg-sender {
            color: #6366f1;
        }
        .msg-preview {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.4;
            transition: color 0.2s ease;
        }
        .dropdown-item:hover .msg-preview {
            color: #4b5563;
        }
        .msg-time {
            font-size: 0.75rem;
            color: #9ca3af;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .msg-time::before {
            content: '•';
            color: #6366f1;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .dropdown-item:hover .msg-time::before {
            opacity: 1;
        }
        .dropdown-item:hover .msg-time {
            color: #6366f1;
        }
        .msg-unread-dot {
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            flex-shrink: 0;
            margin-left: 8px;
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            animation: pulse-dot 2s infinite;
        }
        .dropdown-item.unread .msg-sender {
            color: #1f2937;
            font-weight: 700;
        }
        .dropdown-item.unread .msg-preview {
            color: #374151;
            font-weight: 500;
        }
        .dropdown-footer {
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            position: sticky;
            bottom: 0;
            background: white;
        }
        .dropdown-footer a {
            color: #6366f1;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .dropdown-footer a:hover {
            color: #4f46e5;
        }
        .user-menu {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
            min-width: 280px;
            z-index: 10001;
            animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .user-menu::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            width: 16px;
            height: 16px;
            background: white;
            transform: rotate(45deg);
            box-shadow: -2px -2px 4px rgba(0,0,0,0.05);
        }
        .user-menu-header {
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }
        .user-menu-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.05);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .user-menu-header:hover::after {
            opacity: 1;
        }
        .user-menu-avatar {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            border: 3px solid rgba(255,255,255,0.5);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .user-menu-header:hover .user-menu-avatar {
            transform: scale(1.05) rotate(5deg);
        }
        .user-menu-info {
            flex: 1;
            min-width: 0;
        }
        .user-menu-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-menu-email {
            font-size: 0.8rem;
            opacity: 0.9;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-menu-body {
            padding: 8px;
        }
        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            color: #374151;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        .user-menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            transform: scaleY(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .user-menu-item:hover::before {
            transform: scaleY(1);
        }
        .user-menu-item:hover {
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(129,140,248,0.08));
            color: #6366f1;
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(99,102,241,0.15);
        }
        .user-menu-item:active {
            transform: translateX(4px) scale(0.98);
        }
        .user-menu-item i {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #6366f1;
            transition: all 0.3s ease;
        }
        .user-menu-item:hover i {
            transform: scale(1.15) rotate(5deg);
        }
        .user-menu-item.logout-item {
            color: #dc2626;
            margin-top: 4px;
        }
        .user-menu-item.logout-item i {
            color: #dc2626;
        }
        .user-menu-item.logout-item:hover {
            background: linear-gradient(135deg, rgba(220,38,38,0.1), rgba(239,68,68,0.1));
            color: #dc2626;
            box-shadow: 0 2px 8px rgba(220,38,38,0.2);
        }
        .user-menu-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 8px 12px;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
}

// Close modal and dropdowns when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('quickMenu');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Close dropdowns when clicking anywhere
document.addEventListener('click', function(e) {
    if(!e.target.closest('.topbar-icon') && !e.target.closest('.admin-profile')) {
        closeAllDropdowns();
    }
});

// Page Loader
window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if(loader) {
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 300);
    }
});

// Handle hamburger menu for mobile
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const sidebar = document.querySelector('.sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    if(hamburger && sidebar) {
        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-show');
            mobileOverlay.classList.toggle('active');
        });
        
        // Đóng sidebar khi click vào overlay
        if(mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-show');
                mobileOverlay.classList.remove('active');
            });
        }
        
        // Đóng sidebar khi click vào menu item trên mobile
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if(window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-show');
                    mobileOverlay.classList.remove('active');
                }
            });
        });
    }
    
    // Smooth number counting animation
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(num => {
        const target = parseInt(num.textContent.replace(/,/g, ''));
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if(current >= target) {
                num.textContent = target.toLocaleString('vi-VN');
                clearInterval(timer);
            } else {
                num.textContent = Math.floor(current).toLocaleString('vi-VN');
            }
        }, 20);
    });
    
    // Global search functionality
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if(query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Đóng search results khi click ra ngoài
        document.addEventListener('click', function(e) {
            if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    async function performSearch(query) {
        searchResults.innerHTML = '<div style="padding:12px; text-align:center; color:#999;"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
        searchResults.style.display = 'block';
        
        try {
            const response = await fetch('search.php?q=' + encodeURIComponent(query));
            const data = await response.json();
            
            if(data.length === 0) {
                searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:#999;">Không tìm thấy kết quả</div>';
                return;
            }
            
            let html = '';
            data.forEach(item => {
                const icon = {
                    'van_hoa': 'fa-newspaper',
                    'bai_viet': 'fa-newspaper',
                    'chua': 'fa-place-of-worship',
                    'le_hoi': 'fa-calendar-check',
                    'truyen': 'fa-book',
                    'bai_hoc': 'fa-book-open'
                }[item.type] || 'fa-file';
                
                html += `
                    <div class="search-result-item" onclick="location.href='${item.url}'">
                        <i class="fas ${icon}"></i>
                        <div>
                            <div class="search-result-title">${item.title}</div>
                            <div class="search-result-type">${item.type_label}</div>
                        </div>
                    </div>
                `;
            });
            
            searchResults.innerHTML = html;
        } catch(error) {
            searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:#ef4444;">Lỗi khi tìm kiếm</div>';
        }
    }
});

// Add ripple effect to cards
document.querySelectorAll('.stat-card, .menu-item, .quick-action-btn').forEach(elem => {
    elem.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });
});

// Add CSS animation for ripple
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        from {
            transform: scale(0);
            opacity: 1;
        }
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Modal functions
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = '';
}

function openRegisterModal() {
    document.getElementById('registerModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
    document.body.style.overflow = '';
}

function switchToRegister() {
    closeLoginModal();
    openRegisterModal();
}

function switchToLogin() {
    closeRegisterModal();
    openLoginModal();
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if(input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Handle login form submit
async function handleLogin(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('dangnhap.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if(result.includes('success') || response.redirected) {
            window.location.href = 'index.php';
        } else {
            alert('Đăng nhập thất bại! Vui lòng kiểm tra lại thông tin.');
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        }
    } catch(error) {
        alert('Có lỗi xảy ra! Vui lòng thử lại.');
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
    }
}

// Handle register form submit
async function handleRegister(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    
    // Validate password match
    const password = form.querySelector('[name="password"]').value;
    const confirmPassword = form.querySelector('[name="confirm_password"]').value;
    
    if(password !== confirmPassword) {
        alert('Mật khẩu xác nhận không khớp!');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('dangky.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if(result.includes('success')) {
            alert('Đăng ký thành công! Vui lòng đăng nhập.');
            closeRegisterModal();
            openLoginModal();
        } else {
            alert('Đăng ký thất bại! Vui lòng thử lại.');
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        }
    } catch(error) {
        alert('Có lỗi xảy ra! Vui lòng thử lại.');
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    
    if(event.target === loginModal) {
        closeLoginModal();
    }
    if(event.target === registerModal) {
        closeRegisterModal();
    }
}
</script>

</body>
</html>
