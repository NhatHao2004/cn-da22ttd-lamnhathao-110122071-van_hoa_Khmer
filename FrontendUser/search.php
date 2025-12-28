<?php
/**
 * Tìm kiếm - Redesigned
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('search');

$pdo = getDBConnection();
$query = sanitize($_GET['q'] ?? '');
$type = sanitize($_GET['type'] ?? 'all');

$results = [];

if ($query) {
    $searchTerm = "%$query%";
    
    // Search văn hóa
    if ($type === 'all' || $type === 'vanhoa') {
        $stmt = $pdo->prepare("SELECT ma_van_hoa as id, tieu_de, noi_dung, hinh_anh_chinh as hinh_anh, ngay_tao, 'vanhoa' as type FROM van_hoa WHERE trang_thai = 'xuat_ban' AND (tieu_de LIKE ? OR noi_dung LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());
    }
    
    // Search chùa
    if ($type === 'all' || $type === 'chua') {
        $stmt = $pdo->prepare("SELECT ma_chua as id, ten_chua as tieu_de, mo_ta_ngan as noi_dung, hinh_anh_chinh as hinh_anh, ngay_tao, 'chua' as type FROM chua_khmer WHERE trang_thai = 'hoat_dong' AND (ten_chua LIKE ? OR dia_chi LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());
    }
    
    // Search lễ hội
    if ($type === 'all' || $type === 'lehoi') {
        $stmt = $pdo->prepare("SELECT ma_le_hoi as id, ten_le_hoi as tieu_de, mo_ta as noi_dung, anh_dai_dien as hinh_anh, ngay_tao, 'lehoi' as type FROM le_hoi WHERE trang_thai = 'hien_thi' AND (ten_le_hoi LIKE ? OR mo_ta LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());
    }
    
    // Search truyện
    if ($type === 'all' || $type === 'truyen') {
        $stmt = $pdo->prepare("SELECT ma_truyen as id, tieu_de, noi_dung, anh_dai_dien as hinh_anh, ngay_tao, 'truyen' as type FROM truyen_dan_gian WHERE trang_thai = 'hien_thi' AND (tieu_de LIKE ? OR noi_dung LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());
    }
    
    // Search bài học
    if ($type === 'all' || $type === 'baihoc') {
        $stmt = $pdo->prepare("SELECT ma_bai_hoc as id, tieu_de, mo_ta as noi_dung, hinh_anh, ngay_tao, 'baihoc' as type FROM bai_hoc WHERE trang_thai = 'xuat_ban' AND (tieu_de LIKE ? OR noi_dung LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());
    }
}

$typeLabels = [
    'vanhoa' => ['label' => __('nav_culture'), 'icon' => 'fa-book-open', 'url' => 'van-hoa-chi-tiet.php', 'folder' => 'vanhoa', 'color' => '#667eea'],
    'chua' => ['label' => __('temple'), 'icon' => 'fa-place-of-worship', 'url' => 'chua-khmer-chi-tiet.php', 'folder' => 'chua', 'color' => '#f093fb'],
    'lehoi' => ['label' => __('nav_festivals'), 'icon' => 'fa-calendar-alt', 'url' => 'le-hoi-chi-tiet.php', 'folder' => 'lehoi', 'color' => '#4facfe'],
    'truyen' => ['label' => __('story'), 'icon' => 'fa-book', 'url' => 'truyen-chi-tiet.php', 'folder' => 'truyendangian', 'color' => '#fa709a'],
    'baihoc' => ['label' => __('lesson'), 'icon' => 'fa-graduation-cap', 'url' => 'bai-hoc-chi-tiet.php', 'folder' => '', 'color' => '#43e97b'],
];
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Search Hero Section ===== */
.search-page {
    padding-top: 70px;
    min-height: 100vh;
    background: #ffffff;
}

.search-hero {
    min-height: 30vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding: 4rem 0 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.search-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #000000;
    width: 100%;
}

.search-title {
    font-size: clamp(2rem, 5vw, 2.5rem);
    font-weight: 900;
    color: #000000 !important;
    margin-bottom: 0.5rem;
}

.search-subtitle {
    color: #000000;
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 2rem;
}
</style>

<style>
/* ===== Search Form ===== */
.search-form-wrapper {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.search-form-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 2px solid #000000;
}

.search-input-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-input-wrapper {
    flex: 1;
    min-width: 280px;
    position: relative;
}

.search-input-wrapper i {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: #000000;
    font-size: 1rem;
    font-weight: 600;
}

.search-input-wrapper input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #000000;
}

.search-input-wrapper input:focus {
    outline: none;
    border-color: #000000;
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.search-select {
    padding: 0.875rem 2.5rem 0.875rem 1rem;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23000000' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 1rem center;
    appearance: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 150px;
    color: #000000;
}

.search-select:focus {
    outline: none;
    border-color: #000000;
    background-color: white;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.search-btn {
    padding: 0.875rem 1.5rem;
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px);
}
</style>

<style>
/* Filter Tags */
.filter-tags {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.filter-tag {
    padding: 0.5rem 1.25rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-tag:hover,
.filter-tag.active {
    border-color: #667eea;
    color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.filter-tag.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
}

/* Results Section */
.search-results {
    padding: 2rem 0 4rem;
}

.results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.results-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.results-title span {
    color: #667eea;
}

.results-count {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #667eea;
}

/* Result Cards */
.results-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.result-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    text-decoration: none;
    color: inherit;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(102, 126, 234, 0.08);
}

.result-card:hover {
    transform: translateY(-4px) translateX(4px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    border-color: rgba(102, 126, 234, 0.2);
}

.result-image {
    width: 200px;
    min-height: 160px;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.result-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.result-card:hover .result-image img {
    transform: scale(1.1);
}

.result-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.5);
}

.result-content {
    flex: 1;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.result-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    width: fit-content;
    margin-bottom: 0.75rem;
}

.result-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease;
}

.result-card:hover .result-title {
    color: #667eea;
}

.result-excerpt {
    color: #64748b;
    font-size: 0.9375rem;
    line-height: 1.6;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.result-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}

.result-date {
    font-size: 0.8125rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

.result-arrow {
    margin-left: auto;
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    transition: all 0.3s ease;
}

.result-card:hover .result-arrow {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    transform: translateX(4px);
}
</style>

<style>
/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 2px solid #000000;
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.empty-icon i {
    font-size: 3rem;
    color: #f59e0b;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.5rem;
}

.empty-desc {
    color: #64748b;
    font-weight: 600;
    margin-bottom: 2rem;
}

/* ===== Popular Tags ===== */
.popular-tags {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

.popular-tag {
    padding: 0.625rem 1.25rem;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 700;
    color: #000000;
    text-decoration: none;
    transition: all 0.3s ease;
}

.popular-tag:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .search-title { font-size: 1.75rem; }
    .search-input-group { flex-direction: column; }
    .search-select { width: 100%; }
    .search-btn { width: 100%; justify-content: center; }
    .result-card { flex-direction: column; height: auto; }
    .result-image { width: 100%; min-height: 180px; }
    .result-content { padding: 1.5rem; }
}
</style>

<main class="search-page">
    <div class="container">
        <!-- Search Hero -->
        <div class="search-hero">
            <div class="search-hero-content">
                <h1 class="search-title"><?= __('search') ?></h1>
                <p class="search-subtitle"><?= __('search_hint') ?></p>
                
                <!-- Search Form -->
                <div class="search-form-wrapper">
                    <div class="search-form-card">
                        <form method="GET" action="">
                            <div class="search-input-group">
                                <div class="search-input-wrapper">
                                    <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= $query ?>" autofocus>
                                    <i class="fas fa-search"></i>
                                </div>
                                <select name="type" class="search-select">
                                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>><?= __('all') ?></option>
                                    <option value="vanhoa" <?= $type === 'vanhoa' ? 'selected' : '' ?>><?= __('nav_culture') ?></option>
                                    <option value="chua" <?= $type === 'chua' ? 'selected' : '' ?>><?= __('temple') ?></option>
                                    <option value="lehoi" <?= $type === 'lehoi' ? 'selected' : '' ?>><?= __('nav_festivals') ?></option>
                                    <option value="truyen" <?= $type === 'truyen' ? 'selected' : '' ?>><?= __('story') ?></option>
                                    <option value="baihoc" <?= $type === 'baihoc' ? 'selected' : '' ?>><?= __('lesson') ?></option>
                                </select>
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                    <?= __('search') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Filter Tags -->
                    <div class="filter-tags">
                        <a href="?q=<?= urlencode($query) ?>&type=all" class="filter-tag <?= $type === 'all' ? 'active' : '' ?>">
                            <i class="fas fa-layer-group"></i> <?= __('all') ?>
                        </a>
                        <a href="?q=<?= urlencode($query) ?>&type=vanhoa" class="filter-tag <?= $type === 'vanhoa' ? 'active' : '' ?>">
                            <i class="fas fa-book-open"></i> <?= __('nav_culture') ?>
                        </a>
                        <a href="?q=<?= urlencode($query) ?>&type=chua" class="filter-tag <?= $type === 'chua' ? 'active' : '' ?>">
                            <i class="fas fa-place-of-worship"></i> <?= __('temple') ?>
                        </a>
                        <a href="?q=<?= urlencode($query) ?>&type=lehoi" class="filter-tag <?= $type === 'lehoi' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt"></i> <?= __('nav_festivals') ?>
                        </a>
                        <a href="?q=<?= urlencode($query) ?>&type=truyen" class="filter-tag <?= $type === 'truyen' ? 'active' : '' ?>">
                            <i class="fas fa-book"></i> <?= __('story') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results -->
        <div class="search-results">
            <?php if ($query): ?>
                <div class="results-header">
                    <h2 class="results-title"><?= __('search_results_for') ?> "<span><?= $query ?></span>"</h2>
                    <span class="results-count"><?= count($results) ?> <?= __('results') ?></span>
                </div>
                
                <?php if (empty($results)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="empty-title"><?= __('no_results') ?></h3>
                    <p class="empty-desc"><?= __('try_different_keyword') ?></p>
                    <div class="popular-tags">
                        <a href="?q=Chol Chnam Thmay" class="popular-tag">Chol Chnam Thmay</a>
                        <a href="?q=Ok Om Bok" class="popular-tag">Ok Om Bok</a>
                        <a href="?q=Sene Dolta" class="popular-tag">Sene Dolta</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="results-grid">
                    <?php foreach ($results as $item): 
                        $typeInfo = $typeLabels[$item['type']];
                    ?>
                    <a href="<?= BASE_URL ?>/<?= $typeInfo['url'] ?>?id=<?= $item['id'] ?>" class="result-card">
                        <div class="result-image">
                            <?php if ($item['hinh_anh'] && $typeInfo['folder']): ?>
                            <img src="<?= UPLOAD_PATH ?><?= $typeInfo['folder'] ?>/<?= $item['hinh_anh'] ?>" alt="<?= sanitize($item['tieu_de']) ?>">
                            <?php else: ?>
                            <div class="result-image-placeholder" style="background: linear-gradient(135deg, <?= $typeInfo['color'] ?>, <?= $typeInfo['color'] ?>dd);">
                                <i class="fas <?= $typeInfo['icon'] ?>"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="result-content">
                            <span class="result-badge" style="background: linear-gradient(135deg, <?= $typeInfo['color'] ?>, <?= $typeInfo['color'] ?>cc);">
                                <i class="fas <?= $typeInfo['icon'] ?>"></i>
                                <?= $typeInfo['label'] ?>
                            </span>
                            <h3 class="result-title"><?= sanitize($item['tieu_de']) ?></h3>
                            <p class="result-excerpt"><?= truncateText(strip_tags($item['noi_dung']), 150) ?></p>
                            <div class="result-meta">
                                <span class="result-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d/m/Y', strtotime($item['ngay_tao'])) ?>
                                </span>
                                <span class="result-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <!-- Empty State - No Query -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-compass"></i>
                </div>
                <h3 class="empty-title"><?= __('search_content') ?></h3>
                <p class="empty-desc"><?= __('search_hint') ?></p>
                <div class="popular-tags">
                    <a href="?q=Chol Chnam Thmay" class="popular-tag">Chol Chnam Thmay</a>
                    <a href="?q=Ok Om Bok" class="popular-tag">Ok Om Bok</a>
                    <a href="?q=Sene Dolta" class="popular-tag">Sene Dolta</a>
                    <a href="?q=Chùa Dơi" class="popular-tag">Chùa Dơi</a>
                    <a href="?q=Văn hóa" class="popular-tag">Văn hóa</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
