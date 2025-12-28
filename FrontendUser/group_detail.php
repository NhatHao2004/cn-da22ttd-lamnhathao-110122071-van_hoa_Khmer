<?php
/**
 * Chi tiết nhóm học tập - Group Detail (Unified Design)
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';

// Lấy ID nhóm
$groupId = $_GET['id'] ?? 0;
if (!$groupId) redirect(BASE_URL . '/learning_groups.php');

// Lấy ngôn ngữ hiện tại
$currentLang = $_SESSION['lang'] ?? 'vi';
$isKhmer = ($currentLang === 'km');

$pdo = getDBConnection();

// Lấy thông tin người dùng hiện tại
$currentUser = null;
if (isLoggedIn()) {
    $ma_nguoi_dung = $_SESSION['user_id'] ?? $_SESSION['ma_nguoi_dung'] ?? null;
    if ($ma_nguoi_dung) {
        $userStmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $userStmt->execute([$ma_nguoi_dung]);
        $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Lấy thông tin nhóm
$group = null;
$groupPosts = [];
$recentMembers = [];
$isMember = false;

try {
    // Lấy thông tin nhóm
    $stmt = $pdo->prepare("
        SELECT 
            nht.*,
            nd.ho_ten as ten_nguoi_tao,
            nd.anh_dai_dien as anh_nguoi_tao
        FROM nhom_hoc_tap nht
        LEFT JOIN nguoi_dung nd ON nht.ma_nguoi_tao = nd.ma_nguoi_dung
        WHERE nht.ma_nhom = ? AND nht.trang_thai = 'hoat_dong'
    ");
    $stmt->execute([$groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        redirect(BASE_URL . '/learning_groups.php', 'Nhóm không tồn tại.', 'warning');
    }
    
    // Kiểm tra người dùng đã là thành viên chưa
    if (isLoggedIn()) {
        // Lấy ma_nguoi_dung từ session (hỗ trợ cả 2 key)
        $ma_nguoi_dung = $_SESSION['user_id'] ?? $_SESSION['ma_nguoi_dung'] ?? null;
        
        if ($ma_nguoi_dung) {
            $memberStmt = $pdo->prepare("
                SELECT * FROM thanh_vien_nhom 
                WHERE ma_nhom = ? AND ma_nguoi_dung = ? AND trang_thai = 'hoat_dong'
            ");
            $memberStmt->execute([$groupId, $ma_nguoi_dung]);
            $isMember = $memberStmt->rowCount() > 0;
        }
    }
    
    // Lấy bài viết trong nhóm
    $postsStmt = $pdo->prepare("
        SELECT 
            bvn.*,
            nd.ho_ten as nguoi_dang,
            nd.anh_dai_dien,
            nd.ma_nguoi_dung as ma_nguoi_dang
        FROM bai_viet_nhom bvn
        JOIN nguoi_dung nd ON bvn.ma_nguoi_dung = nd.ma_nguoi_dung
        WHERE bvn.ma_nhom = ? AND bvn.trang_thai = 'hien_thi'
        ORDER BY bvn.ghim_bai DESC, bvn.ngay_dang DESC
        LIMIT 10
    ");
    $postsStmt->execute([$groupId]);
    $groupPosts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy thành viên mới
    $membersStmt = $pdo->prepare("
        SELECT 
            nd.ho_ten,
            nd.anh_dai_dien,
            tvn.ngay_tham_gia
        FROM thanh_vien_nhom tvn
        JOIN nguoi_dung nd ON tvn.ma_nguoi_dung = nd.ma_nguoi_dung
        WHERE tvn.ma_nhom = ? AND tvn.trang_thai = 'hoat_dong'
        ORDER BY tvn.ngay_tham_gia DESC
        LIMIT 5
    ");
    $membersStmt->execute([$groupId]);
    $recentMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error loading group: " . $e->getMessage());
    redirect(BASE_URL . '/learning_groups.php', 'Đã xảy ra lỗi.', 'error');
}

$pageTitle = $isKhmer && !empty($group['ten_nhom_km']) ? $group['ten_nhom_km'] : $group['ten_nhom'];

// Helper function để lấy icon file
function getFileIcon($ext) {
    $icons = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'ppt' => 'powerpoint',
        'pptx' => 'powerpoint',
        'txt' => 'alt'
    ];
    return $icons[$ext] ?? 'alt';
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Modern Group Detail Page ===== */
.group-detail-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #E6F4F1 0%, #D4EDE7 50%, #C2E6DD 100%);
    padding-top: 80px;
    position: relative;
    overflow: hidden;
}

.group-detail-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    opacity: 0.3;
    pointer-events: none;
}

/* Hero Cover Section */
.group-hero-cover {
    position: relative;
    height: 320px;
    background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
    overflow: hidden;
    margin-bottom: -100px;
    background-size: contain !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
}

.group-hero-pattern {
    position: absolute;
    inset: 0;
    opacity: 0.15;
    background-image: 
        repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.15) 35px, rgba(255,255,255,.15) 70px);
}

.group-hero-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 10rem;
    color: rgba(255,255,255,0.15);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translate(-50%, -50%) translateY(0px); }
    50% { transform: translate(-50%, -50%) translateY(-20px); }
}

/* Main Container */
.group-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem 3rem;
    position: relative;
    z-index: 10;
}

/* Group Header Card */
.group-header-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    border: 4px solid #1a1a1a;
}

.group-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #00b894, #00cec9, #74b9ff);
}

.group-header-content {
    display: flex;
    align-items: flex-start;
    gap: 2rem;
}

.group-icon-box {
    width: 140px;
    height: 140px;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4.5rem;
    color: white;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
    border: 4px solid #1a1a1a;
}

.group-icon-box::before {
    content: '';
    position: absolute;
    inset: -50%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.group-header-info {
    flex: 1;
}

.group-title-main {
    font-size: 2.25rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
    line-height: 1.2;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
}

.group-description-text {
    font-size: 1.0625rem;
    color: #2d3436;
    line-height: 1.7;
    margin-bottom: 1.25rem;
    font-weight: 600;
}

.group-stats-row {
    display: flex;
    gap: 2rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.group-stat-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1.25rem;
    background: #ffffff;
    border-radius: 12px;
    border: 3px solid #1a1a1a;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.group-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.group-stat-content {
    display: flex;
    flex-direction: column;
}

.group-stat-label {
    font-size: 0.75rem;
    color: #2d3436;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.group-stat-value {
    font-size: 1.125rem;
    font-weight: 900;
    color: #1a1a1a;
}

.group-action-buttons {
    display: flex;
    gap: 0.875rem;
    flex-wrap: wrap;
}

.group-btn {
    padding: 0.875rem 1.75rem;
    border-radius: 14px;
    font-size: 0.9375rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 0.625rem;
    text-decoration: none;
}

.group-btn-primary {
    background: linear-gradient(135deg, #00b894, #00a383);
    color: white;
    box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3);
    border: 3px solid #1a1a1a;
}

.group-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 184, 148, 0.4);
}

.group-btn-secondary {
    background: #ffffff;
    color: #00b894;
    border: 3px solid #1a1a1a;
}

.group-btn-secondary:hover {
    background: #E6F4F1;
    transform: translateY(-2px);
}

.group-btn-danger {
    background: #ffffff;
    color: #ef4444;
    border: 3px solid #1a1a1a;
}

.group-btn-danger:hover {
    background: #fef2f2;
    transform: translateY(-2px);
}

/* Two Column Layout */
.group-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}

/* Main Content Area */
.group-main-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Post Composer */
.post-composer {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.75rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.composer-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.composer-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.25rem;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
    border: 3px solid #1a1a1a;
}

.composer-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.composer-input {
    flex: 1;
}

.composer-input input {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 3px solid #1a1a1a;
    border-radius: 50px;
    font-size: 0.9375rem;
    background: #ffffff;
    transition: all 0.3s;
    cursor: pointer;
    font-weight: 600;
}

.composer-input input:hover {
    border-color: #00b894;
    background: #E6F4F1;
}

.composer-input input:focus {
    outline: none;
    border-color: #00b894;
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 184, 148, 0.1);
}

.composer-input textarea {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 3px solid #1a1a1a;
    border-radius: 16px;
    font-size: 0.9375rem;
    background: #ffffff;
    transition: all 0.3s;
    font-weight: 600;
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
}

.composer-input textarea:focus {
    outline: none;
    border-color: #00b894;
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 184, 148, 0.1);
}

.composer-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    padding-top: 1.25rem;
    border-top: 3px solid #E6F4F1;
}

.composer-action-btn {
    padding: 0.75rem;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 800;
    color: #2d3436;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.composer-action-btn:hover {
    background: #E6F4F1;
    border-color: #00b894;
    color: #00b894;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.2);
}

.composer-action-btn i {
    font-size: 1.125rem;
}

/* Posts Feed */
.group-posts-feed {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.group-post {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.75rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
    transition: all 0.3s;
}

.group-post:hover {
    border-color: #00b894;
    box-shadow: 0 15px 50px rgba(0, 184, 148, 0.2);
    transform: translateY(-2px);
}

.post-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.post-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.125rem;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
    border: 3px solid #1a1a1a;
}

.post-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-user-info {
    flex: 1;
}

.post-username {
    font-weight: 900;
    color: #1a1a1a;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.post-time {
    font-size: 0.8125rem;
    color: #2d3436;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 600;
}

.post-time i {
    font-size: 0.75rem;
}

.post-content {
    margin-bottom: 1.25rem;
}

.post-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.post-text {
    font-size: 1rem;
    color: #2d3436;
    line-height: 1.8;
    font-weight: 600;
}

.post-stats {
    display: flex;
    gap: 1.5rem;
    padding: 1rem 0;
    border-top: 3px solid #E6F4F1;
    border-bottom: 3px solid #E6F4F1;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #2d3436;
    font-weight: 700;
}

.post-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.post-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.post-action-btn {
    padding: 0.75rem;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 800;
    color: #2d3436;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.post-action-btn:hover {
    background: #E6F4F1;
    border-color: #00b894;
    color: #00b894;
    transform: translateY(-2px);
}

.post-action-btn i {
    font-size: 1rem;
}

.post-menu {
    position: relative;
}

.post-menu-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.post-menu-btn:hover {
    background: #E6F4F1;
    border-color: #00b894;
}

.post-menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    min-width: 180px;
    z-index: 100;
    display: none;
}

.post-menu-dropdown.show {
    display: block;
}

.post-menu-item {
    padding: 0.875rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 700;
    font-size: 0.9375rem;
    border-bottom: 2px solid #E6F4F1;
}

.post-menu-item:last-child {
    border-bottom: none;
}

.post-menu-item:hover {
    background: #E6F4F1;
}

.post-menu-item.danger {
    color: #ef4444;
}

.post-menu-item.danger:hover {
    background: #fef2f2;
}

/* Sidebar */
.group-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    position: sticky;
    top: 100px;
}

.sidebar-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.75rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.sidebar-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #00b894;
}

.sidebar-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.sidebar-card-title {
    font-size: 1.125rem;
    font-weight: 900;
    color: #1a1a1a;
    margin: 0;
}

/* About Section */
.about-item {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding: 0.875rem;
    background: #ffffff;
    border-radius: 12px;
    margin-bottom: 0.75rem;
    border: 3px solid #1a1a1a;
}

.about-item:last-child {
    margin-bottom: 0;
}

.about-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.about-content {
    flex: 1;
}

.about-label {
    font-size: 0.75rem;
    color: #2d3436;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.about-value {
    font-size: 0.9375rem;
    color: #1a1a1a;
    font-weight: 700;
}

/* Members List */
.member-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem;
    background: #ffffff;
    border-radius: 12px;
    transition: all 0.3s;
    border: 3px solid #1a1a1a;
}

.member-item:hover {
    background: #E6F4F1;
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.15);
    transform: translateX(4px);
}

.member-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1rem;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
    border: 3px solid #1a1a1a;
}

.member-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.member-info {
    flex: 1;
    min-width: 0;
}

.member-name {
    font-weight: 800;
    color: #1a1a1a;
    font-size: 0.9375rem;
    margin-bottom: 0.125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.member-time {
    font-size: 0.75rem;
    color: #2d3436;
    font-weight: 700;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #E6F4F1;
    border-radius: 20px;
    border: 3px dashed #00b894;
}

/* Post Images & Files */
.post-images img:hover {
    transform: scale(1.05);
    transition: transform 0.3s;
}

.post-files a:hover {
    background: #D4EDE7 !important;
    transform: translateX(4px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #E6F4F1;
    border-radius: 20px;
    border: 3px dashed #00b894;
}

.empty-state-icon {
    font-size: 4rem;
    color: #00b894;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    font-size: 0.9375rem;
    color: #2d3436;
    font-weight: 600;
}

/* Toast Message */
.toast-message {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 1.25rem 1.75rem;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 800;
    font-size: 0.9375rem;
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 3px solid #1a1a1a;
}

.toast-message.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-message.success {
    border-color: #00b894;
    color: #00b894;
}

.toast-message.error {
    border-color: #ef4444;
    color: #ef4444;
}

.toast-message.info {
    border-color: #00cec9;
    color: #00cec9;
}

.toast-message i {
    font-size: 1.5rem;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background: #ffffff;
    border-radius: 24px;
    padding: 2rem;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    border: 4px solid #1a1a1a;
    position: relative;
    transform: scale(0.9);
    transition: transform 0.3s;
}

.modal-overlay.show .modal-content {
    transform: scale(1);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #E6F4F1;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a1a1a;
}

.modal-close {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 1.25rem;
}

.modal-close:hover {
    background: #fef2f2;
    border-color: #ef4444;
    color: #ef4444;
    transform: rotate(90deg);
}

.modal-body {
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s;
    font-family: inherit;
}

.form-textarea {
    resize: vertical;
    min-height: 150px;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #00b894;
    box-shadow: 0 0 0 4px rgba(0, 184, 148, 0.1);
}

.modal-footer {
    display: flex;
    gap: 0.875rem;
    justify-content: flex-end;
}

.modal-btn {
    padding: 0.875rem 1.75rem;
    border-radius: 14px;
    font-size: 0.9375rem;
    font-weight: 800;
    border: 3px solid #1a1a1a;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.modal-btn-primary {
    background: linear-gradient(135deg, #00b894, #00a383);
    color: white;
}

.modal-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3);
}

.modal-btn-secondary {
    background: #ffffff;
    color: #2d3436;
}

.modal-btn-secondary:hover {
    background: #E6F4F1;
}

/* Upload Buttons */
.upload-btn {
    width: 100%;
    padding: 0.875rem 1.25rem;
    background: #ffffff;
    border: 3px dashed #00b894;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 800;
    color: #00b894;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
}

.upload-btn:hover {
    background: #E6F4F1;
    border-color: #00a383;
    transform: translateY(-2px);
}

.upload-btn i {
    font-size: 1.125rem;
}

/* File Preview */
.file-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.preview-item {
    position: relative;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

.preview-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
}

.preview-file {
    width: 200px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.preview-file-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.preview-file-info {
    flex: 1;
    min-width: 0;
}

.preview-file-name {
    font-weight: 800;
    font-size: 0.875rem;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
}

.preview-file-size {
    font-size: 0.75rem;
    color: #2d3436;
    font-weight: 600;
}

.preview-remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ef4444;
    color: white;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.preview-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 1200px) {
    .group-layout {
        grid-template-columns: 1fr;
    }
    
    .group-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .group-detail-wrapper {
        padding-top: 70px;
    }
    
    .group-hero-cover {
        height: 220px;
        margin-bottom: -80px;
    }
    
    .group-hero-icon {
        font-size: 6rem;
    }
    
    .group-container {
        padding: 0 1rem 2rem;
    }
    
    .group-header-card {
        padding: 1.5rem;
    }
    
    .group-header-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .group-icon-box {
        width: 120px;
        height: 120px;
        font-size: 3.5rem;
    }
    
    .group-title-main {
        font-size: 1.75rem;
    }
    
    .group-stats-row {
        justify-content: center;
    }
    
    .group-action-buttons {
        width: 100%;
        flex-direction: column;
    }
    
    .group-btn {
        width: 100%;
        justify-content: center;
    }
    
    .composer-actions,
    .post-actions {
        grid-template-columns: 1fr;
    }
    
    .toast-message {
        bottom: 20px;
        right: 20px;
        left: 20px;
    }
}

@media (max-width: 480px) {
    .group-stats-row {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .group-stat-item {
        width: 100%;
    }
}
</style>

<div class="group-detail-wrapper">
    <!-- Hero Cover -->
    <div class="group-hero-cover" <?php if (!empty($group['anh_banner'])): ?>style="background-image: url('<?= BASE_URL ?>/<?= $group['anh_banner'] ?>');"<?php endif; ?>>
        <?php if (empty($group['anh_banner'])): ?>
        <div class="group-hero-pattern"></div>
        <div class="group-hero-icon">
            <i class="<?= $group['icon'] ?>"></i>
        </div>
        <?php else: ?>
        <!-- Overlay để làm tối ảnh banner một chút -->
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        <?php endif; ?>
    </div>

    <!-- Main Container -->
    <div class="group-container">
        <!-- Group Header Card -->
        <div class="group-header-card">
            <div class="group-header-content">
                <div class="group-icon-box" style="background: <?= $group['mau_sac'] ?>;">
                    <i class="<?= $group['icon'] ?>"></i>
                </div>
                
                <div class="group-header-info">
                    <h1 class="group-title-main">
                        <?= sanitize($isKhmer && !empty($group['ten_nhom_km']) ? $group['ten_nhom_km'] : $group['ten_nhom']) ?>
                    </h1>
                    
                    <div class="group-stats-row">
                        <div class="group-stat-item">
                            <div class="group-stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="group-stat-content">
                                <div class="group-stat-label">Thành viên</div>
                                <div class="group-stat-value"><?= number_format($group['so_thanh_vien']) ?></div>
                            </div>
                        </div>
                        
                        <div class="group-stat-item">
                            <div class="group-stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="group-stat-content">
                                <div class="group-stat-label">Bài viết</div>
                                <div class="group-stat-value"><?= number_format($group['so_bai_viet']) ?></div>
                            </div>
                        </div>
                        
                        <div class="group-stat-item">
                            <div class="group-stat-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="group-stat-content">
                                <div class="group-stat-label">Hiển thị</div>
                                <div class="group-stat-value">Công khai</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="group-action-buttons">
                        <?php if (isLoggedIn()): ?>
                            <?php if ($isMember): ?>
                            <button onclick="leaveGroup(<?= $group['ma_nhom'] ?>)" class="group-btn group-btn-danger">
                                <i class="fas fa-user-minus"></i>
                                Rời nhóm
                            </button>
                            <?php else: ?>
                            <button onclick="joinGroup(<?= $group['ma_nhom'] ?>)" class="group-btn group-btn-primary">
                                <i class="fas fa-user-plus"></i>
                                Tham gia nhóm
                            </button>
                            <?php endif; ?>
                        <button onclick="shareGroup()" class="group-btn group-btn-secondary">
                            <i class="fas fa-share-alt"></i>
                            <?= __('share') ?? 'Chia sẻ' ?>
                        </button>
                        <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="group-btn group-btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            <?= __('login_to_join') ?? 'Đăng nhập để tham gia' ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/learning_groups.php" class="group-btn group-btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="group-layout">
            <!-- Main Content -->
            <div class="group-main-content">
                <!-- Post Composer -->
                <?php 
                // Debug: Kiểm tra điều kiện
                echo "<!-- Debug: isLoggedIn=" . (isLoggedIn() ? 'true' : 'false') . ", isMember=" . ($isMember ? 'true' : 'false') . ", currentUser=" . ($currentUser ? 'exists' : 'null') . " -->";
                ?>
                <?php if (isLoggedIn() && $isMember): ?>
                <div class="post-composer">
                    <div class="composer-header">
                        <div class="composer-avatar">
                            <?php if (!empty($currentUser['anh_dai_dien'])): ?>
                            <img src="<?= UPLOAD_PATH ?>avatar/<?= $currentUser['anh_dai_dien'] ?>" alt="">
                            <?php else: ?>
                            <?= strtoupper(substr($currentUser['ho_ten'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="composer-input">
                            <input type="text" placeholder="Bạn đang nghĩ gì?" onclick="openCreatePostModal(); return false;" readonly style="cursor: pointer;">
                        </div>
                    </div>
                    <div class="composer-actions">
                        <button type="button" class="composer-action-btn" onclick="openCreatePostModal(); return false;">
                            <i class="fas fa-pen"></i>
                            Viết bài
                        </button>
                        <button type="button" class="composer-action-btn" onclick="openCreatePostModal('image'); return false;">
                            <i class="fas fa-image"></i>
                            Ảnh
                        </button>
                        <button type="button" class="composer-action-btn" onclick="openCreatePostModal('file'); return false;">
                            <i class="fas fa-file-alt"></i>
                            Tài liệu
                        </button>
                    </div>
                </div>
                <?php elseif (isLoggedIn() && !$isMember): ?>
                <!-- Thông báo cho người chưa là thành viên -->
                <div class="empty-state" style="padding: 2rem;">
                    <div class="empty-state-icon" style="font-size: 3rem; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="empty-state-title" style="font-size: 1.125rem;">Tham gia nhóm để đăng bài</h3>
                    <p class="empty-state-text" style="margin-bottom: 1rem;">Bạn cần là thành viên để có thể chia sẻ bài viết</p>
                    <button onclick="joinGroup(<?= $group['ma_nhom'] ?>)" class="group-btn group-btn-primary" style="margin: 0 auto;">
                        <i class="fas fa-user-plus"></i>
                        Tham gia nhóm
                    </button>
                </div>
                <?php elseif (!isLoggedIn()): ?>
                <!-- Thông báo cho người chưa đăng nhập -->
                <div class="empty-state" style="padding: 2rem;">
                    <div class="empty-state-icon" style="font-size: 3rem; margin-bottom: 1rem;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3 class="empty-state-title" style="font-size: 1.125rem;">Đăng nhập để tham gia</h3>
                    <p class="empty-state-text" style="margin-bottom: 1rem;">Bạn cần đăng nhập để xem và đăng bài viết</p>
                    <a href="<?= BASE_URL ?>/login.php" class="group-btn group-btn-primary" style="margin: 0 auto; text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i>
                        Đăng nhập
                    </a>
                </div>
                <?php endif; ?>

                <!-- Posts Feed -->
                <div class="group-posts-feed">
                    <?php if (empty($groupPosts)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="empty-state-title">Chưa có bài viết nào</h3>
                        <p class="empty-state-text">Hãy là người đầu tiên chia sẻ</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($groupPosts as $post): ?>
                    <div class="group-post" data-post-id="<?= $post['ma_bai_viet'] ?>">
                        <div class="post-header">
                            <div class="post-avatar">
                                <?php if (!empty($post['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $post['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(substr($post['nguoi_dang'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="post-user-info">
                                <div class="post-username"><?= sanitize($post['nguoi_dang']) ?></div>
                                <div class="post-time">
                                    <i class="fas fa-clock"></i>
                                    <?= timeAgo($post['ngay_dang']) ?>
                                </div>
                            </div>
                            <?php 
                            $current_user_id = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;
                            if (isLoggedIn() && $current_user_id && $post['ma_nguoi_dang'] == $current_user_id): 
                            ?>
                            <div class="post-menu">
                                <button class="post-menu-btn" onclick="togglePostMenu(<?= $post['ma_bai_viet'] ?>)">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="post-menu-dropdown" id="menu-<?= $post['ma_bai_viet'] ?>">
                                    <div class="post-menu-item" 
                                         data-post-id="<?= $post['ma_bai_viet'] ?>"
                                         data-post-title="<?= htmlspecialchars($post['tieu_de'], ENT_QUOTES) ?>"
                                         data-post-content="<?= htmlspecialchars($post['noi_dung'], ENT_QUOTES) ?>"
                                         onclick="openEditPostModalSafe(this)">
                                        <i class="fas fa-edit"></i>
                                        Chỉnh sửa
                                    </div>
                                    <div class="post-menu-item danger" onclick="deletePost(<?= $post['ma_bai_viet'] ?>)">
                                        <i class="fas fa-trash"></i>
                                        Xóa bài viết
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-content">
                            <?php if (!empty($post['tieu_de'])): ?>
                            <h3 class="post-title"><?= sanitize($post['tieu_de']) ?></h3>
                            <?php endif; ?>
                            <div class="post-text"><?= $post['noi_dung'] ?></div>
                            
                            <?php
                            // Debug: Hiển thị thông tin ảnh và tài liệu
                            if (isset($_GET['debug'])) {
                                echo "<!-- DEBUG POST {$post['ma_bai_viet']} -->";
                                echo "<!-- anh_dinh_kem: " . htmlspecialchars($post['anh_dinh_kem'] ?? 'NULL') . " -->";
                                echo "<!-- tai_lieu_dinh_kem: " . htmlspecialchars($post['tai_lieu_dinh_kem'] ?? 'NULL') . " -->";
                            }
                            
                            // Hiển thị ảnh đính kèm
                            if (!empty($post['anh_dinh_kem'])) {
                                $images = json_decode($post['anh_dinh_kem'], true);
                                if (is_array($images) && count($images) > 0):
                            ?>
                            <div class="post-images" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                                <?php foreach ($images as $image): ?>
                                <div style="position: relative; border-radius: 12px; overflow: hidden; border: 3px solid #1a1a1a; aspect-ratio: 1;">
                                    <img src="<?= BASE_URL ?>/uploads/posts/<?= $image ?>" alt="Post image" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="viewImage('<?= BASE_URL ?>/uploads/posts/<?= $image ?>')" onerror="console.error('Failed to load image:', this.src); this.style.border='3px solid red';">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php 
                                endif;
                            }
                            
                            // Hiển thị tài liệu đính kèm
                            if (!empty($post['tai_lieu_dinh_kem'])) {
                                $files = json_decode($post['tai_lieu_dinh_kem'], true);
                                if (is_array($files) && count($files) > 0):
                            ?>
                            <div class="post-files" style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                                <?php foreach ($files as $file): ?>
                                <a href="<?= BASE_URL ?>/download.php?file=<?= urlencode($file['file']) ?>&type=document" target="_blank" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #E6F4F1; border-radius: 12px; border: 3px solid #1a1a1a; text-decoration: none; transition: all 0.3s;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #00b894, #00cec9); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                                        <i class="fas fa-file-<?= getFileIcon(pathinfo($file['file'], PATHINFO_EXTENSION)) ?>"></i>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 800; color: #1a1a1a; font-size: 0.9375rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= sanitize($file['name']) ?></div>
                                        <div style="font-size: 0.75rem; color: #2d3436; font-weight: 600;"><?= strtoupper(pathinfo($file['file'], PATHINFO_EXTENSION)) ?></div>
                                    </div>
                                    <div style="color: #00b894; font-size: 1.25rem;">
                                        <i class="fas fa-download"></i>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php 
                                endif;
                            }
                            ?>
                        </div>
                        
                        <div class="post-stats">
                            <div class="post-stat">
                                <span>👍</span>
                                <span><?= number_format($post['so_luot_thich']) ?> lượt thích</span>
                            </div>
                            <div class="post-stat">
                                <span>💬</span>
                                <span><?= number_format($post['so_binh_luan']) ?> bình luận</span>
                            </div>
                        </div>
                        
                        <div class="post-actions">
                            <button class="post-action-btn" onclick="likePost(<?= $post['ma_bai_viet'] ?>, this)">
                                <i class="fas fa-thumbs-up"></i>
                                Thích
                            </button>
                            <button class="post-action-btn" onclick="toggleComments(<?= $post['ma_bai_viet'] ?>)">
                                <i class="fas fa-comment"></i>
                                Bình luận
                            </button>
                            <button class="post-action-btn" onclick="sharePost(<?= $post['ma_bai_viet'] ?>)">
                                <i class="fas fa-share"></i>
                                Chia sẻ
                            </button>
                        </div>
                        
                        <!-- Comments Section (Hidden by default) -->
                        <div class="post-comments" id="comments-<?= $post['ma_bai_viet'] ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 3px solid #E6F4F1;">
                            <div class="comment-input" style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                                <div class="composer-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php if (isLoggedIn() && !empty($currentUser['anh_dai_dien'])): ?>
                                    <img src="<?= UPLOAD_PATH ?>avatar/<?= $currentUser['anh_dai_dien'] ?>" alt="">
                                    <?php elseif (isLoggedIn()): ?>
                                    <?= strtoupper(substr($currentUser['ho_ten'] ?? 'U', 0, 1)) ?>
                                    <?php else: ?>
                                    U
                                    <?php endif; ?>
                                </div>
                                <input type="text" placeholder="Viết bình luận..." style="flex: 1; padding: 0.75rem 1rem; border: 3px solid #1a1a1a; border-radius: 50px; font-size: 0.875rem; font-weight: 600;" onkeypress="if(event.key === 'Enter') postComment(<?= $post['ma_bai_viet'] ?>, this)">
                            </div>
                            <div class="comments-list" id="comments-list-<?= $post['ma_bai_viet'] ?>">
                                <!-- Comments will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="group-sidebar">
                <!-- About Card -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="sidebar-card-title">Giới thiệu</h3>
                    </div>
                    
                    <div class="about-item">
                        <div class="about-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="about-content">
                            <div class="about-label">Ngày tạo</div>
                            <div class="about-value"><?= date('d/m/Y', strtotime($group['ngay_tao'])) ?></div>
                        </div>
                    </div>
                    
                    <div class="about-item">
                        <div class="about-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="about-content">
                            <div class="about-label">Người tạo</div>
                            <div class="about-value"><?= sanitize($group['ten_nguoi_tao']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Members Card -->
                <?php if (!empty($recentMembers)): ?>
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="sidebar-card-title">Thành viên mới</h3>
                    </div>
                    
                    <div class="member-list">
                        <?php foreach ($recentMembers as $member): ?>
                        <div class="member-item">
                            <div class="member-avatar">
                                <?php if (!empty($member['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $member['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(substr($member['ho_ten'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="member-info">
                                <div class="member-name"><?= sanitize($member['ho_ten']) ?></div>
                                <div class="member-time"><?= timeAgo($member['ngay_tham_gia']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal-overlay" id="imageViewerModal" onclick="closeImageViewer()">
    <div class="modal-content" style="max-width: 90vw; max-height: 90vh; padding: 0; background: transparent; border: none; box-shadow: none;" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="closeImageViewer()" style="position: absolute; top: 1rem; right: 1rem; z-index: 10;">
            <i class="fas fa-times"></i>
        </button>
        <img id="viewerImage" src="" alt="Full size" style="max-width: 100%; max-height: 90vh; border-radius: 12px; border: 4px solid #1a1a1a; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
    </div>
</div>

<!-- Modal Tạo/Sửa Bài Viết -->
<div class="modal-overlay" id="postModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Tạo bài viết mới</h2>
            <button class="modal-close" onclick="closePostModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="postForm" enctype="multipart/form-data">
                <input type="hidden" id="postId" value="">
                <div class="form-group">
                    <label class="form-label">Tiêu đề (tùy chọn)</label>
                    <input type="text" class="form-input" id="postTitle" placeholder="Nhập tiêu đề bài viết...">
                </div>
                <div class="form-group">
                    <label class="form-label">Nội dung <span style="color: #ef4444;">*</span></label>
                    <textarea class="form-textarea" id="postContent" placeholder="Bạn đang nghĩ gì?" required></textarea>
                </div>
                
                <!-- Upload Ảnh -->
                <div class="form-group">
                    <input type="file" id="postImages" accept="image/*" multiple style="display: none;" onchange="handleImageSelect(event)">
                    <button type="button" class="upload-btn" onclick="document.getElementById('postImages').click()">
                        <i class="fas fa-image"></i>
                        Thêm ảnh
                    </button>
                    <div id="imagePreview" class="file-preview"></div>
                </div>
                
                <!-- Upload Tài liệu -->
                <div class="form-group">
                    <input type="file" id="postFiles" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" multiple style="display: none;" onchange="handleFileSelect(event)">
                    <button type="button" class="upload-btn" onclick="document.getElementById('postFiles').click()">
                        <i class="fas fa-file-alt"></i>
                        Thêm tài liệu
                    </button>
                    <div id="filePreview" class="file-preview"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="closePostModal()">
                <i class="fas fa-times"></i>
                Hủy
            </button>
            <button type="button" class="modal-btn modal-btn-primary" onclick="submitPost(event)">
                <i class="fas fa-paper-plane"></i>
                Đăng bài
            </button>
        </div>
    </div>
</div>

<script>
const groupId = <?= $groupId ?>;

console.log('Group detail script loaded, groupId:', groupId);

// View image in modal
function viewImage(src) {
    const modal = document.getElementById('imageViewerModal');
    const img = document.getElementById('viewerImage');
    img.src = src;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close image viewer
function closeImageViewer() {
    const modal = document.getElementById('imageViewerModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Biến lưu trữ files
let selectedImages = [];
let selectedFiles = [];

// Mở modal tạo bài viết
function openCreatePostModal(type = 'text') {
    console.log('openCreatePostModal called with type:', type);
    
    const modal = document.getElementById('postModal');
    if (!modal) {
        console.error('Modal element not found!');
        showToast('Không tìm thấy modal!', 'error');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Tạo bài viết mới';
    document.getElementById('postId').value = '';
    document.getElementById('postTitle').value = '';
    document.getElementById('postContent').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('filePreview').innerHTML = '';
    selectedImages = [];
    selectedFiles = [];
    
    // Hiển thị modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Tự động mở file picker nếu click vào nút Ảnh hoặc Tài liệu
    setTimeout(() => {
        if (type === 'image') {
            document.getElementById('postImages').click();
        } else if (type === 'file') {
            document.getElementById('postFiles').click();
        }
    }, 300);
    
    console.log('Modal opened successfully');
}

// Xử lý chọn ảnh
function handleImageSelect(event) {
    const files = Array.from(event.target.files);
    
    files.forEach(file => {
        if (!file.type.startsWith('image/')) {
            showToast('Chỉ chấp nhận file ảnh!', 'error');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) { // 5MB
            showToast('Ảnh không được vượt quá 5MB!', 'error');
            return;
        }
        
        selectedImages.push(file);
        
        // Tạo preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const item = document.createElement('div');
            item.className = 'preview-item';
            item.innerHTML = `
                <img src="${e.target.result}" class="preview-image" alt="Preview">
                <button type="button" class="preview-remove" onclick="removeImage(${selectedImages.length - 1})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
    
    // Reset input để có thể chọn lại cùng file
    event.target.value = '';
}

// Xử lý chọn tài liệu
function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) { // 10MB
            showToast('Tài liệu không được vượt quá 10MB!', 'error');
            return;
        }
        
        selectedFiles.push(file);
        
        // Tạo preview
        const preview = document.getElementById('filePreview');
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `
            <div class="preview-file">
                <div class="preview-file-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="preview-file-info">
                    <div class="preview-file-name">${file.name}</div>
                    <div class="preview-file-size">${formatFileSize(file.size)}</div>
                </div>
            </div>
            <button type="button" class="preview-remove" onclick="removeFile(${selectedFiles.length - 1})">
                <i class="fas fa-times"></i>
            </button>
        `;
        preview.appendChild(item);
    });
    
    // Reset input
    event.target.value = '';
}

// Xóa ảnh
function removeImage(index) {
    selectedImages.splice(index, 1);
    updateImagePreview();
}

// Xóa file
function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFilePreview();
}

// Cập nhật preview ảnh
function updateImagePreview() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const item = document.createElement('div');
            item.className = 'preview-item';
            item.innerHTML = `
                <img src="${e.target.result}" class="preview-image" alt="Preview">
                <button type="button" class="preview-remove" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}

// Cập nhật preview file
function updateFilePreview() {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `
            <div class="preview-file">
                <div class="preview-file-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="preview-file-info">
                    <div class="preview-file-name">${file.name}</div>
                    <div class="preview-file-size">${formatFileSize(file.size)}</div>
                </div>
            </div>
            <button type="button" class="preview-remove" onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        preview.appendChild(item);
    });
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Mở modal sửa bài viết (safe version - lấy data từ attribute)
function openEditPostModalSafe(element) {
    const postId = element.getAttribute('data-post-id');
    const title = element.getAttribute('data-post-title');
    const content = element.getAttribute('data-post-content');
    
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa bài viết';
    document.getElementById('postId').value = postId;
    document.getElementById('postTitle').value = title;
    document.getElementById('postContent').value = content;
    
    // Reset files
    selectedImages = [];
    selectedFiles = [];
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('filePreview').innerHTML = '';
    
    document.getElementById('postModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    togglePostMenu(postId); // Đóng menu
}

// Mở modal sửa bài viết (legacy version)
function openEditPostModal(postId, title, content) {
    // Parse JSON if needed
    if (typeof title === 'string' && title.startsWith('"')) {
        title = JSON.parse(title);
    }
    if (typeof content === 'string' && content.startsWith('"')) {
        content = JSON.parse(content);
    }
    
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa bài viết';
    document.getElementById('postId').value = postId;
    document.getElementById('postTitle').value = title;
    document.getElementById('postContent').value = content;
    
    // Reset files
    selectedImages = [];
    selectedFiles = [];
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('filePreview').innerHTML = '';
    
    // Load existing attachments
    // TODO: Fetch existing images and files from server if needed
    
    document.getElementById('postModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    togglePostMenu(postId); // Đóng menu
}

// Đóng modal
function closePostModal() {
    const modal = document.getElementById('postModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Reset form và files
    selectedImages = [];
    selectedFiles = [];
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('filePreview').innerHTML = '';
}

// Đóng modal khi click bên ngoài
document.getElementById('postModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePostModal();
    }
});

// Toggle menu bài viết
function togglePostMenu(postId) {
    const menu = document.getElementById('menu-' + postId);
    const allMenus = document.querySelectorAll('.post-menu-dropdown');
    
    // Đóng tất cả menu khác
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.remove('show');
        }
    });
    
    // Toggle menu hiện tại
    menu.classList.toggle('show');
}

// Đóng menu khi click bên ngoài
document.addEventListener('click', function(e) {
    if (!e.target.closest('.post-menu')) {
        document.querySelectorAll('.post-menu-dropdown').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Submit bài viết (tạo hoặc sửa)
function submitPost(event) {
    console.log('submitPost called');
    
    const postId = document.getElementById('postId').value;
    const title = document.getElementById('postTitle').value.trim();
    const content = document.getElementById('postContent').value.trim();
    
    if (!content) {
        showToast('Vui lòng nhập nội dung bài viết!', 'error');
        return;
    }
    
    const url = postId 
        ? '<?= BASE_URL ?>/api/update_group_post.php'
        : '<?= BASE_URL ?>/api/create_group_post.php';
    
    // Tạo FormData để gửi cả text và files
    const formData = new FormData();
    
    if (postId) {
        formData.append('ma_bai_viet', postId);
    } else {
        formData.append('ma_nhom', groupId);
    }
    
    formData.append('tieu_de', title);
    formData.append('noi_dung', content);
    
    // Thêm ảnh
    selectedImages.forEach((file, index) => {
        formData.append('images[]', file);
    });
    
    // Thêm tài liệu
    selectedFiles.forEach((file, index) => {
        formData.append('files[]', file);
    });
    
    // Hiển thị loading
    const submitBtn = event ? event.target : document.querySelector('.modal-btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng...';
    submitBtn.disabled = true;
    
    console.log('Sending request to:', url);
    console.log('FormData:', {
        ma_nhom: groupId,
        ma_bai_viet: postId || 'new',
        tieu_de: title,
        noi_dung: content,
        images: selectedImages.length,
        files: selectedFiles.length
    });
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast(data.message, 'success');
            closePostModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Đã xảy ra lỗi!', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi khi gửi bài viết!', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Xóa bài viết
function deletePost(postId) {
    if (!confirm('Bạn có chắc muốn xóa bài viết này?')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/api/delete_group_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_bai_viet: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Xóa bài viết khỏi DOM
            const postElement = document.querySelector(`[data-post-id="${postId}"]`);
            if (postElement) {
                postElement.style.opacity = '0';
                postElement.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    postElement.remove();
                    // Kiểm tra nếu không còn bài viết nào
                    const postsContainer = document.querySelector('.group-posts-feed');
                    if (postsContainer && postsContainer.children.length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        } else {
            showToast(data.message || 'Không thể xóa bài viết!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
    });
}

// Hàm tham gia nhóm
function joinGroup(groupId) {
    console.log('joinGroup called with groupId:', groupId);
    
    if (!confirm('Bạn có muốn tham gia nhóm này?')) {
        return;
    }
    
    console.log('Sending request to join group...');
    
    fetch('<?= BASE_URL ?>/api/join_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_nhom: groupId })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast(data.message || 'Tham gia nhóm thành công!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Không thể tham gia nhóm!', 'error');
        }
    })
    .catch(error => {
        console.error('Error joining group:', error);
        showToast('Đã xảy ra lỗi!', 'error');
    });
}

// Hàm rời nhóm
function leaveGroup(groupId) {
    if (!confirm('Bạn có chắc muốn rời khỏi nhóm này?')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/api/leave_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_nhom: groupId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Đã rời khỏi nhóm!', 'success');
            setTimeout(() => {
                window.location.href = '<?= BASE_URL ?>/learning_groups.php';
            }, 1000);
        } else {
            showToast(data.message || 'Không thể rời nhóm!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
    });
}

// Hàm thích bài viết
function likePost(postId, button) {
    console.log('likePost called for post:', postId);
    
    if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
        showToast('Vui lòng đăng nhập để thích bài viết!', 'error');
        return;
    }
    
    // Disable button
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    fetch('<?= BASE_URL ?>/api/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_bai_viet: postId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Like response:', data);
        if (data.success) {
            showToast(data.message, 'success');
            // Update UI
            if (data.action === 'liked') {
                button.style.color = '#00b894';
                button.style.fontWeight = '900';
            } else {
                button.style.color = '';
                button.style.fontWeight = '';
            }
            // Reload to update count
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Không thể thích bài viết!', 'error');
        }
        button.innerHTML = originalHTML;
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Toggle comments section
function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadComments(postId);
    } else {
        commentsSection.style.display = 'none';
    }
}

// Load comments
function loadComments(postId) {
    const commentsList = document.getElementById('comments-list-' + postId);
    commentsList.innerHTML = '<div style="text-align: center; padding: 1rem; color: #2d3436; font-weight: 600;"><i class="fas fa-spinner fa-spin"></i> Đang tải bình luận...</div>';
    
    fetch('<?= BASE_URL ?>/api/get_comments.php?ma_bai_viet=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments.length > 0) {
                const currentUserId = <?= isLoggedIn() ? ($_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? 'null') : 'null' ?>;
                
                commentsList.innerHTML = data.comments.map(comment => {
                    const isOwner = currentUserId && currentUserId == comment.ma_nguoi_dung;
                    const hasReplies = comment.replies && comment.replies.length > 0;
                    
                    return `
                    <div class="comment-item" style="margin-bottom: 1rem;">
                        <div style="display: flex; gap: 0.75rem; padding: 0.875rem; background: #E6F4F1; border-radius: 12px; border: 3px solid #1a1a1a;">
                            <div class="composer-avatar" style="width: 36px; height: 36px; font-size: 0.875rem;">
                                ${comment.anh_dai_dien ? `<img src="<?= UPLOAD_PATH ?>avatar/${comment.anh_dai_dien}" alt="">` : comment.ho_ten.charAt(0).toUpperCase()}
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <div style="font-weight: 800; color: #1a1a1a; font-size: 0.875rem; margin-bottom: 0.25rem;">${comment.ho_ten}</div>
                                        <div class="comment-content-${comment.ma_binh_luan}" style="color: #2d3436; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">${comment.noi_dung}</div>
                                    </div>
                                    ${isOwner ? `
                                    <div class="post-menu" style="position: relative;">
                                        <button class="post-menu-btn" style="width: 28px; height: 28px; font-size: 0.75rem;" onclick="toggleCommentMenu(${comment.ma_binh_luan})">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <div class="post-menu-dropdown" id="comment-menu-${comment.ma_binh_luan}" style="min-width: 150px;">
                                            <div class="post-menu-item" onclick="editComment(${comment.ma_binh_luan}, \`${comment.noi_dung.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)">
                                                <i class="fas fa-edit"></i>
                                                Sửa
                                            </div>
                                            <div class="post-menu-item danger" onclick="deleteComment(${comment.ma_binh_luan}, ${postId})">
                                                <i class="fas fa-trash"></i>
                                                Xóa
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <div style="color: #636e72; font-size: 0.75rem; font-weight: 600;">${comment.thoi_gian}</div>
                                    <button onclick="toggleReplyInput(${comment.ma_binh_luan})" style="color: #00b894; font-size: 0.75rem; font-weight: 800; background: none; border: none; cursor: pointer; padding: 0;">
                                        Trả lời
                                    </button>
                                    ${hasReplies ? `<span style="color: #636e72; font-size: 0.75rem; font-weight: 600;">${comment.replies.length} phản hồi</span>` : ''}
                                </div>
                                
                                <!-- Reply Input (Hidden) -->
                                <div id="reply-input-${comment.ma_binh_luan}" style="display: none; margin-top: 0.75rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <input type="text" placeholder="Viết phản hồi..." style="flex: 1; padding: 0.625rem 1rem; border: 3px solid #1a1a1a; border-radius: 50px; font-size: 0.8125rem; font-weight: 600;" onkeypress="if(event.key === 'Enter') postReply(${postId}, ${comment.ma_binh_luan}, this)">
                                        <button onclick="toggleReplyInput(${comment.ma_binh_luan})" style="padding: 0.625rem 1rem; background: #ffffff; border: 3px solid #1a1a1a; border-radius: 50px; font-size: 0.8125rem; font-weight: 800; cursor: pointer;">Hủy</button>
                                    </div>
                                </div>
                                
                                <!-- Replies -->
                                ${hasReplies ? `
                                <div style="margin-top: 0.75rem; padding-left: 1rem; border-left: 3px solid #00b894;">
                                    ${comment.replies.map(reply => {
                                        const isReplyOwner = currentUserId && currentUserId == reply.ma_nguoi_dung;
                                        return `
                                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; padding: 0.75rem; background: #ffffff; border-radius: 10px; border: 2px solid #1a1a1a;">
                                            <div class="composer-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                ${reply.anh_dai_dien ? `<img src="<?= UPLOAD_PATH ?>avatar/${reply.anh_dai_dien}" alt="">` : reply.ho_ten.charAt(0).toUpperCase()}
                                            </div>
                                            <div style="flex: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                                    <div>
                                                        <div style="font-weight: 800; color: #1a1a1a; font-size: 0.8125rem; margin-bottom: 0.25rem;">${reply.ho_ten}</div>
                                                        <div class="comment-content-${reply.ma_binh_luan}" style="color: #2d3436; font-size: 0.8125rem; font-weight: 600;">${reply.noi_dung}</div>
                                                    </div>
                                                    ${isReplyOwner ? `
                                                    <div class="post-menu" style="position: relative;">
                                                        <button class="post-menu-btn" style="width: 24px; height: 24px; font-size: 0.625rem;" onclick="toggleCommentMenu(${reply.ma_binh_luan})">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <div class="post-menu-dropdown" id="comment-menu-${reply.ma_binh_luan}" style="min-width: 130px;">
                                                            <div class="post-menu-item" onclick="editComment(${reply.ma_binh_luan}, \`${reply.noi_dung.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)">
                                                                <i class="fas fa-edit"></i>
                                                                Sửa
                                                            </div>
                                                            <div class="post-menu-item danger" onclick="deleteComment(${reply.ma_binh_luan}, ${postId})">
                                                                <i class="fas fa-trash"></i>
                                                                Xóa
                                                            </div>
                                                        </div>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                                <div style="color: #636e72; font-size: 0.6875rem; margin-top: 0.25rem; font-weight: 600;">${reply.thoi_gian}</div>
                                            </div>
                                        </div>
                                        `;
                                    }).join('')}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    `;
                }).join('');
            } else {
                commentsList.innerHTML = '<div style="text-align: center; padding: 1rem; color: #636e72; font-weight: 600;">Chưa có bình luận nào</div>';
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<div style="text-align: center; padding: 1rem; color: #ef4444; font-weight: 600;">Không thể tải bình luận</div>';
        });
}

// Toggle reply input
function toggleReplyInput(commentId) {
    const replyInput = document.getElementById('reply-input-' + commentId);
    if (replyInput.style.display === 'none') {
        replyInput.style.display = 'block';
        replyInput.querySelector('input').focus();
    } else {
        replyInput.style.display = 'none';
    }
}

// Post reply
function postReply(postId, parentCommentId, input) {
    const content = input.value.trim();
    if (!content) return;
    
    if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
        showToast('Vui lòng đăng nhập để trả lời!', 'error');
        return;
    }
    
    input.disabled = true;
    
    fetch('<?= BASE_URL ?>/api/post_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ma_bai_viet: postId,
            ma_binh_luan_cha: parentCommentId,
            noi_dung: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            toggleReplyInput(parentCommentId);
            loadComments(postId);
            showToast('Đã trả lời bình luận!', 'success');
        } else {
            showToast(data.message || 'Không thể trả lời!', 'error');
        }
        input.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
        input.disabled = false;
    });
}

// Toggle comment menu
function toggleCommentMenu(commentId) {
    const menu = document.getElementById('comment-menu-' + commentId);
    const allMenus = document.querySelectorAll('.post-menu-dropdown');
    
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.remove('show');
        }
    });
    
    menu.classList.toggle('show');
}

// Edit comment
function editComment(commentId, currentContent) {
    const contentElement = document.querySelector('.comment-content-' + commentId);
    
    // Store original content for cancel
    contentElement.dataset.originalContent = currentContent;
    
    // Create edit form
    const editForm = document.createElement('div');
    editForm.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
    
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentContent;
    input.id = 'edit-input-' + commentId;
    input.style.cssText = 'flex: 1; padding: 0.5rem 0.75rem; border: 2px solid #00b894; border-radius: 8px; font-size: 0.875rem; font-weight: 600;';
    
    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Lưu';
    saveBtn.style.cssText = 'padding: 0.5rem 0.75rem; background: #00b894; color: white; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 0.75rem; font-weight: 800; cursor: pointer;';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Hủy';
    cancelBtn.style.cssText = 'padding: 0.5rem 0.75rem; background: #ffffff; color: #2d3436; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 0.75rem; font-weight: 800; cursor: pointer;';
    
    // Add event listeners
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveEditComment(commentId);
        }
    });
    
    saveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        saveEditComment(commentId);
    });
    
    cancelBtn.addEventListener('click', function(e) {
        e.preventDefault();
        cancelEditComment(commentId);
    });
    
    // Append elements
    editForm.appendChild(input);
    editForm.appendChild(saveBtn);
    editForm.appendChild(cancelBtn);
    
    // Replace content with edit form
    contentElement.innerHTML = '';
    contentElement.appendChild(editForm);
    
    input.focus();
    input.select();
    
    toggleCommentMenu(commentId);
}

// Save edit comment
function saveEditComment(commentId) {
    const input = document.getElementById('edit-input-' + commentId);
    if (!input) {
        console.error('Input not found for comment:', commentId);
        return;
    }
    
    const newContent = input.value.trim();
    
    if (!newContent) {
        showToast('Nội dung không được để trống!', 'error');
        return;
    }
    
    const contentElement = document.querySelector('.comment-content-' + commentId);
    const originalContent = contentElement.dataset.originalContent;
    
    // Disable input and buttons
    input.disabled = true;
    const container = input.parentElement;
    const buttons = container.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading on save button
    const saveBtn = buttons[0];
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    console.log('Saving comment:', commentId, 'Content:', newContent);
    
    fetch('<?= BASE_URL ?>/api/edit_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ma_binh_luan: commentId,
            noi_dung: newContent
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            contentElement.innerHTML = newContent;
            delete contentElement.dataset.originalContent;
            showToast('Đã cập nhật bình luận!', 'success');
        } else {
            showToast(data.message || 'Không thể cập nhật!', 'error');
            // Restore edit form
            input.disabled = false;
            buttons.forEach(btn => btn.disabled = false);
            saveBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error saving comment:', error);
        showToast('Đã xảy ra lỗi: ' + error.message, 'error');
        // Restore original content on error
        if (originalContent) {
            contentElement.innerHTML = originalContent;
            delete contentElement.dataset.originalContent;
        }
    });
}

// Cancel edit comment
function cancelEditComment(commentId) {
    const contentElement = document.querySelector('.comment-content-' + commentId);
    const originalContent = contentElement.dataset.originalContent;
    
    if (originalContent) {
        contentElement.innerHTML = originalContent;
        delete contentElement.dataset.originalContent;
    }
}

// Delete comment
function deleteComment(commentId, postId) {
    if (!confirm('Bạn có chắc muốn xóa bình luận này?')) {
        return;
    }
    
    toggleCommentMenu(commentId);
    
    fetch('<?= BASE_URL ?>/api/delete_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_binh_luan: commentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadComments(postId);
            showToast('Đã xóa bình luận!', 'success');
        } else {
            showToast(data.message || 'Không thể xóa!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
    });
}

// Post comment
function postComment(postId, input) {
    const content = input.value.trim();
    if (!content) return;
    
    if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
        showToast('Vui lòng đăng nhập để bình luận!', 'error');
        return;
    }
    
    input.disabled = true;
    
    fetch('<?= BASE_URL ?>/api/post_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ma_bai_viet: postId,
            noi_dung: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(postId);
            showToast('Đã đăng bình luận!', 'success');
        } else {
            showToast(data.message || 'Không thể đăng bình luận!', 'error');
        }
        input.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã xảy ra lỗi!', 'error');
        input.disabled = false;
    });
}

// Share post
function sharePost(postId) {
    const url = window.location.origin + window.location.pathname + '?id=<?= $groupId ?>&post=' + postId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Chia sẻ bài viết',
            url: url
        }).then(() => {
            showToast('Đã chia sẻ!', 'success');
        }).catch(err => {
            console.log('Share failed:', err);
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showToast('Đã sao chép liên kết bài viết!', 'success');
        }).catch(err => {
            console.error('Copy failed:', err);
            showToast('Không thể sao chép liên kết!', 'error');
        });
    }
}

// Hàm chia sẻ nhóm
function shareGroup() {
    const url = window.location.href;
    const title = '<?= addslashes($group['ten_nhom']) ?>';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).then(() => {
            showToast('Đã chia sẻ!', 'success');
        }).catch(err => {
            console.log('Share failed:', err);
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showToast('Đã sao chép liên kết!', 'success');
        }).catch(err => {
            console.error('Copy failed:', err);
        });
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-message ${type} show`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
