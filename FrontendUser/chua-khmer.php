<?php
/**
 * Danh sách chùa Khmer - Modern Redesign
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('temple_page_title');

try {
    $pdo = getDBConnection();

    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    // Filters
    $search = sanitize($_GET['search'] ?? '');
    $province = sanitize($_GET['province'] ?? '');
    $view = sanitize($_GET['view'] ?? 'grid');

    // Build query - Không filter theo trang_thai nếu cột không tồn tại
    $where = "WHERE 1=1";
    $params = [];

    if ($search) {
        $where .= " AND (ten_chua LIKE ? OR dia_chi LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($province) {
        $where .= " AND tinh_thanh = ?";
        $params[] = $province;
    }

    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM chua_khmer $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);

    // Get items
    $stmt = $pdo->prepare("SELECT * FROM chua_khmer $where ORDER BY ngay_tao DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $temples = $stmt->fetchAll();

    // Get provinces
    $provinces = $pdo->query("SELECT DISTINCT tinh_thanh FROM chua_khmer WHERE tinh_thanh IS NOT NULL AND tinh_thanh != '' ORDER BY tinh_thanh")->fetchAll(PDO::FETCH_COLUMN);

    // Get stats
    $totalTemples = $pdo->query("SELECT COUNT(*) FROM chua_khmer")->fetchColumn() ?: 0;
    $totalProvinces = count($provinces);

    // Get featured temple
    $featuredStmt = $pdo->query("SELECT * FROM chua_khmer ORDER BY ngay_tao DESC LIMIT 1");
    $featured = $featuredStmt->fetch();
    
    // Get quizzes
    $quizStmt = $pdo->query("SELECT * FROM quiz WHERE trang_thai = 'hoat_dong' ORDER BY ngay_tao DESC LIMIT 6");
    $quizzes = $quizStmt->fetchAll();
} catch (Exception $e) {
    error_log("Error in chua-khmer.php: " . $e->getMessage());
    $temples = [];
    $provinces = [];
    $featured = null;
    $total = 0;
    $totalPages = 0;
    $totalTemples = 0;
    $totalProvinces = 0;
    $quizzes = [];
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Temple Hero Section ===== */
.temple-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 3px solid rgba(255, 255, 255, 0.3);
}

.temple-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.temple-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.temple-hero-subtitle {
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
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.2);
    transition: all 0.3s ease;
}
.hero-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
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
</style>


<style>
/* ===== Main Content Area ===== */
.temple-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFEDD5 100%);
    min-height: 60vh;
}

/* ===== Filter Bar ===== */
.filter-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.15);
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
    border: 2px solid #1a1a1a;
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

.filter-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FF9800;
    font-weight: 600;
}

.filter-select {
    min-width: 160px;
}

.filter-select select {
    width: 100%;
    padding: 0.875rem 2.5rem 0.875rem 1rem;
    border: 2px solid #1a1a1a;
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
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
}

.filter-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255, 152, 0, 0.4);
}

.filter-reset {
    padding: 0.875rem 1.25rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.filter-reset:hover {
    background: #1a1a1a;
    color: #ffffff;
    transform: translateY(-2px);
}

/* View Toggle */
.view-toggle {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    width: 44px;
    height: 44px;
    border: 2px solid #1a1a1a;
    border-radius: 12px;
    background: #ffffff;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-weight: 700;
}

.view-btn:hover {
    background: #ffffff;
    border-color: rgba(255, 255, 255, 0.5);
    color: #FF9800;
}

.view-btn.active {
    background: #FF9800;
    border-color: #1a1a1a;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
}

/* Active Filters */
.active-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 700;
}

.filter-tag i {
    cursor: pointer;
    color: #FF9800;
    transition: opacity 0.2s;
}

.filter-tag i:hover {
    opacity: 0.7;
}
</style>

<style>
/* ===== Temple Cards Grid ===== */
.temple-grid-section {
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
    color: #2d2d2d;
    font-weight: 700;
}

.results-count strong {
    color: #FF9800;
    font-weight: 900;
}

/* Temple Cards Grid - Table Layout */
.temple-cards-grid {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 25px rgba(255, 152, 0, 0.15);
    border: 3px solid #1a1a1a;
}

/* Table Header */
.temple-table-header {
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

.temple-table-header-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.temple-table-header-cell i {
    color: #FF9800;
    font-size: 1.125rem;
}

/* Table Body */
.temple-table-body {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    position: relative;
    margin-bottom: 0;
}

/* Temple Card - Table Row */
.temple-card {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    text-decoration: none;
    align-items: center;
    border-radius: 0;
    height: auto;
    box-shadow: none;
}

.temple-card:last-child {
    border-bottom: none;
}

.temple-card:hover {
    background: #ffffff;
    transform: none;
    box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.3);
}

/* Image Column */
.temple-card-image {
    position: relative;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    flex-shrink: 0;
    border: 2px solid #1a1a1a;
}

.temple-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.temple-card:hover .temple-card-image img {
    transform: scale(1.1);
}

/* Content Column */
.temple-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0;
    margin-top: -3rem;
}

.temple-card-content-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.temple-card-meta-row {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.temple-card-year,
.temple-card-location {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.temple-card-year i {
    color: #FF9800;
    font-size: 1rem;
}

.temple-card-location i {
    color: #FF6F00;
    font-size: 1rem;
}

.temple-card-title {
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

.temple-card-title-khmer {
    font-size: 1rem;
    font-weight: 500;
    color: #666666;
    font-family: 'Battambang', 'Khmer OS Siemreap', 'Noto Sans Khmer', cursive;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
    line-height: 1.5;
}

/* Action Column */
.temple-card-btn-wrapper {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 0.5rem 0;
    margin-bottom: -7rem;
}

.temple-card-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border: 2px solid #1a1a1a;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    width: 100%;
    margin: 0;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
}

.temple-card-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255, 152, 0, 0.4);
}

.temple-card-btn i {
    color: inherit;
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.temple-card-btn:hover i {
    transform: translateX(3px);
}

/* Remove old styles */
.temple-card-overlay,
.temple-card-info,
.temple-card-left,
.temple-card-name,
.temple-card-name-khmer,
.temple-card-right,
.temple-card-province,
.temple-card-badge {
    display: none;
}
</style>


<style>
/* ===== Map Section ===== */
.map-section {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 2.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
}

.map-container {
    height: 500px;
    width: 100%;
}

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
    background: white;
    padding: 0.75rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
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
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination a {
    color: #64748b;
    background: #f8fafc;
}

.pagination a:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
}

.pagination .active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.pagination .disabled {
    color: #cbd5e1;
    background: transparent;
    cursor: not-allowed;
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #f59e0b;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.75rem;
}

.empty-state-desc {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 2rem;
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.7;
    white-space: nowrap;
}

.empty-state-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.empty-state-btn:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-3px);
}

/* ===== Responsive ===== */
@media (max-width: 1200px) {
    .temple-cards-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .temple-cards-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    /* Table Header - Stack on mobile */
    .temple-table-header {
        display: none;
    }
    
    /* Table Body */
    .temple-cards-grid {
        border-radius: 12px;
    }
    
    .temple-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 0;
    }
    
    .temple-card:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .temple-card:last-child {
        border-radius: 0 0 12px 12px;
    }
    
    .temple-card-image {
        width: 100%;
        height: 180px;
    }
    
    .temple-card-body {
        gap: 0.75rem;
        margin-top: 0;
    }
    
    .temple-card-title {
        font-size: 1rem !important;
        -webkit-line-clamp: 2 !important;
    }
    
    .temple-card-title-khmer {
        font-size: 0.875rem;
    }
    
    .temple-card-meta-row {
        font-size: 0.8125rem;
        gap: 0.75rem;
    }
    
    .temple-card-btn-wrapper {
        width: 100%;
        padding: 0;
        margin-bottom: 0;
    }
    
    .temple-card-btn {
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
    .temple-hero-title {
        font-size: 1.75rem;
    }
    .map-container {
        height: 350px;
    }
}

@media (max-width: 480px) {
    .temple-card {
        padding: 1rem;
    }
    
    .temple-card-image {
        height: 160px;
        border-radius: 10px;
    }
    
    .temple-card-title {
        font-size: 0.9375rem !important;
    }
    
    .temple-card-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

/* Tablet - Show simplified table */
@media (min-width: 769px) and (max-width: 1024px) {
    .temple-table-header {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .temple-card {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }
    
    .temple-card-image {
        height: 120px;
    }
    
    .temple-card-title {
        font-size: 1rem !important;
    }
    
    .temple-card-btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
}
</style>

<!-- Hero Section -->
<section class="temple-hero">
    <div class="container">
        <div class="temple-hero-content">
            <h1 class="temple-hero-title">🏛️ <?= __('temple_page_title') ?></h1>
            <p class="temple-hero-subtitle"><?= __('temple_page_desc') ?></p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="temple-main">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="<?= __('search_temple') ?>" value="<?= $search ?>">
                </div>
                
                <div class="filter-select">
                    <select name="province">
                        <option value=""><?= __('all_provinces') ?></option>
                        <?php foreach ($provinces as $prov): ?>
                        <option value="<?= $prov ?>" <?= $province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <input type="hidden" name="view" value="<?= $view ?>">
                
                <div class="view-toggle">
                    <a href="?view=grid&search=<?= urlencode($search) ?>&province=<?= urlencode($province) ?>" 
                       class="view-btn <?= $view === 'grid' ? 'active' : '' ?>" title="<?= __('grid_view') ?? 'Dạng lưới' ?>">
                        <i class="fas fa-th"></i>
                    </a>
                    <a href="?view=map&search=<?= urlencode($search) ?>&province=<?= urlencode($province) ?>" 
                       class="view-btn <?= $view === 'map' ? 'active' : '' ?>" title="<?= __('map_view') ?? 'Bản đồ' ?>">
                        <i class="fas fa-map"></i>
                    </a>
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> <?= __('filter') ?>
                </button>
                
                <?php if ($search || $province): ?>
                <a href="<?= BASE_URL ?>/chua-khmer.php" class="filter-reset">
                    <i class="fas fa-times"></i> <?= __('reset') ?>
                </a>
                <?php endif; ?>
            </form>
            
            <?php if ($search || $province): ?>
            <div class="active-filters">
                <?php if ($search): ?>
                <span class="filter-tag">
                    <i class="fas fa-search"></i> "<?= $search ?>"
                    <a href="?province=<?= urlencode($province) ?>&view=<?= $view ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
                <?php if ($province): ?>
                <span class="filter-tag">
                    <i class="fas fa-map-marker-alt"></i> <?= $province ?>
                    <a href="?search=<?= urlencode($search) ?>&view=<?= $view ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if ($view === 'map'): ?>
        <!-- Map View -->
        <div class="map-section">
            <div id="map" class="map-container"></div>
        </div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const map = L.map('map').setView([9.8, 105.5], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            
            <?php foreach ($temples as $temple): ?>
            <?php if ($temple['vi_do'] && $temple['kinh_do']): ?>
            L.marker([<?= $temple['vi_do'] ?>, <?= $temple['kinh_do'] ?>])
                .addTo(map)
                .bindPopup(`
                    <strong><?= addslashes($temple['ten_chua']) ?></strong><br>
                    <small><?= addslashes($temple['dia_chi']) ?></small><br>
                    <a href="<?= BASE_URL ?>/chua-khmer-chi-tiet.php?id=<?= $temple['ma_chua'] ?>"><?= __('view_detail') ?></a>
                `);
            <?php endif; ?>
            <?php endforeach; ?>
        </script>
        <?php endif; ?>
        
        <!-- Temple Grid -->
        <div class="temple-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    <?= __('all_temples') ?? 'Tất cả chùa Khmer' ?>
                </h2>
                <span class="results-count">
                    <?= __('showing') ?> <strong><?= count($temples) ?></strong> / <strong><?= $total ?></strong> <?= __('results') ?>
                </span>
            </div>
            
            <?php if (!empty($temples)): ?>
            <div class="temple-cards-grid">
                <!-- Table Header -->
                <div class="temple-table-header">
                    <div class="temple-table-header-cell">
                        Hình chùa Khmer
                    </div>
                    <div class="temple-table-header-cell">
                        Tên chùa Khmer
                    </div>
                    <div class="temple-table-header-cell">
                        Nút thao tác
                    </div>
                </div>
                
                <!-- Table Body -->
                <div class="temple-table-body">
                <?php foreach ($temples as $temple): 
                    $imgPath = $temple['hinh_anh_chinh'];
                    $imgUrl = (strpos($imgPath, 'uploads/') === 0) ? '/DoAn_ChuyenNganh/' . $imgPath : UPLOAD_PATH . 'chua/' . $imgPath;
                ?>
                <div class="timeline-item">
                    <div class="temple-card">
                        <!-- Image Column -->
                        <?php if (!empty($temple['hinh_anh_chinh'])): ?>
                        <div class="temple-card-image">
                            <img src="<?= $imgUrl ?>" alt="<?= sanitize($temple['ten_chua']) ?>" onerror="this.style.display='none'">
                        </div>
                        <?php endif; ?>
                        
                        <!-- Content Column -->
                        <div class="temple-card-body">
                            <div class="temple-card-content-left">
                                <div class="temple-card-meta-row">
                                    <?php if (!empty($temple['nam_thanh_lap'])): ?>
                                    <span class="temple-card-year">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= $temple['nam_thanh_lap'] ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($temple['tinh_thanh'])): ?>
                                    <span class="temple-card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= sanitize($temple['tinh_thanh']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="temple-card-title"><?= sanitize($temple['ten_chua']) ?></h3>
                                <?php if (!empty($temple['ten_tieng_khmer'])): ?>
                                <p class="temple-card-title-khmer"><?= $temple['ten_tieng_khmer'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Action Column -->
                        <div class="temple-card-btn-wrapper">
                            <a href="<?= BASE_URL ?>/chua-khmer-chi-tiet.php?id=<?= $temple['ma_chua'] ?>" class="temple-card-btn">
                                Xem thêm <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
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
                    <i class="fas fa-place-of-worship"></i>
                </div>
                <h3 class="empty-state-title"><?= __('no_results') ?></h3>
                <p class="empty-state-desc"><?= __('try_different') ?></p>
                <a href="<?= BASE_URL ?>/chua-khmer.php" class="empty-state-btn">
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

@media (max-width: 1024px) {
    .quiz-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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
