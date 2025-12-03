<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/BaiHoc.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();
$baiHocModel = new BaiHoc();

// Xử lý các hành động CRUD với PRG Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add':
                $data = [
                    'ma_danh_muc' => $_POST['ma_danh_muc'] ?? null,
                    'tieu_de' => $_POST['tieu_de'],
                    'mo_ta' => $_POST['mo_ta'] ?? '',
                    'noi_dung' => $_POST['noi_dung'] ?? '',
                    'cap_do' => $_POST['cap_do'] ?? 'co_ban',
                    'thu_tu' => $_POST['thu_tu'] ?? 0,
                    'thoi_luong' => $_POST['thoi_luong'] ?? 30,
                    'trang_thai' => $_POST['trang_thai'] ?? 'xuat_ban',
                ];
                if($baiHocModel->create($data)) {
                    $_SESSION['flash_message'] = 'Thêm bài học thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi thêm bài học!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: hoctiengkhmer.php');
                exit;
                
            case 'edit':
                $data = [
                    'ma_danh_muc' => $_POST['ma_danh_muc'] ?? null,
                    'tieu_de' => $_POST['tieu_de'],
                    'mo_ta' => $_POST['mo_ta'] ?? '',
                    'noi_dung' => $_POST['noi_dung'] ?? '',
                    'cap_do' => $_POST['cap_do'] ?? 'co_ban',
                    'thu_tu' => $_POST['thu_tu'] ?? 0,
                    'thoi_luong' => $_POST['thoi_luong'] ?? 30,
                    'trang_thai' => $_POST['trang_thai'] ?? 'xuat_ban',
                ];
                if($baiHocModel->update($_POST['ma_bai_hoc'], $data)) {
                    $_SESSION['flash_message'] = 'Cập nhật bài học thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi cập nhật!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: hoctiengkhmer.php');
                exit;
                
            case 'delete':
                if($baiHocModel->delete($_POST['ma_bai_hoc'])) {
                    $_SESSION['flash_message'] = 'Xóa bài học thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: hoctiengkhmer.php');
                exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: hoctiengkhmer.php');
        exit;
    }
}

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách bài học
$lessons = $baiHocModel->getAll(100);
if(!is_array($lessons)) {
    $lessons = [];
}

// Format ngày tạo
foreach($lessons as &$lesson) {
    if(isset($lesson['ngay_tao'])) {
        $lesson['ngay_tao_fmt'] = date('d/m/Y H:i', strtotime($lesson['ngay_tao']));
    }
}

// Lấy danh mục
$categories = $baiHocModel->getCategories();
if(!is_array($categories)) {
    $categories = [];
}

// Thống kê
$total_lessons = $baiHocModel->count();
$basic_lessons = $baiHocModel->count('co_ban');
$intermediate_lessons = $baiHocModel->count('trung_cap');
$advanced_lessons = $baiHocModel->count('nang_cao');

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
<meta name="description" content="Quản lý bài học tiếng Khmer">
<meta name="theme-color" content="#6366f1">
<title>Quản lý Bài học tiếng Khmer</title>
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
                <div class="menu-item active" onclick="location.href='hoctiengkhmer.php'">
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
                    <input type="text" id="searchInput" placeholder="Tìm kiếm bài học..." autocomplete="off">
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
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h1>Quản lý Bài học tiếng Khmer</h1>
                            <p>Tạo và quản lý các bài học tiếng Khmer cho người học</p>
                        </div>
                    </div>
                    <button class="btn-add-new" onclick="openAddModal()">
                        <i class="fas fa-plus-circle"></i>
                        Thêm bài học mới
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #f093fb;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Tổng bài học</span>
                            <div class="stat-number"><?php echo number_format($total_lessons); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(240, 147, 251, 0.1); color: #f093fb;">
                            <i class="fas fa-database"></i> Tất cả bài học
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #10b981;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Cơ bản</span>
                            <div class="stat-number"><?php echo number_format($basic_lessons); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-seedling"></i> Dễ học
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #f59e0b;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Trung cấp</span>
                            <div class="stat-number"><?php echo number_format($intermediate_lessons); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="fas fa-chart-line"></i> Trung bình
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ef4444;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Nâng cao</span>
                            <div class="stat-number"><?php echo number_format($advanced_lessons); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fas fa-crown"></i> Chuyên sâu
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        Danh sách bài học
                    </h3>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="filter-item">
                        <select id="filterLevel" onchange="filterLessons()">
                            <option value="">Tất cả cấp độ</option>
                            <option value="co_ban">Cơ bản</option>
                            <option value="trung_cap">Trung cấp</option>
                            <option value="nang_cao">Nâng cao</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select id="filterStatus" onchange="filterLessons()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="hoat_dong">Hoạt động</option>
                            <option value="tam_ngung">Tạm ngừng</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <input type="text" id="filterSearch" placeholder="Tìm kiếm bài học..." onkeyup="filterLessons()">
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Cấp độ</th>
                                <th>Thời lượng</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="lessonsTableBody">
                            <?php if(empty($lessons)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-inbox" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có bài học nào</strong>
                                    <p style="margin-top:8px;">Hãy thêm bài học đầu tiên!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($lessons as $index => $lesson): ?>
                                <tr data-level="<?php echo $lesson['cap_do']; ?>" data-status="<?php echo $lesson['trang_thai']; ?>">
                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lesson['tieu_de']); ?></strong>
                                        <?php if(!empty($lesson['mo_ta'])): ?>
                                        <br><small style="color:var(--gray);"><?php echo htmlspecialchars(mb_substr($lesson['mo_ta'], 0, 50)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($lesson['ten_danh_muc'] ?? 'Chưa phân loại'); ?></td>
                                    <td>
                                        <?php 
                                        $level_map = [
                                            'co_ban' => ['text' => 'Cơ bản', 'class' => 'basic'],
                                            'trung_cap' => ['text' => 'Trung cấp', 'class' => 'intermediate'],
                                            'nang_cao' => ['text' => 'Nâng cao', 'class' => 'advanced']
                                        ];
                                        $level = $level_map[$lesson['cap_do']] ?? ['text' => 'Cơ bản', 'class' => 'basic'];
                                        ?>
                                        <span class="level-badge <?php echo $level['class']; ?>">
                                            <?php echo $level['text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-clock"></i>
                                        <?php echo $lesson['thoi_luong'] ?? 30; ?> phút
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $lesson['trang_thai'] === 'xuat_ban' ? 'active' : 'inactive'; ?>">
                                            <?php echo $lesson['trang_thai'] === 'xuat_ban' ? 'Xuất bản' : ($lesson['trang_thai'] === 'nhap' ? 'Nháp' : 'Ẩn'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $lesson['ngay_tao_fmt']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($lesson)); ?>)" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteLesson(<?php echo $lesson['ma_bai_hoc']; ?>)" title="Xóa">
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
            <h3>Thêm bài học mới</h3>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Tiêu đề bài học <span style="color:red;">*</span></label>
                    <input type="text" name="tieu_de" required placeholder="VD: Bảng chữ cái Khmer">
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cấp độ <span style="color:red;">*</span></label>
                    <select name="cap_do" required>
                        <option value="co_ban">Cơ bản</option>
                        <option value="trung_cap">Trung cấp</option>
                        <option value="nang_cao">Nâng cao</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Thứ tự hiển thị</label>
                    <input type="number" name="thu_tu" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>Thời lượng học (phút)</label>
                    <input type="number" name="thoi_luong" value="30" min="5" max="180">
                </div>
                <div class="form-group full-width">
                    <label>Mô tả ngắn</label>
                    <textarea name="mo_ta" rows="2" placeholder="Mô tả ngắn gọn về bài học..."></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Nội dung bài học</label>
                    <textarea name="noi_dung" placeholder="Nhập nội dung chi tiết của bài học..."></textarea>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai">
                        <option value="xuat_ban">Xuất bản</option>
                        <option value="nhap">Nháp</option>
                        <option value="an">Ẩn</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu bài học
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Chỉnh sửa bài học</h3>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ma_bai_hoc" id="edit_ma_bai_hoc">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tiêu đề bài học <span style="color:red;">*</span></label>
                    <input type="text" name="tieu_de" id="edit_tieu_de" required>
                </div>
                <div class="form-group">
                    <label>Tiêu đề tiếng Khmer</label>
                    <input type="text" name="tieu_de_khmer" id="edit_tieu_de_khmer">
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc" id="edit_ma_danh_muc">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cấp độ <span style="color:red;">*</span></label>
                    <select name="cap_do" id="edit_cap_do" required>
                        <option value="co_ban">Cơ bản</option>
                        <option value="trung_cap">Trung cấp</option>
                        <option value="nang_cao">Nâng cao</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Thứ tự hiển thị</label>
                    <input type="number" name="thu_tu" id="edit_thu_tu" min="0">
                </div>
                <div class="form-group">
                    <label>Thời lượng học (phút)</label>
                    <input type="number" name="thoi_luong_hoc" id="edit_thoi_luong_hoc" min="5" max="180">
                </div>
                <div class="form-group full-width">
                    <label>Nội dung bài học</label>
                    <textarea name="noi_dung" id="edit_noi_dung"></textarea>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="edit_trang_thai">
                        <option value="hoat_dong">Hoạt động</option>
                        <option value="tam_ngung">Tạm ngừng</option>
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

// Modal functions
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function openEditModal(lesson) {
    document.getElementById('edit_ma_bai_hoc').value = lesson.ma_bai_hoc;
    document.getElementById('edit_tieu_de').value = lesson.tieu_de;
    document.getElementById('edit_tieu_de_khmer').value = lesson.tieu_de_khmer || '';
    document.getElementById('edit_ma_danh_muc').value = lesson.ma_danh_muc || '';
    document.getElementById('edit_cap_do').value = lesson.cap_do;
    document.getElementById('edit_thu_tu').value = lesson.thu_tu || 0;
    document.getElementById('edit_thoi_luong_hoc').value = lesson.thoi_luong_hoc || 30;
    document.getElementById('edit_noi_dung').value = lesson.noi_dung || '';
    document.getElementById('edit_trang_thai').value = lesson.trang_thai;
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Delete lesson
function deleteLesson(id) {
    if(confirm('Bạn có chắc chắn muốn xóa bài học này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ma_bai_hoc" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Filter lessons
function filterLessons() {
    const level = document.getElementById('filterLevel').value.toLowerCase();
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#lessonsTableBody tr');
    
    rows.forEach(row => {
        if(row.cells.length === 1) return; // Skip empty row
        
        const rowLevel = row.dataset.level || '';
        const rowStatus = row.dataset.status || '';
        const rowText = row.textContent.toLowerCase();
        
        const matchLevel = !level || rowLevel === level;
        const matchStatus = !status || rowStatus === status;
        const matchSearch = !search || rowText.includes(search);
        
        row.style.display = (matchLevel && matchStatus && matchSearch) ? '' : 'none';
    });
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#lessonsTableBody tr');
    
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

</body>
</html>
