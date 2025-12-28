<?php
/**
 * Danh sách lễ hội - Modern Redesign
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('festival_page_title');

try {
    $pdo = getDBConnection();

    // Filters
    $filter = sanitize($_GET['filter'] ?? 'all');
    $view = sanitize($_GET['view'] ?? 'timeline');

    // Build query - Không filter theo trang_thai nếu cột không tồn tại
    $where = "WHERE 1=1";
    if ($filter === 'upcoming') {
        $where .= " AND ngay_bat_dau >= CURDATE()";
    } elseif ($filter === 'past') {
        $where .= " AND ngay_ket_thuc < CURDATE()";
    }

    $stmt = $pdo->query("SELECT * FROM le_hoi $where ORDER BY ngay_bat_dau ASC");
    $festivals = $stmt->fetchAll();

    // Group by month for calendar view
    $festivalsByMonth = [];
    foreach ($festivals as $f) {
        $month = date('Y-m', strtotime($f['ngay_bat_dau']));
        $festivalsByMonth[$month][] = $f;
    }

    // Get stats
    $totalFestivals = $pdo->query("SELECT COUNT(*) FROM le_hoi")->fetchColumn() ?: 0;
    $upcomingCount = $pdo->query("SELECT COUNT(*) FROM le_hoi WHERE ngay_bat_dau >= CURDATE()")->fetchColumn() ?: 0;
    
    // Get quizzes
    $quizStmt = $pdo->query("SELECT * FROM quiz WHERE trang_thai = 'hoat_dong' ORDER BY ngay_tao DESC LIMIT 6");
    $quizzes = $quizStmt->fetchAll();
} catch (Exception $e) {
    error_log("Error in le-hoi.php: " . $e->getMessage());
    $festivals = [];
    $festivalsByMonth = [];
    $totalFestivals = 0;
    $upcomingCount = 0;
    $quizzes = [];
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Festival Hero Section ===== */
.festival-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 3px solid rgba(255, 255, 255, 0.3);
}

.festival-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.festival-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.festival-hero-subtitle {
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
.festival-main {
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

.filter-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    background: #ffffff;
    padding: 0.375rem;
    border-radius: 12px;
    border: 2px solid #1a1a1a;
}

.filter-tab {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 700;
    color: #1a1a1a;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.filter-tab:hover {
    background: #ffffff;
}

.filter-tab.active {
    background: #FF9800;
    color: #ffffff;
    border-color: #1a1a1a;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
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
</style>

<style>
/* ===== Timeline View - Table Layout ===== */
.timeline-section {
    position: relative;
    padding-left: 0;
}

.timeline-line {
    display: none;
}

.timeline-dot {
    display: none;
}

/* Festival Cards Grid - Table Layout */
.festival-cards-grid {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 25px rgba(255, 152, 0, 0.15);
    border: 3px solid #1a1a1a;
}

/* Table Header */
.festival-table-header {
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

.festival-table-header-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.festival-table-header-cell i {
    color: #FF9800;
    font-size: 1.125rem;
}

/* Table Body */
.festival-table-body {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    position: relative;
    margin-bottom: 0;
}

/* Festival Card - Table Row */
.festival-card-new {
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
    border-left: none;
}

.festival-card-new:last-child {
    border-bottom: none;
}

.festival-card-new:hover {
    background: #ffffff;
    transform: none;
    box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.3);
}

.festival-card-new.upcoming {
    border-left: none;
}

/* Image Column */
.festival-card-image {
    position: relative;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    flex-shrink: 0;
    border: 2px solid #1a1a1a;
}

.festival-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.festival-card-new:hover .festival-card-image img {
    transform: scale(1.1);
}

/* Content Column */
.festival-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0;
    margin-top: -5rem;
}

.festival-card-content-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.festival-card-date-row {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.festival-card-date-row i {
    color: #FF9800;
    font-size: 1rem;
}

.festival-card-title {
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

/* Action Column */
.festival-card-btn-wrapper {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 0.5rem 0;
    margin-bottom: -5rem;
}

.festival-card-btn {
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
    margin-bottom: -2rem;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
}

.festival-card-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255, 152, 0, 0.4);
}

.festival-card-btn i {
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.festival-card-btn:hover i {
    transform: translateX(3px);
}

/* Hide old elements */
.festival-card-content-row,
.festival-card-title-section,
.festival-card-header,
.festival-card-meta-top,
.festival-badge,
.festival-date,
.festival-card-desc {
    display: none;
}
</style>


<style>
/* ===== Calendar View ===== */
.calendar-month {
    margin-bottom: 3rem;
}

.calendar-month-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #FF9800;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.calendar-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.1);
    transition: all 0.4s ease;
    border: 2px solid #1a1a1a;
    position: relative;
}

.calendar-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(255, 152, 0, 0.25);
}

.calendar-card:hover .calendar-image-overlay {
    opacity: 1;
}

.calendar-image-wrapper {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
}

.calendar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.calendar-card:hover .calendar-image {
    transform: scale(1.1);
}

.calendar-date {
    position: absolute;
    top: 1rem;
    left: 1rem;
    min-width: 70px;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
    z-index: 2;
    border: 2px solid #1a1a1a;
}

.calendar-day {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
}

.calendar-month-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
}

.calendar-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.calendar-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.3);
    border: 2px solid #1a1a1a;
}

.calendar-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(255, 152, 0, 0.4);
}

.calendar-content {
    padding: 1.25rem;
}

.calendar-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.calendar-desc {
    display: none;
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
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #667eea;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.empty-state-desc {
    font-size: 1rem;
    color: #64748b;
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
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.empty-state-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    color: white;
}

/* ===== Section Header ===== */
.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
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

/* ===== Responsive ===== */
@media (max-width: 1024px) {
    .calendar-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    /* Table Header - Stack on mobile */
    .festival-table-header {
        display: none;
    }
    
    /* Table Body */
    .festival-cards-grid {
        border-radius: 12px;
    }
    
    .festival-card-new {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 0;
    }
    
    .festival-card-new:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .festival-card-new:last-child {
        border-radius: 0 0 12px 12px;
    }
    
    .festival-card-image {
        width: 100%;
        height: 180px;
    }
    
    .festival-card-body {
        gap: 0.75rem;
        margin-top: 0;
    }
    
    .festival-card-title {
        font-size: 1rem !important;
        -webkit-line-clamp: 2 !important;
    }
    
    .festival-card-date-row {
        font-size: 0.8125rem;
    }
    
    .festival-card-btn-wrapper {
        width: 100%;
        padding: 0;
        margin-bottom: 0;
    }
    
    .festival-card-btn {
        width: 100%;
        padding: 0.875rem 1.25rem;
    }
    
    .calendar-grid {
        grid-template-columns: 1fr;
    }
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-tabs {
        justify-content: center;
    }
    .view-toggle {
        justify-content: center;
    }
    .hero-stats {
        gap: 1.5rem;
    }
    .festival-hero-title {
        font-size: 1.75rem;
    }
    .calendar-image-wrapper {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .festival-card-new {
        padding: 1rem;
    }
    
    .festival-card-image {
        height: 160px;
        border-radius: 10px;
    }
    
    .festival-card-title {
        font-size: 0.9375rem !important;
    }
    
    .festival-card-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

/* Tablet - Show simplified table */
@media (min-width: 769px) and (max-width: 1024px) {
    .festival-table-header {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .festival-card-new {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }
    
    .festival-card-image {
        height: 120px;
    }
    
    .festival-card-title {
        font-size: 1rem !important;
    }
    
    .festival-card-btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
    
    .calendar-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>


<!-- Hero Section -->
<section class="festival-hero">
    <div class="container">
        <div class="festival-hero-content">
            <h1 class="festival-hero-title">🎊 <?= __('festival_page_title') ?></h1>
            <p class="festival-hero-subtitle"><?= __('festival_page_desc') ?></p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="festival-main">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-tabs">
                    <a href="?filter=all&view=<?= $view ?>" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>"><?= __('all') ?></a>
                    <a href="?filter=upcoming&view=<?= $view ?>" class="filter-tab <?= $filter === 'upcoming' ? 'active' : '' ?>"><?= __('upcoming') ?></a>
                    <a href="?filter=past&view=<?= $view ?>" class="filter-tab <?= $filter === 'past' ? 'active' : '' ?>"><?= __('past') ?></a>
                </div>
                
                <div class="view-toggle">
                    <a href="?filter=<?= $filter ?>&view=timeline" class="view-btn <?= $view === 'timeline' ? 'active' : '' ?>" title="Timeline">
                        <i class="fas fa-stream"></i>
                    </a>
                    <a href="?filter=<?= $filter ?>&view=calendar" class="view-btn <?= $view === 'calendar' ? 'active' : '' ?>" title="Calendar">
                        <i class="fas fa-calendar-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (empty($festivals)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3 class="empty-state-title"><?= __('no_results') ?></h3>
            <p class="empty-state-desc"><?= __('try_different') ?></p>
            <a href="<?= BASE_URL ?>/le-hoi.php" class="empty-state-btn">
                <i class="fas fa-redo"></i> <?= __('view_all') ?>
            </a>
        </div>
        
        <?php elseif ($view === 'calendar'): ?>
        <!-- Calendar View -->
        <?php foreach ($festivalsByMonth as $month => $items): ?>
        <div class="calendar-month">
            <h3 class="calendar-month-title">
                <i class="far fa-calendar"></i> Tháng <?= date('m/Y', strtotime($month . '-01')) ?>
            </h3>
            <div class="calendar-grid">
                <?php foreach ($items as $festival): 
                    $festivalImage = $festival['anh_dai_dien'];
                    $festivalImageUrl = (strpos($festivalImage, 'uploads/') === 0) ? '/DoAn_ChuyenNganh/' . $festivalImage : BASE_URL . '/uploads/lehoi/' . $festivalImage;
                ?>
                <div class="calendar-card">
                    <div class="calendar-image-wrapper">
                        <?php if (!empty($festival['anh_dai_dien'])): ?>
                        <img src="<?= $festivalImageUrl ?>" alt="<?= sanitize($festival['ten_le_hoi']) ?>" class="calendar-image">
                        <?php endif; ?>
                        <div class="calendar-date">
                            <span class="calendar-day"><?= date('d', strtotime($festival['ngay_bat_dau'])) ?></span>
                            <span class="calendar-month-label"><?= date('M', strtotime($festival['ngay_bat_dau'])) ?></span>
                        </div>
                        <div class="calendar-image-overlay">
                            <a href="<?= BASE_URL ?>/le-hoi-chi-tiet.php?id=<?= $festival['ma_le_hoi'] ?>" class="calendar-btn">
                                Xem chi tiết <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="calendar-content">
                        <h4 class="calendar-title"><?= sanitize($festival['ten_le_hoi']) ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php else: ?>
        <!-- Timeline View -->
        <div class="section-header-row">
            <h2 class="section-title">
                <?= __('all_festivals') ?? 'Tất cả lễ hội' ?>
            </h2>
            <span class="results-count">
                Hiển thị <strong><?= count($festivals) ?></strong> / <strong><?= $totalFestivals ?></strong> lễ hội
            </span>
        </div>
        
        <div class="timeline-section">
            <div class="festival-cards-grid">
                <!-- Table Header -->
                <div class="festival-table-header">
                    <div class="festival-table-header-cell">
                        Hình lễ hội
                    </div>
                    <div class="festival-table-header-cell">
                        Tên lễ hội
                    </div>
                    <div class="festival-table-header-cell">
                        Nút thao tác
                    </div>
                </div>
                
                <!-- Table Body -->
                <div class="festival-table-body">
                <?php foreach ($festivals as $festival): 
                    $isUpcoming = strtotime($festival['ngay_bat_dau']) >= strtotime('today');
                ?>
                <div class="timeline-item">
                    <div class="festival-card-new <?= $isUpcoming ? 'upcoming' : '' ?>">
                        <!-- Image Column -->
                        <?php if (!empty($festival['anh_dai_dien'])): 
                            $festivalImage = $festival['anh_dai_dien'];
                            $festivalImageUrl = (strpos($festivalImage, 'uploads/') === 0) ? '/DoAn_ChuyenNganh/' . $festivalImage : BASE_URL . '/uploads/lehoi/' . $festivalImage;
                        ?>
                        <div class="festival-card-image">
                            <img src="<?= $festivalImageUrl ?>" alt="<?= sanitize($festival['ten_le_hoi']) ?>" onerror="this.style.display='none'">
                        </div>
                        <?php endif; ?>
                        
                        <!-- Content Column -->
                        <div class="festival-card-body">
                            <div class="festival-card-content-left">
                                <div class="festival-card-date-row">
                                    <i class="far fa-calendar"></i>
                                    <?= formatDate($festival['ngay_bat_dau'], 'd/m/Y') ?>
                                    <?php if ($festival['ngay_ket_thuc'] && $festival['ngay_ket_thuc'] !== $festival['ngay_bat_dau']): ?>
                                    - <?= formatDate($festival['ngay_ket_thuc'], 'd/m/Y') ?>
                                    <?php endif; ?>
                                </div>
                                <h3 class="festival-card-title"><?= htmlspecialchars($festival['ten_le_hoi']) ?></h3>
                            </div>
                        </div>
                        
                        <!-- Action Column -->
                        <div class="festival-card-btn-wrapper">
                            <a href="<?= BASE_URL ?>/le-hoi-chi-tiet.php?id=<?= $festival['ma_le_hoi'] ?>" class="festival-card-btn">
                                Xem thêm <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
