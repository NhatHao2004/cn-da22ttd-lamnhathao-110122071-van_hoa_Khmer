<?php
/**
 * Truyện dân gian - Modern Redesign
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('nav_stories');

try {
    $pdo = getDBConnection();

    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    // Filters
    $search = sanitize($_GET['search'] ?? '');
    $category = sanitize($_GET['category'] ?? '');

    // Build query - Truy vấn từ bảng van_hoa_khmer.truyen_dan_gian
    $where = "WHERE trang_thai = 'hien_thi'";
    $params = [];

    if ($search) {
        $where .= " AND (tieu_de LIKE ? OR noi_dung LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($category) {
        $where .= " AND the_loai = ?";
        $params[] = $category;
    }

    // Get total
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM truyen_dan_gian $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

    // Get stories
    $stmt = $pdo->prepare("SELECT * FROM truyen_dan_gian $where ORDER BY ngay_tao DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $stories = $stmt->fetchAll();

    // Get categories - Kiểm tra xem cột the_loai có tồn tại không
    try {
        $categories = $pdo->query("SELECT DISTINCT the_loai FROM truyen_dan_gian WHERE the_loai IS NOT NULL AND the_loai != '' ORDER BY the_loai")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Nếu cột the_loai không tồn tại, bỏ qua
        $categories = [];
    }

    // Get stats
    $totalStories = $pdo->query("SELECT COUNT(*) FROM truyen_dan_gian")->fetchColumn() ?: 0;
    $totalViews = $pdo->query("SELECT SUM(luot_xem) FROM truyen_dan_gian")->fetchColumn() ?: 0;
    
    // Get quizzes
    $quizStmt = $pdo->query("SELECT * FROM quiz WHERE trang_thai = 'hoat_dong' ORDER BY ngay_tao DESC LIMIT 6");
    $quizzes = $quizStmt->fetchAll();
} catch (Exception $e) {
    error_log("Error in truyen-dan-gian.php: " . $e->getMessage());
    $stories = [];
    $categories = [];
    $total = 0;
    $totalPages = 0;
    $totalStories = 0;
    $totalViews = 0;
    $quizzes = [];
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Story Page ===== */
.story-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
}

/* ===== Story Hero Section ===== */
.story-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.story-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.story-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.story-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto 1rem;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    flex-wrap: wrap;
}

.hero-stat { 
    text-align: center;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 3px solid #1a1a1a;
    box-shadow: 4px 4px 0px #FF9800;
    transition: all 0.3s ease;
}

.hero-stat:hover {
    transform: translateY(-5px);
    box-shadow: 6px 6px 0px #FF9800;
}

.hero-stat-number { 
    font-size: 2rem; 
    font-weight: 900; 
    display: block;
    color: #FF9800;
}
.hero-stat-label { 
    font-size: 0.875rem;
    color: #1a1a1a;
    font-weight: 700;
}

/* ===== Main Content Area ===== */
.story-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFE4B5 0%, #FFCC80 100%);
}

.story-main .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ===== Filter Bar ===== */
.filter-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.filter-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-search {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.filter-search input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #1a1a1a;
}

.filter-search input:focus {
    outline: none;
    border-color: #FF9800;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
}

.filter-search input::placeholder {
    color: #666;
}

.filter-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FF9800;
    font-weight: 600;
    pointer-events: none;
}

.filter-select {
    min-width: 160px;
}

.filter-select select {
    width: 100%;
    padding: 0.875rem 2.5rem 0.875rem 1rem;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23FF9800' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 1rem center;
    appearance: none;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #1a1a1a;
}

.filter-select select:focus {
    outline: none;
    border-color: #FF9800;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
}

.filter-btn {
    padding: 0.875rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.filter-btn:hover {
    background: #F57C00;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.filter-reset {
    padding: 0.875rem 1.25rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 3px 3px 0px #FF9800;
}

.filter-reset:hover {
    background: #1a1a1a;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #FF9800;
}

/* Active Filters */
.active-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #FFE4B5;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #FFE4B5;
    color: #1a1a1a;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 600;
    border: 2px solid #FF9800;
}

.filter-tag i {
    cursor: pointer;
    color: #FF9800;
    transition: all 0.2s;
}

.filter-tag i:hover {
    color: #F57C00;
    transform: scale(1.2);
}
</style>

<style>
/* ===== Story Cards Grid ===== */
.story-grid-section {
    margin-bottom: 3rem;
}

.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
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
}

.results-count {
    font-size: 0.9375rem;
    color: #1a1a1a;
    font-weight: 700;
    background: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    border: 3px solid #1a1a1a;
}

.results-count strong {
    color: #FF9800;
    font-weight: 900;
}

/* Story Cards Grid - Table Layout */
.story-cards-grid {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 4px 4px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
}

/* Table Header */
.story-table-header {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-bottom: 3px solid #1a1a1a;
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a1a;
}

.story-table-header-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.story-table-header-cell i {
    color: #FF9800;
    font-size: 1.125rem;
}

/* Table Body */
.story-table-body {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    position: relative;
    margin-bottom: 0;
}

/* Story Card - Table Row */
.story-card {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-bottom: 2px solid #FFE4B5;
    transition: all 0.3s ease;
    text-decoration: none;
    align-items: center;
    border-radius: 0;
    height: auto;
    box-shadow: none;
}

.story-card:last-child {
    border-bottom: none;
}

.story-card:hover {
    background: #ffffff;
    transform: translateX(5px);
    box-shadow: none;
}

/* Image Column */
.story-card-image {
    position: relative;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #1a1a1a;
    box-shadow: 4px 4px 0px #FF9800;
}

.story-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.story-card:hover .story-card-image img {
    transform: scale(1.1);
}

.story-card-placeholder {
    font-size: 2.5rem;
    color: rgba(26, 26, 26, 0.2);
}

/* Content Column */
.story-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0;
    margin-top: -4rem;
}

.story-card-content-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.story-card-meta-row {
    font-size: 0.875rem;
    color: #2d2d2d;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.story-card-category,
.story-card-views {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    background: #FFE4B5;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    border: 2px solid #FF9800;
}

.story-card-category i {
    color: #FF9800;
    font-size: 1rem;
}

.story-card-views i {
    color: #FF9800;
    font-size: 1rem;
}

.story-card-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #1a1a1a !important;
    margin: 0 !important;
    line-height: 1.4 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.story-card-excerpt {
    display: none;
}

/* Action Column */
.story-card-btn-wrapper {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 0.5rem 0;
    margin-bottom: -7rem;
}

.story-card-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    width: 100%;
    margin: 0;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.story-card-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.story-card-btn i {
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.story-card-btn:hover i {
    transform: translateX(3px);
}

/* Remove old overlay styles */
.story-card-overlay,
.story-card-info,
.story-card-left,
.story-card-right,
.story-card-meta {
    display: none;
}
</style>


<style>
/* ===== Pagination ===== */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #ffffff;
    padding: 0.75rem;
    border-radius: 16px;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.pagination a,
.pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 0.75rem;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination a {
    color: #1a1a1a;
    background: #FFE4B5;
    border: 2px solid #1a1a1a;
}

.pagination a:hover {
    background: #FF9800;
    color: #ffffff;
    transform: translateY(-2px);
}

.pagination .active {
    background: #FF9800;
    color: #ffffff;
    border: 2px solid #1a1a1a;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.pagination .disabled {
    color: #999;
    background: transparent;
    cursor: not-allowed;
    border: 2px solid transparent;
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #FF9800;
    border: 3px solid #1a1a1a;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.empty-state-desc {
    font-size: 1rem;
    color: #2d2d2d;
    margin-bottom: 2rem;
    max-width: 100%;
    line-height: 1.7;
    white-space: nowrap;
}

.empty-state-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: #FF9800;
    color: #ffffff;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 3px solid #1a1a1a;
    box-shadow: 4px 4px 0px #1a1a1a;
}

.empty-state-btn:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px #1a1a1a;
    color: #ffffff;
    background: #F57C00;
}

/* ===== Responsive ===== */
@media (max-width: 1200px) {
    .story-cards-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .story-cards-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    /* Table Header - Stack on mobile */
    .story-table-header {
        display: none;
    }
    
    /* Table Body */
    .story-cards-grid {
        border-radius: 12px;
    }
    
    .story-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 0;
    }
    
    .story-card:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .story-card:last-child {
        border-radius: 0 0 12px 12px;
    }
    
    .story-card-image {
        width: 100%;
        height: 180px;
    }
    
    .story-card-body {
        gap: 0.75rem;
        margin-top: 0;
    }
    
    .story-card-title {
        font-size: 1rem !important;
        -webkit-line-clamp: 2 !important;
    }
    
    .story-card-meta-row {
        font-size: 0.8125rem;
        gap: 0.75rem;
    }
    
    .story-card-btn-wrapper {
        width: 100%;
        padding: 0;
        margin-bottom: 0;
    }
    
    .story-card-btn {
        width: 100%;
        padding: 0.875rem 1.25rem;
    }
    
    .filter-form {
        flex-direction: column;
    }
    .filter-search {
        width: 100%;
    }
    .filter-select {
        width: 100%;
    }
    .hero-stats {
        gap: 1.5rem;
    }
    .story-hero-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .story-card {
        padding: 1rem;
    }
    
    .story-card-image {
        height: 160px;
        border-radius: 10px;
    }
    
    .story-card-title {
        font-size: 0.9375rem !important;
    }
    
    .story-card-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

/* Tablet - Show simplified table */
@media (min-width: 769px) and (max-width: 1024px) {
    .story-table-header {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .story-card {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }
    
    .story-card-image {
        height: 120px;
    }
    
    .story-card-title {
        font-size: 1rem !important;
    }
    
    .story-card-btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
}
</style>

<!-- Hero Section -->
<section class="story-hero">
    <div class="container">
        <div class="story-hero-content">
            <h1 class="story-hero-title">📖 <?= __('nav_stories') ?></h1>
            <p class="story-hero-subtitle"><?= __('stories_page_desc') ?></p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="story-main">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="<?= __('search_story') ?>" value="<?= $search ?>">
                </div>
                
                <div class="filter-select">
                    <select name="category">
                        <option value=""><?= __('all_genres') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> <?= __('filter') ?>
                </button>
                
                <?php if ($search || $category): ?>
                <a href="<?= BASE_URL ?>/truyen-dan-gian.php" class="filter-reset">
                    <i class="fas fa-times"></i> <?= __('reset') ?>
                </a>
                <?php endif; ?>
            </form>
            
            <?php if ($search || $category): ?>
            <div class="active-filters">
                <?php if ($search): ?>
                <span class="filter-tag">
                    <i class="fas fa-search"></i> "<?= $search ?>"
                    <a href="?category=<?= urlencode($category) ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
                <?php if ($category): ?>
                <span class="filter-tag">
                    <i class="fas fa-tag"></i> <?= $category ?>
                    <a href="?search=<?= urlencode($search) ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Story Grid -->
        <div class="story-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    <?= __('all_stories') ?? 'Tất cả truyện dân gian' ?>
                </h2>
                <span class="results-count">
                    <?= __('showing') ?> <strong><?= count($stories) ?></strong> / <strong><?= $total ?></strong> <?= __('results') ?>
                </span>
            </div>
            
            <?php if (!empty($stories)): ?>
            <div class="story-cards-grid">
                <!-- Table Header -->
                <div class="story-table-header">
                    <div class="story-table-header-cell">
                        Hình truyện dân gian
                    </div>
                    <div class="story-table-header-cell">
                        Tên truyện dân gian
                    </div>
                    <div class="story-table-header-cell">
                        Nút thao tác
                    </div>
                </div>
                
                <!-- Table Body -->
                <div class="story-table-body">
                <?php foreach ($stories as $story): 
                    // Xử lý đường dẫn ảnh
                    $imgPath = $story['anh_dai_dien'];
                    if ($imgPath) {
                        if (strpos($imgPath, 'uploads/') === 0) {
                            $imgUrl = '/DoAn_ChuyenNganh/' . $imgPath;
                        } else {
                            $imgUrl = UPLOAD_PATH . 'truyendangian/' . $imgPath;
                        }
                    } else {
                        $imgUrl = '';
                    }
                ?>
                <div class="timeline-item">
                    <a href="<?= BASE_URL ?>/truyen-chi-tiet.php?id=<?= $story['ma_truyen'] ?>" class="story-card">
                        <!-- Image Column -->
                        <div class="story-card-image">
                            <?php if ($imgUrl): ?>
                            <img src="<?= $imgUrl ?>" alt="<?= sanitize($story['tieu_de']) ?>">
                            <?php else: ?>
                            <i class="fas fa-book-open story-card-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Content Column -->
                        <div class="story-card-body">
                            <div class="story-card-content-left">
                                <div class="story-card-meta-row">
                                    <?php if (!empty($story['the_loai'])): ?>
                                    <span class="story-card-category">
                                        <i class="fas fa-tag"></i>
                                        <?= $story['the_loai'] ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="story-card-views">
                                        <i class="far fa-eye"></i>
                                        <?= formatNumber($story['luot_xem']) ?>
                                    </span>
                                </div>
                                <h3 class="story-card-title"><?= sanitize($story['tieu_de']) ?></h3>
                            </div>
                        </div>
                        
                        <!-- Action Column -->
                        <div class="story-card-btn-wrapper">
                            <span class="story-card-btn">
                                Xem thêm <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <!-- Pagination -->
            <div class="pagination-wrapper">
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php else: ?>
                    <span class="disabled prev"><i class="fas fa-chevron-left"></i></span>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    <?php if ($startPage > 2): ?>
                    <span class="disabled">...</span>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                    <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                    <span class="disabled">...</span>
                    <?php endif; ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="disabled next"><i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="empty-state-title"><?= __('no_results') ?></h3>
                <p class="empty-state-desc"><?= __('try_different') ?></p>
                <a href="<?= BASE_URL ?>/truyen-dan-gian.php" class="empty-state-btn">
                    <i class="fas fa-redo"></i> <?= __('view_all') ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>



<style>
/* ===== Quiz Section ===== */
.quiz-section {
    padding: 4rem 0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

.quiz-header {
    text-align: center;
    margin-bottom: 3rem;
}

.quiz-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.quiz-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.quiz-subtitle {
    font-size: 1rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

.quiz-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.quiz-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: block;
    border: 2px solid transparent;
}

.quiz-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(102, 126, 234, 0.15);
    border-color: rgba(102, 126, 234, 0.2);
}

.quiz-card-header {
    padding: 1.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    position: relative;
    overflow: hidden;
}

.quiz-card-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.quiz-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.quiz-card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

.quiz-card-body {
    padding: 1.5rem 1.75rem;
}

.quiz-meta {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
    font-size: 0.8125rem;
    color: #64748b;
}

.quiz-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.quiz-meta-item i {
    color: #667eea;
}

.quiz-description {
    font-size: 0.9375rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.quiz-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.25rem;
    border-top: 1px solid #e2e8f0;
}

.quiz-difficulty {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.quiz-difficulty.easy {
    background: rgba(67, 233, 123, 0.1);
    color: #22c55e;
}

.quiz-difficulty.medium {
    background: rgba(251, 191, 36, 0.1);
    color: #f59e0b;
}

.quiz-difficulty.hard {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.quiz-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.quiz-card:hover .quiz-btn {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.quiz-view-all {
    text-align: center;
    margin-top: 3rem;
}

.btn-view-all-quiz {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2.5rem;
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
}

.btn-view-all-quiz:hover {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

/* ===== Quiz Empty State ===== */
.quiz-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.08);
    border: 2px dashed rgba(102, 126, 234, 0.2);
}

.quiz-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #667eea;
    animation: pulse-icon 2s ease-in-out infinite;
}

@keyframes pulse-icon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.quiz-empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.quiz-empty-desc {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.quiz-empty-features {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.quiz-empty-feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9375rem;
    color: #64748b;
}

.quiz-empty-feature i {
    color: #22c55e;
    font-size: 1.125rem;
}

@media (max-width: 1024px) {
    .quiz-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .quiz-grid {
        grid-template-columns: 1fr;
    }
    .quiz-title {
        font-size: 1.5rem;
    }
    .quiz-empty-state {
        padding: 3rem 1.5rem;
    }
    .quiz-empty-features {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
