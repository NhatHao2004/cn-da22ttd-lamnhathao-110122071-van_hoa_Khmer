<?php
/**
 * Danh sách văn hóa Khmer - Modern Redesign (giống chua-khmer.php)
 */
// Disable cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/includes/header.php';
$pageTitle = __('culture_page_title');

try {
    $pdo = getDBConnection();

    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    // Filters
    $search = sanitize($_GET['search'] ?? '');
    $category = sanitize($_GET['category'] ?? '');

    // Build query - Filter theo trang_thai = 'xuat_ban'
    $where = "WHERE trang_thai = 'xuat_ban'";
    $params = [];

    if ($search) {
        $where .= " AND (tieu_de LIKE ? OR noi_dung LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM van_hoa $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $totalPages = ceil($total / $perPage);

    // Get items
    $stmt = $pdo->prepare("SELECT * FROM van_hoa $where ORDER BY ngay_tao DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Bảng van_hoa không có cột the_loai, không lấy categories
    $categories = [];
    $categoryMap = [];

    // Get stats
    $totalArticles = $pdo->query("SELECT COUNT(*) FROM van_hoa")->fetchColumn() ?: 0;
    $totalCategories = count($categories);
    
    // Get quizzes
    $quizStmt = $pdo->query("SELECT * FROM quiz WHERE trang_thai = 'hoat_dong' ORDER BY ngay_tao DESC LIMIT 6");
    $quizzes = $quizStmt->fetchAll();
} catch (Exception $e) {
    error_log("ERROR in van-hoa.php: " . $e->getMessage());
    error_log("ERROR TRACE: " . $e->getTraceAsString());
    $items = [];
    $categories = [];
    $categoryMap = [];
    $total = 0;
    $totalPages = 0;
    $totalArticles = 0;
    $totalCategories = 0;
    $quizzes = [];
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Culture Hero Section ===== */
.culture-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 3px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.culture-hero::before {
    content: '🎭';
    position: absolute;
    top: 20px;
    left: 10%;
    font-size: 4rem;
    opacity: 0.15;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.culture-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.culture-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.2);
}

.culture-hero-subtitle {
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
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.25);
    transition: all 0.3s ease;
}

.hero-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(255, 152, 0, 0.35);
}

.hero-stat-number { 
    font-size: 2rem; 
    font-weight: 900; 
    display: block;
    color: #FF9800;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}
.hero-stat-label { 
    font-size: 0.875rem;
    color: #1a1a1a;
    font-weight: 700;
}
</style>

<style>
/* ===== Main Content Area ===== */
.culture-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFEDCC 100%);
    min-height: 60vh;
}

/* ===== Filter Bar ===== */
.filter-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.2);
    border: 3px solid #1a1a1a;
    position: relative;
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
    min-width: 180px;
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
    background: linear-gradient(135deg, #FF9800 0%, #FF6F00 100%);
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
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

.filter-btn:hover {
    background: linear-gradient(135deg, #F57C00 0%, #E65100 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 124, 0, 0.5);
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
    background: linear-gradient(135deg, #F57C00 0%, #E65100 100%);
    color: #ffffff;
    transform: translateY(-2px);
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
    background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 111, 0, 0.15));
    color: #FF6F00;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 600;
    border: 2px solid rgba(255, 152, 0, 0.3);
}

.filter-tag a { color: inherit; }
</style>

<style>
/* ===== Culture Cards Grid ===== */
.culture-grid-section {
    margin-bottom: 3rem;
}

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
    font-size: 1.75rem;
}

.results-count {
    font-size: 0.9375rem;
    color: #2d2d2d;
    font-weight: 700;
    background: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    border: 2px solid #1a1a1a;
}

.results-count strong {
    color: #FF9800;
    font-weight: 900;
}

/* Culture Cards Grid - Table Layout */
.culture-cards-grid {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(255, 152, 0, 0.2);
    border: 3px solid #1a1a1a;
}

/* Table Header */
.culture-table-header {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #FFE4B5 0%, #FFCC80 100%);
    border-bottom: 3px solid #1a1a1a;
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a1a;
}

.culture-table-header-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.culture-table-header-cell i {
    color: #FF6F00;
    font-size: 1.125rem;
}

/* Table Body */
.culture-table-body {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    position: relative;
    margin-bottom: 0;
}

/* Culture Card - Table Row */
.culture-card {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    text-decoration: none;
    align-items: center;
}

.culture-card:last-child {
    border-bottom: none;
}

.culture-card:hover {
    background: #ffffff;
}

/* Image Column */
.culture-card-image-wrapper {
    position: relative;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #FF9800, #FF6F00);
    flex-shrink: 0;
    border: 2px solid #1a1a1a;
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

.culture-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.culture-card:hover img {
    transform: scale(1.1);
}

.culture-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: rgba(255,255,255,0.3);
}

/* Title Column */
.culture-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0;
    margin-top: -5rem;
}

.culture-card-header {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.culture-card-meta-row {
    font-size: 0.8125rem;
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.culture-card-date,
.culture-card-author {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.culture-card-date i {
    color: #FF9800;
    font-size: 0.875rem;
}

.culture-card-author i {
    color: #FF6F00;
    font-size: 0.875rem;
}

.culture-card-name {
    color: #1a1a1a;
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.culture-card-name-khmer {
    color: #64748b;
    font-size: 0.9375rem;
    font-family: 'Battambang', 'Khmer OS Siemreap', 'Noto Sans Khmer', cursive;
    font-weight: 500;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
    line-height: 1.5;
}

.culture-card-excerpt {
    display: none;
}

/* Action Column */
.culture-card-footer {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 0.5rem 0;
    border: none;
    margin: 0;
    margin-bottom: -7rem;
}

.culture-card-meta {
    display: none;
}

.culture-card-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #FF9800 0%, #FF6F00 100%);
    color: #ffffff;
    border: 2px solid #1a1a1a;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 700;
    transition: all 0.3s ease;
    white-space: nowrap;
    width: 100%;
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

.culture-card:hover .culture-card-action {
    background: linear-gradient(135deg, #F57C00 0%, #E65100 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 124, 0, 0.5);
}

.culture-card-action i {
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.culture-card:hover .culture-card-action i {
    transform: translateX(3px);
}

.culture-card-badge {
    display: none;
}

.culture-card-status-badge {
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
    color: #2d2d2d;
    background: #ffffff;
    border: 2px solid transparent;
}

.pagination a:hover {
    background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 111, 0, 0.15));
    color: #FF6F00;
    border-color: #FF9800;
}

.pagination .active {
    background: linear-gradient(135deg, #FF9800, #FF6F00);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);
    border: 2px solid #1a1a1a;
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
    background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 111, 0, 0.15));
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
}

.empty-state-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #FF9800, #FF6F00);
    color: white;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid #1a1a1a;
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

.empty-state-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4);
    color: white;
}

/* ===== Responsive ===== */
@media (max-width: 1200px) {
    .culture-card-image-wrapper {
        width: 340px;
    }
    
    .culture-card-content {
        padding: 1.75rem 2rem;
    }
    
    .culture-card-name {
        font-size: 1.375rem;
    }
}

@media (max-width: 1024px) {
    .culture-card {
        height: 220px;
    }
    
    .culture-card-image-wrapper {
        width: 300px;
    }
    
    .culture-card-content {
        padding: 1.5rem 1.75rem;
    }
    
    .culture-card-name {
        font-size: 1.25rem;
    }
}

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

@media (max-width: 768px) {
    /* Table Header - Stack on mobile */
    .culture-table-header {
        display: none;
    }
    
    /* Table Body */
    .culture-cards-grid {
        border-radius: 12px;
    }
    
    .culture-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 0;
    }
    
    .culture-card:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .culture-card:last-child {
        border-radius: 0 0 12px 12px;
    }
    
    .culture-card-image-wrapper {
        width: 100%;
        height: 180px;
    }
    
    .culture-card-content {
        gap: 0.75rem;
    }
    
    .culture-card-name {
        font-size: 1rem;
        -webkit-line-clamp: 2;
    }
    
    .culture-card-name-khmer {
        font-size: 0.875rem;
    }
    
    .culture-card-meta-row {
        font-size: 0.8125rem;
        gap: 0.75rem;
    }
    
    .culture-card-footer {
        width: 100%;
    }
    
    .culture-card-action {
        width: 100%;
        padding: 0.875rem 1.25rem;
    }
    
    .filter-form {
        flex-direction: column;
    }
    .filter-search, .filter-select {
        width: 100%;
    }
    .hero-stats {
        gap: 1.5rem;
    }
    .culture-hero-title {
        font-size: 1.75rem;
    }
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

@media (max-width: 480px) {
    .culture-card {
        padding: 1rem;
    }
    
    .culture-card-image-wrapper {
        height: 160px;
        border-radius: 10px;
    }
    
    .culture-card-name {
        font-size: 0.9375rem;
    }
    
    .culture-card-action {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

/* Tablet - Show simplified table */
@media (min-width: 769px) and (max-width: 1024px) {
    .culture-table-header {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .culture-card {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }
    
    .culture-card-image-wrapper {
        height: 120px;
    }
    
    .culture-card-name {
        font-size: 1rem;
    }
    
    .culture-card-action {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
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
</style>

<!-- Hero Section -->
<section class="culture-hero">
    <div class="container">
        <div class="culture-hero-content">
            <h1 class="culture-hero-title">🎭 Văn Hóa Khmer</h1>
            <p class="culture-hero-subtitle">Khám phá nét đẹp văn hóa của người Khmer Nam Bộ</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="culture-main">
    <div class="container">

        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" method="GET" action="">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="<?= __('search_article') ?? 'Tìm bài viết...' ?>" value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> <?= __('search') ?? 'Tìm kiếm' ?>
                </button>
                
                <?php if ($search || $category): ?>
                <a href="<?= BASE_URL ?>/van-hoa.php" class="filter-reset">
                    <i class="fas fa-times"></i> <?= __('reset') ?? 'Đặt lại' ?>
                </a>
                <?php endif; ?>
            </form>
            
            <?php if ($search || $category): ?>
            <div class="active-filters">
                <?php if ($search): ?>
                <span class="filter-tag">
                    <i class="fas fa-search"></i> "<?= htmlspecialchars($search) ?>"
                    <a href="?category=<?= urlencode($category) ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
                <?php if ($category): ?>
                <span class="filter-tag">
                    <i class="fas fa-folder"></i> <?= isset($categoryMap[$category]) ? htmlspecialchars($categoryMap[$category]) : $category ?>
                    <a href="?search=<?= urlencode($search) ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Culture Grid -->
        <div class="culture-grid-section">
            <div class="section-header-row">
                <h2 class="section-title">
                    Tất cả bài viết văn hóa
                </h2>
                <span class="results-count">
                    Hiển thị <strong><?= count($items) ?></strong> / <strong><?= $total ?></strong> bài viết
                </span>
            </div>
            
            <?php if (!empty($items)): ?>
            <div class="culture-cards-grid">
                <!-- Table Header -->
                <div class="culture-table-header">
                    <div class="culture-table-header-cell">
                        Hình bài viết văn hóa
                    </div>
                    <div class="culture-table-header-cell">
                        Tên bài viết văn hóa
                    </div>
                    <div class="culture-table-header-cell">
                        Nút thao tác
                    </div>
                </div>
                
                <!-- Table Body -->
                <div class="culture-table-body">
                <?php foreach ($items as $item): 
                    $imgPath = $item['hinh_anh_chinh'] ?? '';
                    // Xử lý đường dẫn ảnh
                    if (empty($imgPath)) {
                        $imgUrl = '';
                    } elseif (strpos($imgPath, 'http') === 0) {
                        $imgUrl = $imgPath;
                    } elseif (strpos($imgPath, 'uploads/') === 0) {
                        $imgUrl = '/DoAn_ChuyenNganh/' . $imgPath;
                    } else {
                        $imgUrl = UPLOAD_PATH . 'vanhoa/' . $imgPath;
                    }
                ?>
                <a href="<?= BASE_URL ?>/van-hoa-chi-tiet.php?id=<?= $item['ma_van_hoa'] ?>" class="culture-card">
                    <!-- Image Column -->
                    <div class="culture-card-image-wrapper">
                        <?php if (!empty($imgUrl)): ?>
                        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($item['tieu_de']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="culture-card-placeholder" style="display:none;"><i class="fas fa-book-open"></i></div>
                        <?php else: ?>
                        <div class="culture-card-placeholder"><i class="fas fa-book-open"></i></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Title Column -->
                    <div class="culture-card-content">
                        <div class="culture-card-header">
                            <div class="culture-card-meta-row">
                                <span class="culture-card-date">
                                    <i class="far fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($item['ngay_tao'])) ?>
                                </span>
                                <?php if (!empty($item['tac_gia'])): ?>
                                <span class="culture-card-author">
                                    <i class="far fa-user"></i>
                                    <?= htmlspecialchars($item['tac_gia']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="culture-card-name"><?= htmlspecialchars($item['tieu_de']) ?></h3>
                            <?php if (!empty($item['tieu_de_khmer'])): ?>
                            <p class="culture-card-name-khmer"><?= $item['tieu_de_khmer'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Column -->
                    <div class="culture-card-footer">
                        <span class="culture-card-action">
                            Xem thêm <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
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
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="empty-state-title">Không tìm thấy bài viết</h3>
                <p class="empty-state-desc">Hiện chưa có bài viết văn hóa nào được xuất bản. Vui lòng quay lại sau!</p>
                <a href="<?= BASE_URL ?>/van-hoa.php" class="empty-state-btn">
                    <i class="fas fa-redo"></i> Làm mới
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
