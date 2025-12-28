<?php
/**
 * Danh mục diễn đàn - Forum Category (Unified Design)
 */
require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/dien-dan.php');

$pdo = getDBConnection();

// Kiểm tra bảng tồn tại
$tableExists = $pdo->query("SHOW TABLES LIKE 'danh_muc_dien_dan'")->rowCount() > 0;
if (!$tableExists) redirect(BASE_URL . '/dien-dan.php');

// Lấy thông tin danh mục
$stmt = $pdo->prepare("SELECT * FROM danh_muc_dien_dan WHERE ma_danh_muc = ? AND trang_thai = 'hien_thi'");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) redirect(BASE_URL . '/dien-dan.php', 'Danh mục không tồn tại', 'warning');

// Lấy tên và mô tả theo ngôn ngữ hiện tại
$isKhmer = getCurrentLang() === 'km';
$categoryName = $isKhmer && !empty($category['ten_danh_muc_km']) ? $category['ten_danh_muc_km'] : $category['ten_danh_muc'];
$categoryDesc = $isKhmer && !empty($category['mo_ta_km']) ? $category['mo_ta_km'] : $category['mo_ta'];

$pageTitle = $categoryName;

// Phân trang
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Đếm tổng
$total = $pdo->query("SELECT COUNT(*) FROM chu_de_thao_luan WHERE ma_danh_muc = $id")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Lấy chủ đề
$threads = $pdo->query("SELECT cd.*, nd.ho_ten, nd.anh_dai_dien, nd.tong_diem,
    (SELECT ho_ten FROM nguoi_dung WHERE ma_nguoi_dung = (SELECT ma_nguoi_dung FROM bai_viet_dien_dan WHERE ma_chu_de = cd.ma_chu_de ORDER BY ngay_tao DESC LIMIT 1)) as nguoi_tra_loi_cuoi,
    (SELECT ngay_tao FROM bai_viet_dien_dan WHERE ma_chu_de = cd.ma_chu_de ORDER BY ngay_tao DESC LIMIT 1) as thoi_gian_tra_loi_cuoi,
    (SELECT COUNT(*) FROM bai_viet_dien_dan WHERE ma_chu_de = cd.ma_chu_de) as so_tra_loi
    FROM chu_de_thao_luan cd
    JOIN nguoi_dung nd ON cd.ma_nguoi_tao = nd.ma_nguoi_dung
    WHERE cd.ma_danh_muc = $id
    ORDER BY cd.ghim DESC, cd.ngay_tao DESC
    LIMIT $perPage OFFSET $offset")->fetchAll();

// Lấy tất cả danh mục để hiển thị sidebar
$allCategories = $pdo->query("SELECT dm.*, 
    (SELECT COUNT(*) FROM chu_de_thao_luan WHERE ma_danh_muc = dm.ma_danh_muc) as so_chu_de
    FROM danh_muc_dien_dan dm WHERE dm.trang_thai = 'hien_thi' ORDER BY dm.thu_tu")->fetchAll();

// Thống kê danh mục hiện tại
$categoryStats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM chu_de_thao_luan WHERE ma_danh_muc = $id) as tong_chu_de,
    (SELECT COUNT(*) FROM bai_viet_dien_dan bv JOIN chu_de_thao_luan cd ON bv.ma_chu_de = cd.ma_chu_de WHERE cd.ma_danh_muc = $id) as tong_bai_viet")->fetch();

?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Category Page Unified Design ===== */
:root {
    --cat-primary: <?= $category['mau_sac'] ?>;
}

.category-page {
    min-height: 100vh;
    background: #ffffff;
}

/* Hero Section */
.category-hero {
    min-height: 40vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

.category-hero-content {
    position: relative;
    z-index: 2;
    color: #000000;
}

/* Breadcrumb */
.category-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.category-breadcrumb a {
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}

.category-breadcrumb a:hover { color: #000000; }
.category-breadcrumb .separator { color: #cbd5e1; }

/* Hero Header */
.category-hero-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.category-hero-icon {
    width: 80px;
    height: 80px;
    background: var(--cat-primary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
}

.category-hero-info h1 {
    font-size: clamp(1.75rem, 4vw, 3rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #000000 !important;
}

.category-hero-desc {
    font-size: 1rem;
    color: #64748b;
    max-width: 600px;
    line-height: 1.6;
    font-weight: 600;
}

/* Hero Stats */
.category-hero-stats {
    display: flex;
    gap: 3rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.hero-stat-card {
    text-align: center;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 2px solid #000000;
}

.hero-stat-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.hero-stat-number {
    font-size: 2rem;
    font-weight: 900;
    display: block;
    line-height: 1.2;
    color: #000000;
}

.hero-stat-label {
    font-size: 0.875rem;
    color: #000000;
    font-weight: 700;
}

/* Main Content */
.category-main {
    padding: 2rem 0;
    background: #ffffff;
    min-height: 60vh;
}

.category-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}
</style>


<style>
/* Threads Section */
.threads-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 2px solid #000000;
}

.threads-header {
    padding: 1.5rem;
    background: #ffffff;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.threads-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.threads-header h2 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #000000;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin: 0;
}

.threads-header h2 i { color: #f59e0b; }

.threads-count {
    background: #f8fafc;
    color: #000000;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 700;
    border: 1px solid #e2e8f0;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
}

.filter-tab {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: #64748b;
    background: transparent;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-tab:hover { 
    background: #f8fafc;
    border-color: #e2e8f0;
}

.filter-tab.active {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
}

/* Thread List */
.threads-list {
    padding: 0.5rem;
}

/* Thread Card - Timeline Style */
.thread-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    margin: 0.5rem;
    border-radius: 12px;
    background: white;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    border: 2px solid #e2e8f0;
}

.thread-card:hover {
    background: #f8fafc;
    border-color: #000000;
    transform: translateX(5px);
}

/* Thread Avatar */
.thread-avatar {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: var(--cat-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.thread-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Thread Content */
.thread-content {
    flex: 1;
    min-width: 0;
}

.thread-badges {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.thread-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.thread-badge.pinned {
    background: #fef3c7;
    color: #f59e0b;
    border: 1px solid #fde68a;
}

.thread-badge.locked {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.thread-badge.hot {
    background: #fee2e2;
    color: #ef4444;
    border: 1px solid #fecaca;
}

.thread-badge.new {
    background: #d1fae5;
    color: #10b981;
    border: 1px solid #a7f3d0;
}

.thread-title {
    font-size: 1.0625rem;
    font-weight: 900;
    color: #000000;
    line-height: 1.4;
    margin-bottom: 0.625rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s;
}

.thread-card:hover .thread-title { color: var(--cat-primary); }

.thread-excerpt {
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-weight: 600;
}

/* Thread Meta */
.thread-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #64748b;
    flex-wrap: wrap;
    font-weight: 600;
}

.thread-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.thread-meta-item i { 
    font-size: 0.75rem;
    color: #f59e0b;
}

.thread-author {
    color: var(--cat-primary);
    font-weight: 700;
}

/* Thread Stats */
.thread-stats {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex-shrink: 0;
    min-width: 100px;
    text-align: center;
}

.thread-stat {
    background: #f8fafc;
    padding: 0.75rem;
    border-radius: 12px;
    transition: all 0.3s;
    border: 1px solid #e2e8f0;
}

.thread-card:hover .thread-stat {
    background: #ffffff;
    border-color: var(--cat-primary);
}

.thread-stat-number {
    font-size: 1.25rem;
    font-weight: 900;
    color: #000000;
    display: block;
    line-height: 1.2;
}

.thread-stat-label {
    font-size: 0.6875rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}

/* Last Reply Info */
.thread-last-reply {
    background: #f8fafc;
    padding: 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    color: #64748b;
    text-align: left;
    border: 1px solid #e2e8f0;
    font-weight: 600;
}

.thread-last-reply strong {
    color: #000000;
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 900;
}

/* Sidebar */
.category-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    position: sticky;
    top: 100px;
}

.sidebar-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 2px solid #000000;
}

.sidebar-card-header {
    padding: 1.25rem;
    background: #ffffff;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 900;
    color: #000000;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-size: 0.9375rem;
}

.sidebar-card-header i { color: #f59e0b; }

.sidebar-card-body { padding: 1rem; }

/* New Thread Button */
.btn-new-thread {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    width: 100%;
    padding: 1.125rem;
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-new-thread:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px);
}

.btn-new-thread i { font-size: 1.125rem; }

/* Category Nav */
.category-nav-list {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.category-nav-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
    border: 1px solid transparent;
}

.category-nav-item:hover {
    background: #f8fafc;
    transform: translateX(5px);
    border-color: #e2e8f0;
}

.category-nav-item.active {
    background: #f8fafc;
    border-color: var(--cat-primary);
}

.category-nav-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9375rem;
    flex-shrink: 0;
}

.category-nav-info {
    flex: 1;
    min-width: 0;
}

.category-nav-name {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #000000;
    margin-bottom: 0.125rem;
}

.category-nav-count {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
}

/* Quick Stats Card */
.quick-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.quick-stat {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.quick-stat-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.quick-stat-number {
    font-size: 1.375rem;
    font-weight: 900;
    color: #000000;
    display: block;
}

.quick-stat-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 700;
}

/* Empty State */
.threads-empty {
    text-align: center;
    padding: 4rem 2rem;
}

.threads-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    background: #f8fafc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #f59e0b;
    border: 2px solid #e2e8f0;
}

.threads-empty h3 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.5rem;
}

.threads-empty p {
    color: #64748b;
    font-size: 0.9375rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.threads-empty .btn-new-thread {
    display: inline-flex;
    width: auto;
    padding: 1rem 2rem;
}

/* Pagination */
.pagination-wrapper {
    padding: 1.5rem;
    border-top: 2px solid #e2e8f0;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.625rem 1rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn.prev,
.pagination-btn.next {
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
}

.pagination-btn.prev:hover,
.pagination-btn.next:hover {
    background: #000000;
    color: #ffffff;
}

.pagination-numbers {
    display: flex;
    gap: 0.375rem;
}

.pagination-num {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s;
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.pagination-num:hover {
    background: #ffffff;
    color: #000000;
    border-color: #000000;
}

.pagination-num.active {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
}

/* Responsive */
@media (max-width: 1024px) {
    .category-grid {
        grid-template-columns: 1fr;
    }
    .category-sidebar {
        position: static;
        order: -1;
    }
    .thread-stats {
        flex-direction: row;
        min-width: auto;
    }
    .thread-stat {
        min-width: 70px;
    }
}

@media (max-width: 768px) {
    .category-hero {
        padding-top: 100px;
    }
    .category-hero-header {
        flex-direction: column;
        text-align: center;
    }
    .category-hero-stats {
        justify-content: center;
        gap: 1.5rem;
    }
    .hero-stat-card {
        min-width: 130px;
    }
    .thread-card {
        flex-direction: column;
    }
    .thread-avatar {
        width: 44px;
        height: 44px;
    }
    .thread-stats {
        flex-direction: row;
        justify-content: flex-start;
    }
    .threads-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .filter-tabs {
        width: 100%;
        overflow-x: auto;
    }
}
</style>


<main class="category-page">
    <!-- Hero Section -->
    <section class="category-hero">
        <div class="container">
            <div class="category-hero-content">
                <!-- Breadcrumb -->
                <nav class="category-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> <?= __('home') ?></a>
                    <span class="separator"><i class="fas fa-chevron-right"></i></span>
                    <a href="<?= BASE_URL ?>/dien-dan.php"><?= __('forum') ?></a>
                    <span class="separator"><i class="fas fa-chevron-right"></i></span>
                    <span><?= sanitize($categoryName) ?></span>
                </nav>

                <!-- Header -->
                <div class="category-hero-header">
                    <div class="category-hero-icon">
                        <i class="<?= $category['icon'] ?>"></i>
                    </div>
                    <div class="category-hero-info">
                        <h1><?= sanitize($categoryName) ?></h1>
                        <p class="category-hero-desc"><?= sanitize($categoryDesc) ?></p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="category-hero-stats">
                    <div class="hero-stat-card">
                        <div class="hero-stat-icon">💬</div>
                        <span class="hero-stat-number"><?= number_format($categoryStats['tong_chu_de']) ?></span>
                        <span class="hero-stat-label"><?= __('topics') ?></span>
                    </div>
                    <div class="hero-stat-card">
                        <div class="hero-stat-icon">📝</div>
                        <span class="hero-stat-number"><?= number_format($categoryStats['tong_bai_viet']) ?></span>
                        <span class="hero-stat-label"><?= __('posts') ?></span>
                    </div>
                    <div class="hero-stat-card">
                        <div class="hero-stat-icon">📄</div>
                        <span class="hero-stat-number"><?= $totalPages ?></span>
                        <span class="hero-stat-label"><?= __('page') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="category-main">
        <div class="container">
            <div class="category-grid">
                <!-- Threads Section -->
                <div class="threads-section">
                    <div class="threads-header">
                        <div class="threads-header-left">
                            <h2><i class="fas fa-list"></i> <?= __('topic_list') ?></h2>
                            <span class="threads-count"><?= number_format($total) ?> <?= __('topic_count') ?></span>
                        </div>
                        <div class="filter-tabs">
                            <button class="filter-tab active"><?= __('newest') ?></button>
                            <button class="filter-tab"><?= __('popular') ?></button>
                            <button class="filter-tab"><?= __('no_replies') ?></button>
                        </div>
                    </div>

                    <?php if (empty($threads)): ?>
                    <div class="threads-empty">
                        <div class="threads-empty-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3><?= __('no_topics_in_category') ?></h3>
                        <p><?= __('be_first_to_create') ?></p>
                        <?php if (isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/tao-chu-de.php?danh_muc=<?= $id ?>" class="btn-new-thread">
                            <i class="fas fa-plus-circle"></i> <?= __('create_new_topic') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="threads-list">
                        <?php foreach ($threads as $thread): 
                            $isNew = (time() - strtotime($thread['ngay_tao'])) < 86400;
                            $isHot = $thread['so_tra_loi'] >= 10 || $thread['luot_xem'] >= 100;
                        ?>
                        <a href="<?= BASE_URL ?>/chu-de.php?id=<?= $thread['ma_chu_de'] ?>" class="thread-card">
                            <div class="thread-avatar">
                                <?php if ($thread['anh_dai_dien']): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $thread['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($thread['ho_ten'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="thread-content">
                                <div class="thread-badges">
                                    <?php if ($thread['ghim']): ?>
                                    <span class="thread-badge pinned"><i class="fas fa-thumbtack"></i> <?= __('pinned') ?></span>
                                    <?php endif; ?>
                                    <?php if ($thread['khoa']): ?>
                                    <span class="thread-badge locked"><i class="fas fa-lock"></i> <?= __('locked') ?></span>
                                    <?php endif; ?>
                                    <?php if ($isHot): ?>
                                    <span class="thread-badge hot"><i class="fas fa-fire"></i> <?= __('hot') ?></span>
                                    <?php endif; ?>
                                    <?php if ($isNew): ?>
                                    <span class="thread-badge new"><i class="fas fa-sparkles"></i> <?= __('new') ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="thread-title"><?= sanitize($thread['tieu_de']) ?></h3>
                                
                                <p class="thread-excerpt"><?= mb_substr(strip_tags($thread['noi_dung']), 0, 150) ?>...</p>
                                
                                <div class="thread-meta">
                                    <span class="thread-meta-item">
                                        <i class="fas fa-user"></i>
                                        <span class="thread-author"><?= sanitize($thread['ho_ten']) ?></span>
                                    </span>
                                    <span class="thread-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <?= timeAgo($thread['ngay_tao']) ?>
                                    </span>
                                    <span class="thread-meta-item">
                                        <i class="fas fa-eye"></i>
                                        <?= number_format($thread['luot_xem']) ?> <?= __('views_count') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="thread-stats">
                                <div class="thread-stat">
                                    <span class="thread-stat-number"><?= number_format($thread['so_tra_loi']) ?></span>
                                    <span class="thread-stat-label"><?= __('replies') ?></span>
                                </div>
                                <?php if ($thread['nguoi_tra_loi_cuoi']): ?>
                                <div class="thread-last-reply">
                                    <strong><?= sanitize($thread['nguoi_tra_loi_cuoi']) ?></strong>
                                    <?= timeAgo($thread['thoi_gian_tra_loi_cuoi']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination-wrapper">
                        <?php if ($page > 1): ?>
                        <a href="?id=<?= $id ?>&page=<?= $page - 1 ?>" class="pagination-btn prev">
                            <i class="fas fa-chevron-left"></i> <?= __('prev') ?>
                        </a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            if ($start > 1): ?>
                            <a href="?id=<?= $id ?>&page=1" class="pagination-num">1</a>
                            <?php if ($start > 2): ?>
                            <span class="pagination-num">...</span>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?id=<?= $id ?>&page=<?= $i ?>" class="pagination-num <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                            <span class="pagination-num">...</span>
                            <?php endif; ?>
                            <a href="?id=<?= $id ?>&page=<?= $totalPages ?>" class="pagination-num"><?= $totalPages ?></a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="?id=<?= $id ?>&page=<?= $page + 1 ?>" class="pagination-btn next">
                            <?= __('next') ?> <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <aside class="category-sidebar">
                    <!-- New Thread Button -->
                    <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/tao-chu-de.php?danh_muc=<?= $id ?>" class="btn-new-thread">
                        <i class="fas fa-plus-circle"></i> <?= __('create_new_topic') ?>
                    </a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn-new-thread">
                        <i class="fas fa-sign-in-alt"></i> <?= __('login_to_join') ?>
                    </a>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-chart-bar"></i> <?= __('quick_stats') ?>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="quick-stats">
                                <div class="quick-stat">
                                    <div class="quick-stat-icon">💬</div>
                                    <span class="quick-stat-number"><?= number_format($categoryStats['tong_chu_de']) ?></span>
                                    <span class="quick-stat-label"><?= __('topics') ?></span>
                                </div>
                                <div class="quick-stat">
                                    <div class="quick-stat-icon">📝</div>
                                    <span class="quick-stat-number"><?= number_format($categoryStats['tong_bai_viet']) ?></span>
                                    <span class="quick-stat-label"><?= __('posts') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Categories -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-th-large"></i> <?= __('other_categories') ?>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="category-nav-list">
                                <?php foreach ($allCategories as $cat): 
                                    $catName = $isKhmer && !empty($cat['ten_danh_muc_km']) ? $cat['ten_danh_muc_km'] : $cat['ten_danh_muc'];
                                ?>
                                <a href="<?= BASE_URL ?>/dien-dan-danh-muc.php?id=<?= $cat['ma_danh_muc'] ?>" 
                                   class="category-nav-item <?= $cat['ma_danh_muc'] == $id ? 'active' : '' ?>">
                                    <div class="category-nav-icon" style="background: <?= $cat['mau_sac'] ?>;">
                                        <i class="<?= $cat['icon'] ?>"></i>
                                    </div>
                                    <div class="category-nav-info">
                                        <div class="category-nav-name"><?= sanitize($catName) ?></div>
                                        <div class="category-nav-count"><?= number_format($cat['so_chu_de']) ?> <?= __('topic_count') ?></div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Forum -->
                    <a href="<?= BASE_URL ?>/dien-dan.php" class="sidebar-card" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; text-decoration: none; color: inherit; transition: all 0.3s;">
                        <div style="width: 44px; height: 44px; background: #000000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        <div>
                            <div style="font-weight: 900; color: #000000;"><?= __('back_to_forum') ?></div>
                            <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600;"><?= __('view_all_categories') ?></div>
                        </div>
                    </a>
                </aside>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
