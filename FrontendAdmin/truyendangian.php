<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/TruyenDanGian.php';
require_once 'includes/upload.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

$db = Database::getInstance();
$truyenModel = new TruyenDanGian();

// Xử lý các hành động CRUD với PRG Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add':
                // Xử lý upload ảnh
                $anh_dai_dien = '';
                if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('truyendangian');
                    $anh_dai_dien = $uploader->upload($_FILES['anh_dai_dien']);
                    if (!$anh_dai_dien) {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                $data = [
                    'tieu_de' => $_POST['tieu_de'],
                    'tieu_de_khmer' => $_POST['tieu_de_khmer'] ?? '',
                    'tom_tat' => $_POST['tom_tat'] ?? '',
                    'noi_dung' => $_POST['noi_dung'] ?? '',
                    'anh_dai_dien' => $anh_dai_dien,
                    'ma_danh_muc' => !empty($_POST['the_loai']) ? $_POST['the_loai'] : null,
                    'nguon' => $_POST['nguon_goc'] ?? '',
                    'tac_gia' => $_POST['tac_gia'] ?? '',
                    'trang_thai' => $_POST['trang_thai'] ?? 'hien_thi',
                ];
                if($truyenModel->create($data)) {
                    $_SESSION['flash_message'] = 'Thêm truyện dân gian thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi thêm truyện!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: truyendangian.php');
                exit;
                
            case 'edit':
                // Kiểm tra ID truyện
                if (empty($_POST['ma_truyen'])) {
                    throw new Exception('Thiếu ID truyện');
                }
                
                // Lấy thông tin truyện hiện tại
                $currentTruyen = $truyenModel->getById($_POST['ma_truyen']);
                if (!$currentTruyen) {
                    throw new Exception('Không tìm thấy truyện ID: ' . $_POST['ma_truyen']);
                }
                
                $anh_dai_dien = $currentTruyen['anh_dai_dien'] ?? '';
                
                // Xử lý upload ảnh mới
                if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('truyendangian');
                    $newImagePath = $uploader->upload($_FILES['anh_dai_dien']);
                    if ($newImagePath) {
                        // Xóa ảnh cũ nếu có
                        if ($anh_dai_dien && file_exists(__DIR__ . '/../' . $anh_dai_dien)) {
                            @unlink(__DIR__ . '/../' . $anh_dai_dien);
                        }
                        $anh_dai_dien = $newImagePath;
                    } else {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                // Validate dữ liệu
                if (empty($_POST['tieu_de'])) {
                    throw new Exception('Tiêu đề không được để trống');
                }
                if (empty($_POST['noi_dung'])) {
                    throw new Exception('Nội dung không được để trống');
                }
                
                $data = [
                    'tieu_de' => trim($_POST['tieu_de']),
                    'tieu_de_khmer' => trim($_POST['tieu_de_khmer'] ?? ''),
                    'tom_tat' => trim($_POST['tom_tat'] ?? ''),
                    'noi_dung' => trim($_POST['noi_dung']),
                    'anh_dai_dien' => $anh_dai_dien,
                    'ma_danh_muc' => !empty($_POST['the_loai']) ? $_POST['the_loai'] : null,
                    'nguon' => trim($_POST['nguon_goc'] ?? ''),
                    'tac_gia' => trim($_POST['tac_gia'] ?? ''),
                    'trang_thai' => $_POST['trang_thai'] ?? 'hien_thi',
                ];
                
                $result = $truyenModel->update($_POST['ma_truyen'], $data);
                
                if($result) {
                    $_SESSION['flash_message'] = 'Cập nhật truyện thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    throw new Exception('Update trả về false. Kiểm tra BaseModel::update()');
                }
                header('Location: truyendangian.php');
                exit;
                
            case 'delete':
                // Lấy thông tin truyện để xóa ảnh
                $truyen = $truyenModel->getById($_POST['ma_truyen']);
                if ($truyen && $truyen['anh_dai_dien'] && file_exists(__DIR__ . '/../' . $truyen['anh_dai_dien'])) {
                    @unlink(__DIR__ . '/../' . $truyen['anh_dai_dien']);
                }
                
                if($truyenModel->delete($_POST['ma_truyen'])) {
                    $_SESSION['flash_message'] = 'Xóa truyện thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: truyendangian.php');
                exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: truyendangian.php');
        exit;
    }
}

// Lấy thông báo từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách truyện
$stories = $truyenModel->getAll(100);
if(!is_array($stories)) {
    $stories = [];
}

// Lấy danh mục truyện
$categories = $db->query("SELECT * FROM danh_muc WHERE loai = 'truyen' AND trang_thai = 'hien_thi' ORDER BY thu_tu, ten_danh_muc");
if(!is_array($categories)) {
    $categories = [];
}

// Format ngày tạo
foreach($stories as &$story) {
    if(isset($story['ngay_tao'])) {
        $story['ngay_tao_fmt'] = date('d/m/Y H:i', strtotime($story['ngay_tao']));
    }
}

// Thống kê
$total_stories = $truyenModel->count();
$visible_stories = count(array_filter($stories, fn($s) => ($s['trang_thai'] ?? '') === 'hien_thi'));
$hidden_stories = count(array_filter($stories, fn($s) => ($s['trang_thai'] ?? '') === 'an'));
$total_views = array_sum(array_column($stories, 'luot_xem'));

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
<meta name="description" content="Quản lý truyện dân gian Khmer">
<meta name="theme-color" content="#6366f1">
<title>Quản lý Truyện dân gian Khmer</title>
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
.btn-quiz-link {
    background:var(--white);
    color:#8b5cf6;
    box-shadow:0 2px 8px rgba(139,92,246,0.15);
}
.btn-quiz-link:hover {
    background:linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    color:var(--white);
    box-shadow:0 8px 24px rgba(139,92,246,0.3);
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
    box-shadow:0 20px 60px rgba(0,0,0,0.3);
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
    color:var(--dark);
    display:flex;
    align-items:center;
    gap:12px;
}
.modal-header h3 i {
    width:40px;
    height:40px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--white);
    font-size:1.2rem;
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
    display:flex;
    align-items:center;
    gap:8px;
}
.form-group label i {
    font-size:1rem;
    color:var(--primary);
}
.form-group label .required {
    color:var(--danger);
    margin-left:2px;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding:12px 16px;
    border:2px solid var(--gray-light);
    border-radius:12px;
    font-size:0.95rem;
    transition:all 0.3s ease;
    background:var(--white);
}
.form-group input::placeholder,
.form-group textarea::placeholder {
    color:#a0aec0;
    font-weight:400;
}
.form-group select {
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236366f1' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 16px center;
    padding-right:40px;
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
    padding:14px 36px;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:var(--white);
    border:none;
    border-radius:12px;
    font-weight:700;
    font-size:1rem;
    cursor:pointer;
    transition:all 0.3s ease;
    display:flex;
    align-items:center;
    gap:8px;
}
.btn-submit:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(102,126,234,0.4);
}
.btn-submit i {
    font-size:1.1rem;
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

/* HTML Editor Toolbar */
.html-editor-toolbar {
    background:var(--gray-light);
    padding:10px 14px;
    border-radius:12px 12px 0 0;
    border:2px solid var(--gray-light);
    border-bottom:none;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}
.editor-btn {
    width:40px;
    height:40px;
    border:none;
    background:var(--white);
    border-radius:8px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1rem;
    font-weight:700;
    color:var(--dark);
    transition:all 0.2s ease;
    box-shadow:0 1px 3px rgba(0,0,0,0.1);
}
.editor-btn:hover {
    background:var(--primary);
    color:var(--white);
    transform:translateY(-1px);
    box-shadow:0 4px 8px rgba(99,102,241,0.3);
}
.editor-btn:active {
    transform:translateY(0);
}
.editor-btn i {
    font-size:0.95rem;
}
.html-editor-toolbar::before {
    content:'';
    display:inline-block;
    width:2px;
    height:24px;
    background:var(--gray);
    opacity:0.3;
    margin:0 4px;
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
                <div class="menu-item" onclick="location.href='hoctiengkhmer.php'">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Học tiếng Khmer</span>
                </div>
                <div class="menu-item active" onclick="location.href='truyendangian.php'">
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
                    <input type="text" id="searchInput" placeholder="Tìm kiếm truyện dân gian..." autocomplete="off">
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
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div>
                            <h1>Quản lý Truyện dân gian Khmer</h1>
                            <p>Lưu giữ và chia sẻ kho tàng truyện dân gian Khmer Nam Bộ</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn-add-new" onclick="openAddModal()">
                            <i class="fas fa-plus-circle"></i>
                            Thêm truyện mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #fa709a;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Tổng truyện</span>
                            <div class="stat-number"><?php echo number_format($total_stories); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(250, 112, 154, 0.1); color: #fa709a;">
                            <i class="fas fa-database"></i> Tất cả truyện
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #10b981;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Đang hiển thị</span>
                            <div class="stat-number"><?php echo number_format($visible_stories); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-check-circle"></i> Công khai
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #6b7280;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Đã ẩn</span>
                            <div class="stat-number"><?php echo number_format($hidden_stories); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                            <i class="fas fa-eye-slash"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(107, 114, 128, 0.1); color: #6b7280;">
                            <i class="fas fa-lock"></i> Riêng tư
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #3b82f6;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Lượt xem</span>
                            <div class="stat-number"><?php echo number_format($total_views); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="fas fa-users"></i> Độc giả
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        Danh sách truyện dân gian
                    </h3>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="filter-item">
                        <select id="filterCategory" onchange="filterStories()">
                            <option value="">Tất cả thể loại</option>
                            <option value="truyen_co_tich">Truyện cổ tích</option>
                            <option value="truyen_truyen_thuyet">Truyền thuyết</option>
                            <option value="truyen_dan_gian">Truyện dân gian</option>
                            <option value="truyen_than_thoai">Thần thoại</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select id="filterStatus" onchange="filterStories()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="hien_thi">Hiển thị</option>
                            <option value="an">Ẩn</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <input type="text" id="filterSearch" placeholder="Tìm kiếm truyện..." onkeyup="filterStories()">
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Hình Ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Tiêu đề Khmer</th>
                                <th>Thể loại</th>
                                <th>Lượt xem</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="storiesTableBody">
                            <?php if(empty($stories)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-inbox" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có truyện nào</strong>
                                    <p style="margin-top:8px;">Hãy thêm truyện dân gian đầu tiên!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($stories as $index => $story): ?>
                                <tr data-category="<?php echo $story['ma_danh_muc'] ?? ''; ?>" data-status="<?php echo $story['trang_thai']; ?>">
                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                    <td>
                                        <?php if(!empty($story['anh_dai_dien'])): ?>
                                            <img src="../<?php echo htmlspecialchars($story['anh_dai_dien']); ?>" class="article-image" alt="Story">
                                        <?php else: ?>
                                            <div class="article-image" style="background:var(--gradient-story); display:flex; align-items:center; justify-content:center; color:white; font-weight:800;">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($story['tieu_de']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($story['tieu_de_khmer'] ?? '-'); ?></td>
                                    <td>
                                        <?php 
                                        // Lấy tên danh mục từ database
                                        $categoryName = 'Chưa phân loại';
                                        $categoryClass = 'folk';
                                        
                                        if (!empty($story['ma_danh_muc'])) {
                                            $catResult = $db->querySingle("SELECT ten_danh_muc FROM danh_muc WHERE ma_danh_muc = ?", [$story['ma_danh_muc']]);
                                            if ($catResult) {
                                                $categoryName = $catResult['ten_danh_muc'];
                                                // Map class dựa trên tên
                                                if (strpos($categoryName, 'Cổ tích') !== false) $categoryClass = 'fairy';
                                                elseif (strpos($categoryName, 'Truyền thuyết') !== false) $categoryClass = 'legend';
                                                elseif (strpos($categoryName, 'Thần thoại') !== false) $categoryClass = 'myth';
                                            }
                                        }
                                        ?>
                                        <span class="category-badge <?php echo $categoryClass; ?>">
                                            <?php echo htmlspecialchars($categoryName); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-eye"></i>
                                        <?php echo number_format($story['luot_xem'] ?? 0); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $story['trang_thai'] === 'hien_thi' ? 'visible' : 'hidden'; ?>">
                                            <?php echo $story['trang_thai'] === 'hien_thi' ? 'Hiển thị' : 'Ẩn'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $story['ngay_tao_fmt'] ?? '-'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" 
                                                    data-id="<?php echo $story['ma_truyen']; ?>"
                                                    onclick="loadAndEditStory(<?php echo $story['ma_truyen']; ?>)" 
                                                    title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteStory(<?php echo $story['ma_truyen']; ?>)" title="Xóa">
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
            <h3><i class="fas fa-book-reader"></i> Thêm truyện dân gian mới</h3>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fas fa-book"></i> Tiêu đề truyện <span class="required">*</span></label>
                    <input type="text" name="tieu_de" required placeholder="VD: Tấm Cám">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-language"></i> Tiêu đề tiếng Khmer</label>
                    <input type="text" name="tieu_de_khmer" placeholder="រឿងតាមកាម">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Thể loại <span class="required">*</span></label>
                    <select name="the_loai" required>
                        <option value="">-- Chọn thể loại --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-edit"></i> Tác giả</label>
                    <input type="text" name="tac_gia" placeholder="VD: Dân gian Khmer">
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-info-circle"></i> Nguồn gốc</label>
                    <input type="text" name="nguon_goc" placeholder="VD: Truyền miệng từ các già làng Khmer">
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-align-left"></i> Tóm tắt</label>
                    <textarea name="tom_tat" placeholder="Tóm tắt ngắn gọn nội dung truyện..." style="min-height:100px;"></textarea>
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-file-code" style="color: var(--primary); margin-right: 6px;"></i>Nội dung truyện <small style="color: var(--gray); font-weight: 400;">(hỗ trợ HTML)</small> <span style="color:red;">*</span></label>
                    <div class="html-editor-toolbar" style="background: var(--gray-light); padding: 8px 12px; border-radius: 12px 12px 0 0; border: 2px solid var(--gray-light); border-bottom: none; display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'b')" title="In đậm"><i class="fas fa-bold"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'i')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'u')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'h2')" title="Tiêu đề 2"><i class="fas fa-heading"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'h3')" title="Tiêu đề 3"><i class="fas fa-heading" style="font-size: 0.9em;"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'p')" title="Đoạn văn"><i class="fas fa-paragraph"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'ul')" title="Danh sách"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'ol')" title="Danh sách số"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('add_noi_dung', 'a')" title="Link"><i class="fas fa-link"></i></button>
                        <button type="button" class="editor-btn" onclick="insertImage('add_noi_dung')" title="Chèn ảnh"><i class="fas fa-image"></i></button>
                    </div>
                    <textarea name="noi_dung" id="add_noi_dung" required placeholder="Nhập nội dung đầy đủ của truyện...&#10;&#10;Ví dụ:&#10;<p>Ngày xửa ngày xưa...</p>&#10;<p>Có một...</p>" rows="10" style="border-radius: 0 0 12px 12px; font-family: 'Consolas', monospace; font-size: 0.9rem;"></textarea>
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-image"></i> Ảnh đại diện</label>
                    <input type="file" name="anh_dai_dien" id="add_anh_dai_dien" accept="image/*" onchange="previewImage(this, 'add_preview')">
                    <img id="add_preview" style="display:none; margin-top:12px; max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Trạng thái</label>
                    <select name="trang_thai">
                        <option value="hien_thi">✅ Hiển thị</option>
                        <option value="an">🔒 Ẩn</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu truyện
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Chỉnh sửa truyện</h3>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="editForm" onsubmit="return validateEditForm()">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ma_truyen" id="edit_ma_truyen">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tiêu đề truyện <span style="color:red;">*</span></label>
                    <input type="text" name="tieu_de" id="edit_tieu_de" required>
                </div>
                <div class="form-group">
                    <label>Tiêu đề tiếng Khmer</label>
                    <input type="text" name="tieu_de_khmer" id="edit_tieu_de_khmer">
                </div>
                <div class="form-group">
                    <label>Thể loại <span style="color:red;">*</span></label>
                    <select name="the_loai" id="edit_the_loai" required>
                        <option value="">-- Chọn thể loại --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tác giả</label>
                    <input type="text" name="tac_gia" id="edit_tac_gia">
                </div>
                <div class="form-group full-width">
                    <label>Nguồn gốc</label>
                    <input type="text" name="nguon_goc" id="edit_nguon_goc">
                </div>
                <div class="form-group full-width">
                    <label>Tóm tắt</label>
                    <textarea name="tom_tat" id="edit_tom_tat" style="min-height:100px;"></textarea>
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-file-code" style="color: var(--primary); margin-right: 6px;"></i>Nội dung truyện <small style="color: var(--gray); font-weight: 400;">(hỗ trợ HTML)</small> <span style="color:red;">*</span></label>
                    <div class="html-editor-toolbar" style="background: var(--gray-light); padding: 8px 12px; border-radius: 12px 12px 0 0; border: 2px solid var(--gray-light); border-bottom: none; display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'b')" title="In đậm"><i class="fas fa-bold"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'i')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'u')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'h2')" title="Tiêu đề 2"><i class="fas fa-heading"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'h3')" title="Tiêu đề 3"><i class="fas fa-heading" style="font-size: 0.9em;"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'p')" title="Đoạn văn"><i class="fas fa-paragraph"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'ul')" title="Danh sách"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'ol')" title="Danh sách số"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="editor-btn" onclick="insertTag('edit_noi_dung', 'a')" title="Link"><i class="fas fa-link"></i></button>
                        <button type="button" class="editor-btn" onclick="insertImage('edit_noi_dung')" title="Chèn ảnh"><i class="fas fa-image"></i></button>
                    </div>
                    <textarea name="noi_dung" id="edit_noi_dung" required rows="10" style="border-radius: 0 0 12px 12px; font-family: 'Consolas', monospace; font-size: 0.9rem;"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Ảnh đại diện</label>
                    <div id="edit_current_image_preview"></div>
                    <input type="file" name="anh_dai_dien" id="edit_anh_dai_dien" accept="image/*" onchange="previewImage(this, 'edit_preview')">
                    <img id="edit_preview" style="display:none; margin-top:12px; max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="edit_trang_thai">
                        <option value="hien_thi">Hiển thị</option>
                        <option value="an">Ẩn</option>
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
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

// Validate edit form before submit
function validateEditForm() {
    const maTruyen = document.getElementById('edit_ma_truyen').value;
    const tieuDe = document.getElementById('edit_tieu_de').value;
    const noiDung = document.getElementById('edit_noi_dung').value;
    
    console.log('Validating form:', { maTruyen, tieuDe, noiDung: noiDung.substring(0, 50) + '...' });
    
    if (!maTruyen) {
        alert('Lỗi: Không có ID truyện. Vui lòng đóng form và thử lại.');
        return false;
    }
    
    if (!tieuDe || tieuDe.trim().length < 5) {
        alert('Tiêu đề phải có ít nhất 5 ký tự');
        return false;
    }
    
    if (!noiDung || noiDung.trim().length < 50) {
        alert('Nội dung phải có ít nhất 50 ký tự');
        return false;
    }
    
    return true;
}

// Load story data and open edit modal
function loadAndEditStory(id) {
    console.log('Loading story ID:', id);
    
    // Show loading
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    
    // Disable form while loading
    const form = document.getElementById('editForm');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea, select, button');
        inputs.forEach(input => input.disabled = true);
    }
    
    // Load data via AJAX
    fetch('get_truyen.php?id=' + id)
        .then(response => response.json())
        .then(story => {
            if (story.error) {
                throw new Error(story.error);
            }
            
            console.log('Story loaded:', story);
            console.log('Nội dung length:', story.noi_dung ? story.noi_dung.length : 0);
            
            // Set values
            document.getElementById('edit_ma_truyen').value = story.ma_truyen;
            document.getElementById('edit_tieu_de').value = story.tieu_de;
            document.getElementById('edit_tieu_de_khmer').value = story.tieu_de_khmer || '';
            document.getElementById('edit_the_loai').value = story.ma_danh_muc || '';
            document.getElementById('edit_tac_gia').value = story.tac_gia || '';
            document.getElementById('edit_nguon_goc').value = story.nguon || '';
            document.getElementById('edit_tom_tat').value = story.tom_tat || '';
            document.getElementById('edit_noi_dung').value = story.noi_dung || '';
            // Không thể set value cho input file, chỉ hiển thị ảnh hiện tại
            document.getElementById('edit_trang_thai').value = story.trang_thai;
            
            // Hiển thị ảnh hiện tại nếu có
            const currentImagePreview = document.getElementById('edit_current_image_preview');
            if (currentImagePreview && story.anh_dai_dien) {
                currentImagePreview.innerHTML = '<p style="margin:10px 0; color:#666;">Ảnh hiện tại:</p><img src="../' + story.anh_dai_dien + '" style="max-width:200px; border-radius:8px;">';
            } else if (currentImagePreview) {
                currentImagePreview.innerHTML = '<p style="margin:10px 0; color:#999;">Chưa có ảnh</p>';
            }
            
            // Enable form
            if (form) {
                const inputs = form.querySelectorAll('input, textarea, select, button');
                inputs.forEach(input => input.disabled = false);
            }
        })
        .catch(error => {
            console.error('Error loading story:', error);
            alert('Lỗi khi tải dữ liệu truyện: ' + error.message);
            modal.style.display = 'none';
        });
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Delete story
function deleteStory(id) {
    if(confirm('Bạn có chắc chắn muốn xóa truyện này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ma_truyen" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Filter stories
function filterStories() {
    const category = document.getElementById('filterCategory').value.toLowerCase();
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#storiesTableBody tr');
    
    rows.forEach(row => {
        if(row.cells.length === 1) return; // Skip empty row
        
        const rowCategory = row.dataset.category || '';
        const rowStatus = row.dataset.status || '';
        const rowText = row.textContent.toLowerCase();
        
        const matchCategory = !category || rowCategory === category;
        const matchStatus = !status || rowStatus === status;
        const matchSearch = !search || rowText.includes(search);
        
        row.style.display = (matchCategory && matchStatus && matchSearch) ? '' : 'none';
    });
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#storiesTableBody tr');
    
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

// HTML Editor Functions
function insertTag(textareaId, tag) {
    const textarea = document.getElementById(textareaId);
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    if (tag === 'ul' || tag === 'ol') {
        const listHtml = `<${tag}>\n  <li>Mục 1</li>\n  <li>Mục 2</li>\n  <li>Mục 3</li>\n</${tag}>`;
        textarea.value = textarea.value.substring(0, start) + listHtml + textarea.value.substring(end);
        textarea.focus();
    } else if (tag === 'a') {
        const url = prompt('Nhập URL:', 'https://');
        if (url) {
            const linkText = selectedText || 'Văn bản liên kết';
            const linkHtml = `<a href="${url}" target="_blank">${linkText}</a>`;
            textarea.value = textarea.value.substring(0, start) + linkHtml + textarea.value.substring(end);
            textarea.focus();
        }
    } else {
        const replacement = `<${tag}>${selectedText}</${tag}>`;
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + tag.length + 2, start + tag.length + 2 + selectedText.length);
    }
}

function insertImage(textareaId) {
    const url = prompt('Nhập URL hình ảnh:', 'https://');
    if (url) {
        const alt = prompt('Nhập mô tả hình ảnh:', 'Hình ảnh');
        const textarea = document.getElementById(textareaId);
        const start = textarea.selectionStart;
        const imgHtml = `<img src="${url}" alt="${alt}" style="max-width: 100%;">`;
        textarea.value = textarea.value.substring(0, start) + imgHtml + textarea.value.substring(start);
        textarea.focus();
    }
}
</script>

</body>
</html>
