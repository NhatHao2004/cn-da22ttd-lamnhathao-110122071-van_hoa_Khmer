<?php
/**
 * Diễn đàn cộng đồng - Forum (Redesigned)
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('forum_title') ?? 'Diễn đàn cộng đồng';

// Lấy ngôn ngữ hiện tại
$currentLang = getCurrentLang();
$isKhmer = ($currentLang === 'km');

$pdo = getDBConnection();

// Kiểm tra bảng tồn tại
$tableExists = $pdo->query("SHOW TABLES LIKE 'danh_muc_dien_dan'")->rowCount() > 0;

$categories = [];
$latestThreads = [];
$stats = ['tong_chu_de' => 0, 'tong_bai_viet' => 0, 'tong_thanh_vien' => 0];

if ($tableExists) {
    $categories = $pdo->query("SELECT dd.*, 
        (SELECT COUNT(*) FROM chu_de_thao_luan WHERE ma_danh_muc = dd.ma_danh_muc) as so_chu_de,
        (SELECT COUNT(*) FROM bai_viet_dien_dan bv JOIN chu_de_thao_luan cd ON bv.ma_chu_de = cd.ma_chu_de WHERE cd.ma_danh_muc = dd.ma_danh_muc) as so_bai_viet
        FROM danh_muc_dien_dan dd WHERE dd.trang_thai = 'hien_thi' ORDER BY dd.thu_tu")->fetchAll();

    $latestThreads = $pdo->query("SELECT cd.*, nd.ho_ten, nd.anh_dai_dien, dm.ten_danh_muc, dm.ten_danh_muc_km, dm.mau_sac
        FROM chu_de_thao_luan cd
        JOIN nguoi_dung nd ON cd.ma_nguoi_tao = nd.ma_nguoi_dung
        JOIN danh_muc_dien_dan dm ON cd.ma_danh_muc = dm.ma_danh_muc
        ORDER BY cd.ngay_tao DESC LIMIT 5")->fetchAll();

    $stats = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM chu_de_thao_luan) as tong_chu_de,
        (SELECT COUNT(*) FROM bai_viet_dien_dan) as tong_bai_viet,
        (SELECT COUNT(*) FROM nguoi_dung WHERE trang_thai = 'hoat_dong') as tong_thanh_vien")->fetch();
}

// Top contributors
$topUsers = $pdo->query("SELECT ho_ten, anh_dai_dien, tong_diem FROM nguoi_dung WHERE trang_thai = 'hoat_dong' ORDER BY tong_diem DESC LIMIT 5")->fetchAll();

// Learning groups from database
$learningGroups = [];
$groupTableExists = $pdo->query("SHOW TABLES LIKE 'nhom_hoc_tap'")->rowCount() > 0;
if ($groupTableExists) {
    $learningGroups = $pdo->query("SELECT nh.*, 
        (SELECT COUNT(*) FROM thanh_vien_nhom WHERE ma_nhom = nh.ma_nhom AND trang_thai = 'hoat_dong') as so_thanh_vien
        FROM nhom_hoc_tap nh 
        WHERE nh.trang_thai = 'hoat_dong' 
        ORDER BY nh.thu_tu ASC 
        LIMIT 3")->fetchAll();
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<?php if (isset($_GET['msg']) && isset($_GET['type'])): ?>
<div class="alert-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;">
    <?php if ($_GET['type'] === 'success'): ?>
    <div style="display: flex; align-items: center; gap: 1rem; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1.25rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
        <span style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($_GET['msg']) ?></span>
    </div>
    <?php else: ?>
    <div style="display: flex; align-items: center; gap: 1rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1.25rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
        <span style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($_GET['msg']) ?></span>
    </div>
    <?php endif; ?>
</div>
<script>
// Xóa query string khỏi URL ngay lập tức để tránh hiển thị lại khi reload
if (window.history.replaceState) {
    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({path: cleanUrl}, '', cleanUrl);
}

// Ẩn thông báo sau 3 giây
setTimeout(() => {
    const alert = document.querySelector('.alert-container');
    if (alert) {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }
}, 3000);
</script>
<style>
.alert-container { animation: slideIn 0.3s ease; }
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(400px); opacity: 0; }
}
</style>
<?php endif; ?>

<style>
/* ===== Forum Page - Unified Design ===== */
.forum-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
}

/* Hero Section - Unified Style */
.forum-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.forum-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.forum-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.forum-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 700px;
    margin: 0 auto 1rem;
    line-height: 1.6;
}

/* Stats Cards - Unified Style */
.forum-stats-row {
    display: flex;
    justify-content: center;
    gap: 3rem;
    flex-wrap: wrap;
}

.forum-stat-card {
    text-align: center;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 3px solid #1a1a1a;
    box-shadow: 4px 4px 0px #FF9800;
    transition: all 0.3s ease;
}

.forum-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 6px 6px 0px #FF9800;
}

.forum-stat-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.forum-stat-number {
    font-size: 2rem;
    font-weight: 900;
    display: block;
    line-height: 1.2;
    color: #FF9800;
}

.forum-stat-label {
    font-size: 0.875rem;
    color: #1a1a1a;
    font-weight: 700;
}

/* Main Content - Three Column Layout */
.forum-main {
    padding: 2.5rem 0;
    background: linear-gradient(180deg, #FFE4B5 0%, #FFCC80 100%);
    min-height: 60vh;
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid #1a1a1a;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title i {
    color: #FF9800;
    font-size: 1.25rem;
}

/* Three Column Grid Layout */
.forum-grid {
    display: grid;
    grid-template-columns: 280px 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

/* Left Sidebar - Categories & Groups */
.forum-left-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Center Content - Main Discussions */
.forum-center-content {
    min-height: 500px;
}

/* Right Sidebar - Activities */
.forum-right-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Category Cards - Compact Style for Left Sidebar */
.categories-compact {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.category-compact-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 2px 2px 0px #FF9800;
    transition: all 0.3s ease;
    border: 2px solid #1a1a1a;
    text-decoration: none;
    color: inherit;
}

.category-compact-card:hover {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0px #FF9800;
    border-color: #1a1a1a;
}

.category-compact-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.category-compact-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    color: white;
    flex-shrink: 0;
}

.category-compact-name {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.3;
}

.category-compact-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #2d2d2d;
    padding-left: 44px;
    font-weight: 600;
}

.category-compact-stat {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.category-compact-stat i {
    color: #FF9800;
}

/* Discussion Posts - Social Media Style */
.discussions-feed {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.discussion-post {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 4px 4px 0px #1a1a1a;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    border: 3px solid #1a1a1a;
}

.discussion-post:hover {
    box-shadow: 6px 6px 0px #FF9800;
    transform: translate(-2px, -2px);
    border-color: #1a1a1a;
}

.discussion-header {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    margin-bottom: 1rem;
}

.discussion-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    overflow: hidden;
    border: 3px solid #1a1a1a;
    flex-shrink: 0;
}

.discussion-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.discussion-user-info {
    flex: 1;
}

.discussion-username {
    font-weight: 700;
    color: #1a1a1a;
    font-size: 0.9375rem;
    margin-bottom: 0.125rem;
}

.discussion-meta {
    font-size: 0.75rem;
    color: #2d2d2d;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}

.discussion-category-tag {
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-size: 0.6875rem;
    font-weight: 700;
    color: white;
}

.discussion-content {
    margin-bottom: 1rem;
}

.discussion-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.discussion-excerpt {
    font-size: 0.9375rem;
    color: #2d2d2d;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-align: justify;
}

.discussion-actions {
    display: flex;
    gap: 1.5rem;
    padding-top: 0.75rem;
    border-top: 2px solid #FFE4B5;
}

.discussion-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #2d2d2d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    background: none;
    border: none;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
}

.discussion-action-btn:hover {
    background: #FFE4B5;
    color: #1a1a1a;
}

.discussion-action-btn i {
    color: #FF9800;
}

/* Sidebar Cards - Unified Style */
.sidebar-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 4px 4px 0px #1a1a1a;
    overflow: hidden;
    border: 3px solid #1a1a1a;
}

.sidebar-card-header {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-bottom: 3px solid #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sidebar-card-header i {
    color: #FF9800;
    font-size: 1.125rem;
}

.sidebar-card-header h3 {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.sidebar-card-body {
    padding: 1rem;
}

/* New Thread Button - Call to Action */
.btn-new-thread {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    padding: 1rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 4px 4px 0px #1a1a1a;
}

.btn-new-thread:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px #1a1a1a;
}

.btn-new-thread i {
    font-size: 1.125rem;
}

/* Learning Groups - Compact Cards */
.learning-groups {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.group-compact-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 0.875rem;
    box-shadow: 2px 2px 0px #FF9800;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    border: 2px solid #1a1a1a;
}

.group-compact-card:hover {
    box-shadow: 4px 4px 0px #FF9800;
    transform: translate(-2px, -2px);
    border-color: #1a1a1a;
}

.group-compact-header {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 0.5rem;
}

.group-compact-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    color: white;
    border: 2px solid #1a1a1a;
}

.group-compact-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.3;
}

.group-compact-members {
    font-size: 0.75rem;
    color: #2d2d2d;
    padding-left: 40px;
    font-weight: 600;
}

/* Latest Threads - Activity Feed */
.latest-thread {
    display: block;
    padding: 0.875rem;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    margin-bottom: 0.5rem;
    border: 2px solid transparent;
}

.latest-thread:last-child {
    margin-bottom: 0;
}

.latest-thread:hover {
    background: #FFF6E5;
    border-color: #FF9800;
}

.latest-thread-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.latest-thread-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.6875rem;
    color: #2d2d2d;
    font-weight: 600;
}

.latest-thread-category {
    padding: 0.25rem 0.5rem;
    border-radius: 50px;
    font-size: 0.625rem;
    font-weight: 700;
    color: white;
}

/* Top Contributors - Leaderboard Style */
.contributor-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.contributor-item:hover {
    background: #FFF6E5;
    border-color: #FF9800;
}

.contributor-rank {
    width: 28px;
    font-weight: 800;
    font-size: 0.875rem;
    color: #2d2d2d;
    text-align: center;
}

.contributor-rank.gold { color: #FF9800; }
.contributor-rank.silver { color: #a8a8a8; }
.contributor-rank.bronze { color: #cd7f32; }

.contributor-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    overflow: hidden;
    border: 2px solid #1a1a1a;
}

.contributor-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contributor-info {
    flex: 1;
    min-width: 0;
}

.contributor-name {
    font-weight: 700;
    color: #1a1a1a;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.contributor-points {
    font-size: 0.75rem;
    color: #FF9800;
    font-weight: 700;
}

/* Community Stats Widget */
.community-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.stat-item {
    text-align: center;
    padding: 0.875rem;
    background: #ffffff;
    border-radius: 10px;
    border: 2px solid #1a1a1a;
}

.stat-item-icon {
    font-size: 1.25rem;
    color: #FF9800;
    margin-bottom: 0.375rem;
}

.stat-item-number {
    font-size: 1.25rem;
    font-weight: 800;
    color: #FF9800;
    display: block;
}

.stat-item-label {
    font-size: 0.75rem;
    color: #2d2d2d;
    font-weight: 600;
}

/* Empty State */
.forum-empty {
    text-align: center;
    padding: 4rem 2rem;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.forum-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #FF9800;
    border: 3px solid #1a1a1a;
}

.forum-empty h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.forum-empty p {
    color: #2d2d2d;
    margin-bottom: 2rem;
    font-weight: 500;
    font-size: 1rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .forum-grid {
        grid-template-columns: 260px 1fr 300px;
        gap: 1.25rem;
    }
}

@media (max-width: 1024px) {
    .forum-grid {
        grid-template-columns: 240px 1fr 280px;
        gap: 1rem;
    }
    
    .forum-hero {
        min-height: 30vh;
        padding-top: 100px;
    }
}

@media (max-width: 900px) {
    .forum-grid {
        grid-template-columns: 1fr;
    }
    
    .forum-left-sidebar,
    .forum-right-sidebar {
        display: none;
    }
    
    .forum-center-content {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .forum-hero {
        min-height: 35vh;
        padding-top: 100px;
        padding-bottom: 20px;
    }
    
    .forum-hero-title {
        font-size: 2rem;
    }
    
    .forum-hero-subtitle {
        font-size: 1rem;
    }
    
    .forum-stats-row {
        gap: 1.5rem;
    }
    
    .forum-stat-card {
        padding: 1.25rem 1.5rem;
        min-width: 100px;
    }
    
    .forum-stat-number {
        font-size: 1.75rem;
    }
    
    .discussion-post {
        padding: 1.25rem;
        border-radius: 12px;
    }
    
    .discussion-title {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .forum-main {
        padding: 1.5rem 0;
    }
    
    .discussion-post {
        padding: 1rem;
        border-radius: 10px;
    }
    
    .discussion-header {
        gap: 0.625rem;
    }
    
    .discussion-avatar {
        width: 38px;
        height: 38px;
    }
    
    .discussion-actions {
        gap: 1rem;
    }
    
    .discussion-action-btn {
        font-size: 0.8125rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>


<main class="forum-page">
    <!-- Hero Section -->
    <section class="forum-hero">
        <div class="container">
            <div class="forum-hero-content">
                <h1 class="forum-hero-title">💬 <?= __('forum_title') ?? 'Diễn đàn Cộng đồng' ?></h1>
                <p class="forum-hero-subtitle"><?= __('forum_subtitle') ?? 'Nơi kết nối, chia sẻ kiến thức và thảo luận về văn hóa Khmer Nam Bộ' ?></p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="forum-main">
        <div class="container">
            <div class="forum-grid">
                <!-- Left Sidebar - Categories & Groups -->
                <aside class="forum-left-sidebar">
                    <!-- Categories Compact -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-th-large"></i>
                            <h3>Danh mục thảo luận</h3>
                        </div>
                        <div class="sidebar-card-body">
                            <?php if (empty($categories)): ?>
                            <p style="text-align: center; color: #8b7355; padding: 1rem; font-size: 0.875rem;"><?= __('no_categories') ?? 'Chưa có danh mục' ?></p>
                            <?php else: ?>
                            <div class="categories-compact">
                                <?php foreach ($categories as $cat): ?>
                                <a href="<?= BASE_URL ?>/dien-dan-danh-muc.php?id=<?= $cat['ma_danh_muc'] ?>" class="category-compact-card">
                                    <div class="category-compact-header">
                                        <div class="category-compact-icon" style="background: <?= $cat['mau_sac'] ?>;">
                                            <i class="<?= $cat['icon'] ?>"></i>
                                        </div>
                                        <div class="category-compact-name"><?= sanitize($isKhmer && !empty($cat['ten_danh_muc_km']) ? $cat['ten_danh_muc_km'] : $cat['ten_danh_muc']) ?></div>
                                    </div>
                                    <div class="category-compact-stats">
                                        <span class="category-compact-stat">
                                            <i class="fas fa-comments"></i> <?= number_format($cat['so_chu_de']) ?>
                                        </span>
                                        <span class="category-compact-stat">
                                            <i class="fas fa-reply-all"></i> <?= number_format($cat['so_bai_viet']) ?>
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- New Thread Button -->
                    <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/tao-chu-de.php" class="btn-new-thread">
                        <i class="fas fa-plus-circle"></i> Tạo chủ đề mới
                    </a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn-new-thread">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để tạo chủ đề
                    </a>
                    <?php endif; ?>
                </aside>

                <!-- Center Content - Main Discussions Feed -->
                <div class="forum-center-content">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-comments"></i> Thảo luận gần đây</h2>
                    </div>
                    
                    <?php if (empty($latestThreads)): ?>
                    <div class="forum-empty">
                        <div class="forum-empty-icon"><i class="fas fa-comments"></i></div>
                        <h3>Chưa có thảo luận nào</h3>
                        <p>Hãy là người đầu tiên bắt đầu cuộc thảo luận!</p>
                        <?php if (isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/tao-chu-de.php" class="btn-new-thread" style="max-width: 300px; margin: 0 auto;">
                            <i class="fas fa-plus-circle"></i> Tạo chủ đề mới
                        </a>
                        <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="btn-new-thread" style="max-width: 300px; margin: 0 auto;">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập để tham gia
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="discussions-feed">
                        <?php foreach ($latestThreads as $thread): ?>
                        <a href="<?= BASE_URL ?>/chu-de.php?id=<?= $thread['ma_chu_de'] ?>" class="discussion-post">
                            <div class="discussion-header">
                                <div class="discussion-avatar">
                                    <?php if ($thread['anh_dai_dien']): ?>
                                    <img src="<?= UPLOAD_PATH ?>avatar/<?= $thread['anh_dai_dien'] ?>" alt="">
                                    <?php else: ?>
                                    <?= strtoupper(substr($thread['ho_ten'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="discussion-user-info">
                                    <div class="discussion-username"><?= sanitize($thread['ho_ten']) ?></div>
                                    <div class="discussion-meta">
                                        <span class="discussion-category-tag" style="background: <?= $thread['mau_sac'] ?>;">
                                            <?= sanitize($isKhmer && !empty($thread['ten_danh_muc_km']) ? $thread['ten_danh_muc_km'] : $thread['ten_danh_muc']) ?>
                                        </span>
                                        <span>•</span>
                                        <span><?= timeAgo($thread['hoat_dong_cuoi']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="discussion-content">
                                <h3 class="discussion-title"><?= sanitize($thread['tieu_de']) ?></h3>
                                <p class="discussion-excerpt"><?= mb_substr(strip_tags($thread['noi_dung'] ?? ''), 0, 150) ?>...</p>
                            </div>
                            <div class="discussion-actions">
                                <button class="discussion-action-btn">
                                    <i class="fas fa-thumbs-up"></i> Thích
                                </button>
                                <button class="discussion-action-btn">
                                    <i class="fas fa-comment"></i> Bình luận
                                </button>
                                <button class="discussion-action-btn">
                                    <i class="fas fa-share"></i> Chia sẻ
                                </button>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Sidebar - Activities & Stats -->
                <aside class="forum-right-sidebar">
                    <!-- Community Stats -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-chart-line"></i>
                            <h3>Thống kê cộng đồng</h3>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="community-stats">
                                <div class="stat-item">
                                    <div class="stat-item-icon">💬</div>
                                    <span class="stat-item-number"><?= number_format($stats['tong_chu_de']) ?></span>
                                    <span class="stat-item-label">Chủ đề</span>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-item-icon">📝</div>
                                    <span class="stat-item-number"><?= number_format($stats['tong_bai_viet']) ?></span>
                                    <span class="stat-item-label">Bài viết</span>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-item-icon">👥</div>
                                    <span class="stat-item-number"><?= number_format($stats['tong_thanh_vien']) ?></span>
                                    <span class="stat-item-label">Thành viên</span>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-item-icon">🔥</div>
                                    <span class="stat-item-number"><?= number_format(rand(50, 150)) ?></span>
                                    <span class="stat-item-label">Trực tuyến</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Contributors -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-trophy"></i>
                            <h3>Thành viên tích cực</h3>
                        </div>
                        <div class="sidebar-card-body">
                            <?php foreach ($topUsers as $rank => $user): ?>
                            <div class="contributor-item">
                                <span class="contributor-rank <?= $rank === 0 ? 'gold' : ($rank === 1 ? 'silver' : ($rank === 2 ? 'bronze' : '')) ?>">
                                    <?= $rank === 0 ? '🥇' : ($rank === 1 ? '🥈' : ($rank === 2 ? '🥉' : '#' . ($rank + 1))) ?>
                                </span>
                                <div class="contributor-avatar">
                                    <?php if ($user['anh_dai_dien']): ?>
                                    <img src="<?= UPLOAD_PATH ?>avatar/<?= $user['anh_dai_dien'] ?>" alt="">
                                    <?php else: ?>
                                    <?= strtoupper(substr($user['ho_ten'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="contributor-info">
                                    <div class="contributor-name"><?= sanitize($user['ho_ten']) ?></div>
                                    <div class="contributor-points"><?= number_format($user['tong_diem']) ?> điểm</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>


                </aside>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
