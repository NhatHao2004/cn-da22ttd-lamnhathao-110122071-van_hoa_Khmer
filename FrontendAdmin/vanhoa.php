<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/VanHoa.php';
require_once 'includes/upload.php';
require_once 'includes/image_helper.php';

// Kiểm tra đăng nhập admin
checkAdminAuth();

// Cập nhật thông tin admin từ database
refreshAdminInfo();

// Disable cache để tránh hiển thị dữ liệu cũ
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$db = Database::getInstance();
$vanHoaModel = new VanHoa();

// Xử lý các hành động CRUD với PRG Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Tạo token chống duplicate submit
    $submitToken = $_POST['submit_token'] ?? '';
    $sessionToken = $_SESSION['last_submit_token'] ?? '';
    
    // Kiểm tra duplicate submit
    if ($action === 'add' && !empty($submitToken) && $submitToken === $sessionToken) {
        // Đây là duplicate submit, bỏ qua
        header('Location: vanhoa.php');
        exit;
    }
    
    // Lưu token mới
    if ($action === 'add' && !empty($submitToken)) {
        $_SESSION['last_submit_token'] = $submitToken;
    }
    
    try {
        switch($action) {
            case 'add':
                // Xử lý upload ảnh
                $imagePath = '';
                if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('vanhoa');
                    $imagePath = $uploader->upload($_FILES['hinh_anh']);
                    if (!$imagePath) {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                $data = [
                    'tieu_de' => trim($_POST['tieu_de']),
                    'noi_dung' => $_POST['noi_dung'],
                    'tom_tat' => $_POST['tom_tat'] ?? '',
                    'ma_danh_muc' => !empty($_POST['danh_muc']) ? $_POST['danh_muc'] : null,
                    'trang_thai' => $_POST['trang_thai'],
                    'hinh_anh_chinh' => $imagePath
                ];
                
                // Kiểm tra bài viết trùng tiêu đề trong 5 giây gần đây
                $recentDuplicate = $db->querySingle(
                    "SELECT ma_van_hoa FROM van_hoa WHERE tieu_de = ? AND ngay_tao > DATE_SUB(NOW(), INTERVAL 5 SECOND)",
                    [trim($_POST['tieu_de'])]
                );
                
                if ($recentDuplicate) {
                    $_SESSION['flash_message'] = 'Bài viết đã được tạo trước đó!';
                    $_SESSION['flash_type'] = 'warning';
                    header('Location: vanhoa.php');
                    exit;
                }
                
                if($vanHoaModel->create($data)) {
                    $_SESSION['flash_message'] = 'Thêm bài viết văn hóa thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi thêm bài viết!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: vanhoa.php');
                exit;
                
            case 'edit':
                // Lấy thông tin bài viết hiện tại
                $currentArticle = $vanHoaModel->getById($_POST['ma_van_hoa']);
                $imagePath = $currentArticle['hinh_anh_chinh'] ?? $currentArticle['hinh_anh'] ?? '';
                
                // Xử lý upload ảnh mới
                if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploader = new ImageUploader('vanhoa');
                    $newImagePath = $uploader->upload($_FILES['hinh_anh']);
                    if ($newImagePath) {
                        // Xóa ảnh cũ nếu có
                        if ($imagePath && file_exists(__DIR__ . '/../' . $imagePath)) {
                            @unlink(__DIR__ . '/../' . $imagePath);
                        }
                        $imagePath = $newImagePath;
                    } else {
                        throw new Exception('Lỗi upload ảnh: ' . $uploader->getErrorString());
                    }
                }
                
                $data = [
                    'tieu_de' => $_POST['tieu_de'],
                    'tieu_de_khmer' => $_POST['tieu_de_khmer'] ?? '',
                    'noi_dung' => $_POST['noi_dung'],
                    'tom_tat' => $_POST['tom_tat'] ?? '',
                    'ma_danh_muc' => !empty($_POST['danh_muc']) ? $_POST['danh_muc'] : null,
                    'trang_thai' => $_POST['trang_thai'],
                    'hinh_anh_chinh' => $imagePath
                ];
                
                if($vanHoaModel->update($_POST['ma_van_hoa'], $data)) {
                    $_SESSION['flash_message'] = 'Cập nhật bài viết văn hóa thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi cập nhật bài viết!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: vanhoa.php');
                exit;
                
            case 'delete':
                // Lấy thông tin bài viết để xóa ảnh
                $article = $vanHoaModel->getById($_POST['ma_van_hoa']);
                $articleImage = $article['hinh_anh_chinh'] ?? $article['hinh_anh'] ?? '';
                if ($article && $articleImage && file_exists(__DIR__ . '/../' . $articleImage)) {
                    @unlink(__DIR__ . '/../' . $articleImage);
                }
                
                if($vanHoaModel->delete($_POST['ma_van_hoa'])) {
                    $_SESSION['flash_message'] = 'Xóa bài viết văn hóa thành công!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Có lỗi khi xóa bài viết!';
                    $_SESSION['flash_type'] = 'error';
                }
                header('Location: vanhoa.php');
                exit;
        }
    } catch(Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header('Location: vanhoa.php');
        exit;
    }
}

// Lấy flash message từ session
$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Lấy danh sách bài viết văn hóa
$sql = "SELECT * FROM van_hoa ORDER BY ngay_tao DESC LIMIT 1000";
$articlesRaw = $db->query($sql) ?: [];

// Lấy thông tin người tạo riêng nếu cần - KHÔNG dùng reference
$articlesWithCreator = [];
foreach($articlesRaw as $article) {
    if (!empty($article['ma_nguoi_tao'])) {
        $creator = $db->querySingle("SELECT ho_ten FROM quan_tri_vien WHERE ma_qtv = ?", [$article['ma_nguoi_tao']]);
        $article['nguoi_tao'] = $creator['ho_ten'] ?? 'N/A';
    } else {
        $article['nguoi_tao'] = 'N/A';
    }
    $articlesWithCreator[] = $article;
}
$articlesRaw = $articlesWithCreator;

// Loại bỏ duplicate dựa trên ma_van_hoa - Phương pháp mới
$articles = [];
$seenIds = [];

// Đầu tiên, log raw data
error_log("=== RAW ARTICLES FROM DB ===");
error_log("Count: " . count($articlesRaw));
foreach($articlesRaw as $idx => $art) {
    error_log("Raw[$idx]: ID={$art['ma_van_hoa']}, Title={$art['tieu_de']}");
}

// Loại bỏ duplicate
foreach($articlesRaw as $article) {
    $id = (int)$article['ma_van_hoa'];
    if (!isset($seenIds[$id])) {
        $seenIds[$id] = true;
        $articles[] = $article;
        error_log("ADDED: ID {$id} - {$article['tieu_de']}");
    } else {
        error_log("SKIPPED DUPLICATE: ID {$id} - {$article['tieu_de']}");
    }
}

// Log kết quả cuối cùng
error_log("=== FINAL ARTICLES ===");
error_log("Count: " . count($articles));
foreach($articles as $idx => $art) {
    error_log("Final[$idx]: ID={$art['ma_van_hoa']}, Title={$art['tieu_de']}");
}

// Lấy danh mục từ database để map
$categoriesForMap = $db->query("SELECT ma_danh_muc, ten_danh_muc FROM danh_muc WHERE loai = 'van_hoa'") ?: [];
$categoryMap = [];
foreach($categoriesForMap as $cat) {
    $categoryMap[$cat['ma_danh_muc']] = $cat['ten_danh_muc'];
}

// Xử lý articles - KHÔNG dùng reference để tránh side effect
$processedArticles = [];
foreach($articles as $article) {
    // Thêm tên danh mục
    if(isset($article['ma_danh_muc']) && isset($categoryMap[$article['ma_danh_muc']])) {
        $article['ten_danh_muc'] = $categoryMap[$article['ma_danh_muc']];
    } else {
        $article['ten_danh_muc'] = 'Chưa phân loại';
    }
    
    // Format ngày tạo
    if(isset($article['ngay_tao'])) {
        $article['ngay_tao_fmt'] = date('d/m/Y H:i', strtotime($article['ngay_tao']));
    }
    
    $processedArticles[] = $article;
}

// Gán lại vào $articles
$articles = $processedArticles;

// Lấy danh mục từ dữ liệu có sẵn
$categories = $vanHoaModel->getCategories();
if(!is_array($categories)) {
    $categories = [];
}

// Thống kê
$total_articles = $vanHoaModel->count();
$published_count = $vanHoaModel->countByStatus('xuat_ban');
$draft_count = $vanHoaModel->countByStatus('nhap');
$total_views = $vanHoaModel->getTotalViews();

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
<meta name="description" content="Quản lý bài viết văn hóa Khmer">
<meta name="theme-color" content="#6366f1">
<title>Quản lý bài viết văn hóa Khmer</title>
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
    background:var(--primary);
    border-radius:12px;
    box-shadow:var(--shadow);
    transition:all 0.3s ease;
}
.topbar-action-icon:hover .icon-wrapper {
    transform:scale(1.05);
    background:var(--primary-dark);
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
    background:var(--gradient-primary);
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
    background:var(--gradient-primary);
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
    background:var(--gradient-primary);
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
    text-decoration:none;
}
.btn-add-new:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(99,102,241,0.3);
    background:var(--primary);
    color:var(--white);
    text-decoration:none;
}
.btn-add-new i {
    font-size:1.15rem;
    transition:transform 0.3s ease;
}
.btn-add-new:hover i {
    transform:rotate(90deg) scale(1.3);
}
.btn-quiz-manage {
    background:var(--white);
    color:var(--primary);
}
.btn-quiz-manage:hover {
    background:var(--primary);
    color:var(--white);
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
                <div class="menu-item active" onclick="location.href='vanhoa.php'">
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
                    <input type="text" id="searchInput" placeholder="Tìm kiếm bài viết..." autocomplete="off">
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
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <h1>Quản lý bài viết văn hóa Khmer</h1>
                            <p>Khám phá và quản lý kho tàng văn hóa Khmer Nam Bộ</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <a href="quiz-vanhoa.php" class="btn-add-new btn-quiz-manage">
                            <i class="fas fa-question-circle"></i>
                            Quản lý Quiz
                        </a>
                        <button class="btn-add-new" onclick="openAddModal()">
                            <i class="fas fa-plus-circle"></i>
                            Thêm bài mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #667eea;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Tổng bài viết</span>
                            <div class="stat-number"><?php echo number_format($total_articles); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                            <i class="fas fa-database"></i> Tất cả bài viết
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #10b981;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Đã xuất bản</span>
                            <div class="stat-number"><?php echo number_format($published_count); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-globe"></i> Đang hiển thị
                        </span>
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #f59e0b;">
                    <div class="stat-header">
                        <div>
                            <span class="stat-label">Bản nháp</span>
                            <div class="stat-number"><?php echo number_format($draft_count); ?></div>
                        </div>
                        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="fas fa-hourglass-half"></i> Chờ duyệt
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
                            <i class="fas fa-chart-line"></i> Tổng lượt xem
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        Danh sách bài viết văn hóa Khmer
                    </h3>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <div class="filter-item">
                        <select id="categoryFilter">
                            <option value="">Tất cả danh mục</option>
                            <?php 
                            if (!empty($categories)) {
                                foreach($categories as $cat): 
                            ?>
                                <option value="<?php echo htmlspecialchars($cat['ma_danh_muc'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($cat['ten_danh_muc'] ?? ''); ?>
                                </option>
                            <?php 
                                endforeach;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="xuat_ban">Đã xuất bản</option>
                            <option value="nhap">Bản nháp</option>
                        </select>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="data-table" id="articlesTable">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // DEBUG: In ra số lượng articles
                            echo "<!-- DEBUG: Total articles to render: " . count($articles) . " -->\n";
                            echo "<!-- DEBUG: Article IDs: " . implode(', ', array_column($articles, 'ma_van_hoa')) . " -->\n";
                            echo "<!-- DEBUG: Articles array: " . print_r(array_map(function($a) { 
                                return ['id' => $a['ma_van_hoa'], 'title' => $a['tieu_de']]; 
                            }, $articles), true) . " -->\n";
                            ?>
                            <?php if(empty($articles)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:var(--gray);">
                                    <i class="fas fa-inbox" style="font-size:3rem; margin-bottom:16px; display:block;"></i>
                                    <strong>Chưa có bài viết nào</strong>
                                    <p style="margin-top:8px;">Hãy thêm bài viết đầu tiên!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php 
                            // Debug: Đếm số lần render
                            $renderCount = 0;
                            foreach($articles as $article): 
                                $renderCount++;
                                $articleId = (int)$article['ma_van_hoa'];
                            ?>
                            <!-- Render #<?php echo $renderCount; ?> - ID: <?php echo $articleId; ?> - Title: <?php echo htmlspecialchars($article['tieu_de']); ?> -->
                            <tr data-article-id="<?php echo $articleId; ?>"
                                data-tieu-de="<?php echo htmlspecialchars($article['tieu_de'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-tom-tat="<?php echo htmlspecialchars($article['tom_tat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-danh-muc="<?php echo htmlspecialchars($article['ma_danh_muc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-trang-thai="<?php echo htmlspecialchars($article['trang_thai'] ?? 'nhap', ENT_QUOTES, 'UTF-8'); ?>"
                                data-noi-dung="<?php echo htmlspecialchars($article['noi_dung'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-hinh-anh="<?php echo htmlspecialchars($article['hinh_anh_chinh'] ?? $article['hinh_anh'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="<?php echo $article['ma_danh_muc'] ?? ''; ?>" 
                                data-status="<?php echo $article['trang_thai'] ?? 'nhap'; ?>">
                                <td><?php echo $renderCount; ?></td>
                                <td>
                                    <?php 
                                    $imgPath = $article['hinh_anh_chinh'] ?? $article['hinh_anh'] ?? '';
                                    $imgUrl = '';
                                    if (!empty($imgPath)) {
                                        if (strpos($imgPath, 'uploads/') === 0) {
                                            $imgUrl = '../' . $imgPath;
                                        } else {
                                            $imgUrl = '../uploads/vanhoa/' . $imgPath;
                                        }
                                    }
                                    ?>
                                    <?php if(!empty($imgUrl)): ?>
                                    <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="" class="article-image">
                                    <?php else: ?>
                                    <div class="article-image" style="background:var(--gray-light); display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-image" style="color:var(--gray);"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($article['tieu_de']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($article['ten_danh_muc'] ?? 'Chưa phân loại'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $article['trang_thai'] === 'xuat_ban' ? 'published' : 'draft'; ?>">
                                        <?php echo $article['trang_thai'] === 'xuat_ban' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-eye"></i> <?php echo number_format($article['luot_xem']); ?>
                                </td>
                                <td><?php echo $article['ngay_tao_fmt']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick="editArticle(<?php echo $articleId; ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteArticle(<?php echo $articleId; ?>)" title="Xóa">
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
            <h3><i class="fas fa-book-open"></i> Thêm bài viết mới</h3>
            <button class="modal-close" onclick="closeAddModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="addForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="submit_token" id="add_submit_token" value="">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label><i class="fas fa-heading"></i> Tiêu đề <span class="required">*</span></label>
                    <input type="text" name="tieu_de" required placeholder="Nhập tiêu đề bài viết">
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-align-left"></i> Mô tả ngắn</label>
                    <textarea name="tom_tat" rows="2" placeholder="Mô tả ngắn gọn về bài viết"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-folder"></i> Danh mục</label>
                    <select name="danh_muc">
                        <option value="">📂 Chọn danh mục</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            📁 <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Trạng thái <span class="required">*</span></label>
                    <select name="trang_thai" required>
                        <option value="nhap">📝 Bản nháp</option>
                        <option value="xuat_ban">✅ Xuất bản</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-image"></i> Hình ảnh (JPG, PNG, GIF, WEBP - Tối đa 5MB)</label>
                    <input type="file" name="hinh_anh" id="add_hinh_anh" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(this, 'add_preview')">
                    <img id="add_preview" style="display:none; margin-top:12px; max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-file-code" style="color: var(--primary); margin-right: 6px;"></i>Nội dung <small style="color: var(--gray); font-weight: 400;">(hỗ trợ HTML)</small> <span style="color:red;">*</span></label>
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
                    <textarea name="noi_dung" id="add_noi_dung" required placeholder="Nhập nội dung chi tiết bài viết...&#10;&#10;Ví dụ:&#10;<h2>Giới thiệu</h2>&#10;<p>Nội dung bài viết...</p>&#10;<ul>&#10;  <li>Điểm 1</li>&#10;  <li>Điểm 2</li>&#10;</ul>" rows="10" style="border-radius: 0 0 12px 12px; font-family: 'Consolas', monospace; font-size: 0.9rem;"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu bài viết
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Chỉnh sửa bài viết</h3>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ma_van_hoa" id="edit_ma_van_hoa">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Tiêu đề *</label>
                    <input type="text" name="tieu_de" id="edit_tieu_de" required>
                </div>
                <div class="form-group full-width">
                    <label>Mô tả ngắn</label>
                    <textarea name="tom_tat" id="edit_mo_ta_ngan" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="danh_muc" id="edit_danh_muc">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái *</label>
                    <select name="trang_thai" id="edit_trang_thai" required>
                        <option value="nhap">Bản nháp</option>
                        <option value="xuat_ban">Xuất bản</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Hình ảnh (JPG, PNG, GIF, WEBP - Tối đa 5MB)</label>
                    <input type="hidden" name="current_hinh_anh" id="edit_current_hinh_anh">
                    <input type="file" name="hinh_anh" id="edit_hinh_anh" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(this, 'edit_preview')">
                    <div id="edit_current_image_wrapper" style="display:none; margin-top:12px;">
                        <p style="font-size:0.85rem; color:var(--gray); margin-bottom:8px;">Ảnh hiện tại:</p>
                        <img id="edit_current_image" style="max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <img id="edit_preview" style="display:none; margin-top:12px; max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                </div>
                <div class="form-group full-width">
                    <label><i class="fas fa-file-code" style="color: var(--primary); margin-right: 6px;"></i>Nội dung <small style="color: var(--gray); font-weight: 400;">(hỗ trợ HTML)</small> <span style="color:red;">*</span></label>
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
        <p style="margin-bottom:24px;">Bạn có chắc chắn muốn xóa bài viết này? Hành động này không thể hoàn tác!</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ma_van_hoa" id="delete_ma_van_hoa">
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
// Preview image function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        // Kiểm tra kích thước file (5MB)
        if (input.files[0].size > 5242880) {
            alert('⚠️ File quá lớn! Vui lòng chọn ảnh nhỏ hơn 5MB.');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Kiểm tra loại file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(input.files[0].type)) {
            alert('⚠️ Loại file không hợp lệ! Chỉ chấp nhận: JPG, PNG, GIF, WEBP');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
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

// Tạo token ngẫu nhiên
function generateToken() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Modal functions
function openAddModal() {
    // Reset form
    document.getElementById('addForm').reset();
    document.getElementById('add_preview').style.display = 'none';
    // Tạo token mới mỗi khi mở modal
    document.getElementById('add_submit_token').value = generateToken();
    // Enable lại nút submit
    const submitBtn = document.querySelector('#addForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu bài viết';
    }
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

// Edit article - Sử dụng data attributes thay vì JSON để tránh lỗi
function editArticle(articleId) {
    // Lấy dữ liệu từ data attributes của row
    const row = document.querySelector(`tr[data-article-id="${articleId}"]`);
    if (!row) {
        alert('Không tìm thấy bài viết!');
        return;
    }
    
    const article = {
        ma_van_hoa: row.dataset.articleId,
        tieu_de: row.dataset.tieuDe || '',
        tom_tat: row.dataset.tomTat || '',
        danh_muc: row.dataset.danhMuc || '',
        trang_thai: row.dataset.trangThai || 'nhap',
        noi_dung: row.dataset.noiDung || '',
        hinh_anh_chinh: row.dataset.hinhAnh || ''
    };
    
    document.getElementById('edit_ma_van_hoa').value = article.ma_van_hoa;
    document.getElementById('edit_tieu_de').value = article.tieu_de;
    document.getElementById('edit_mo_ta_ngan').value = article.tom_tat;
    document.getElementById('edit_danh_muc').value = article.danh_muc;
    document.getElementById('edit_trang_thai').value = article.trang_thai;
    document.getElementById('edit_noi_dung').value = article.noi_dung;
    
    // Reset file input và preview
    document.getElementById('edit_hinh_anh').value = '';
    document.getElementById('edit_preview').style.display = 'none';
    
    // Hiển thị ảnh hiện tại nếu có
    const currentImageWrapper = document.getElementById('edit_current_image_wrapper');
    const currentImage = document.getElementById('edit_current_image');
    const imgPath = article.hinh_anh_chinh;
    document.getElementById('edit_current_hinh_anh').value = imgPath;
    
    if (imgPath) {
        let imgUrl = imgPath.startsWith('uploads/') ? '../' + imgPath : '../uploads/vanhoa/' + imgPath;
        currentImage.src = imgUrl;
        currentImageWrapper.style.display = 'block';
    } else {
        currentImageWrapper.style.display = 'none';
    }
    
    openEditModal();
}

// Delete article
function deleteArticle(id) {
    document.getElementById('delete_ma_van_hoa').value = id;
    openDeleteModal();
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#articlesTable tbody tr');
    rows.forEach(row => {
        const title = row.cells[2].textContent.toLowerCase();
        row.style.display = title.includes(query) ? '' : 'none';
    });
});

// Filter by category
document.getElementById('categoryFilter').addEventListener('change', function() {
    applyFilters();
});

// Filter by status
document.getElementById('statusFilter').addEventListener('change', function() {
    applyFilters();
});

function applyFilters() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#articlesTable tbody tr');
    
    rows.forEach(row => {
        const category = row.dataset.category;
        const status = row.dataset.status;
        
        const categoryMatch = !categoryFilter || category === categoryFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        row.style.display = (categoryMatch && statusMatch) ? '' : 'none';
    });
}

// Prevent double submit for add form
document.getElementById('addForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
});

// Prevent double submit for edit form
document.getElementById('editForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
});

// Debug: Đếm số rows thực tế trong DOM
window.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#articlesTable tbody tr');
    console.log('=== DOM DEBUG ===');
    console.log('Total rows in DOM:', rows.length);
    rows.forEach((row, index) => {
        const id = row.dataset.articleId || 'N/A';
        const title = row.cells[2]?.textContent || 'N/A';
        console.log(`Row ${index + 1}: ID=${id}, Title=${title}`);
    });
});

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

