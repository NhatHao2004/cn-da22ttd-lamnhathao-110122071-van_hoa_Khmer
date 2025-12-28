<?php
/**
 * Bản đồ di sản - Unified Design
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('nav_map');

try {
    $pdo = getDBConnection();
    
    // Get temples with more details
    $temples = $pdo->query("
        SELECT ma_chua, ten_chua, ten_tieng_khmer, dia_chi, tinh_thanh, quan_huyen, 
               vi_do, kinh_do, hinh_anh_chinh, mo_ta_ngan, loai_chua, so_nha_su, nam_thanh_lap
        FROM chua_khmer 
        WHERE trang_thai = 'hoat_dong' AND vi_do IS NOT NULL AND kinh_do IS NOT NULL
        ORDER BY ten_chua ASC
    ")->fetchAll();
    
    $provinces = array_unique(array_filter(array_column($temples, 'tinh_thanh')));
    sort($provinces);
    
    $totalTemples = count($temples);
    $totalProvinces = count($provinces);
    
} catch (Exception $e) {
    error_log("Map error: " . $e->getMessage());
    $temples = [];
    $provinces = [];
    $totalTemples = 0;
    $totalProvinces = 0;
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">

<style>
/* ===== Map Hero Section ===== */
.map-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.map-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.map-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.map-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}
</style>

<style>
/* ===== Main Content Area ===== */
.map-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFE4B5 0%, #FFCC80 100%);
    min-height: 60vh;
}

/* ===== Filter Section ===== */
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

.filter-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FF9800;
    font-weight: 600;
}

.filter-select-wrapper {
    min-width: 200px;
}

.filter-select {
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

.filter-select:focus {
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
    color: #ffffff;
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
</style>

<style>
/* ===== Map Layout ===== */
.map-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 1.5rem;
}

/* ===== Temple List Sidebar ===== */
.temple-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.temple-list-container {
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 4px 4px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
    max-height: calc(100vh - 400px);
    overflow-y: auto;
}

.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 900;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #FF9800;
}

.results-count {
    font-size: 0.8125rem;
    color: #1a1a1a;
    font-weight: 700;
    background: #ffffff;
    padding: 0.375rem 0.75rem;
    border-radius: 50px;
    border: 2px solid #1a1a1a;
}

.results-count strong {
    font-weight: 900;
    color: #FF9800;
}

/* Temple Card in Sidebar */
.temple-card {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 0.75rem;
    border: 2px solid transparent;
    background: #ffffff;
}

.temple-card:hover {
    background: #ffffff;
    border-color: #FF9800;
    transform: translateX(4px);
    box-shadow: 2px 2px 0px #FF9800;
}

.temple-card.active {
    background: #FFF6E5;
    border-color: #FF9800;
    box-shadow: 3px 3px 0px #FF9800;
}

.temple-thumb {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #1a1a1a;
    box-shadow: 3px 3px 0px #FF9800;
}

.temple-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.temple-thumb i {
    font-size: 1.5rem;
    color: #FF9800;
}

.temple-info {
    flex: 1;
    min-width: 0;
}

.temple-name {
    font-size: 0.9375rem;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.temple-address {
    font-size: 0.75rem;
    color: #2d2d2d;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 0.375rem;
    line-height: 1.4;
}

.temple-address i {
    color: #FF9800;
    margin-top: 2px;
    flex-shrink: 0;
}

.temple-province {
    display: inline-block;
    margin-top: 0.5rem;
    padding: 0.25rem 0.625rem;
    background: #FFE4B5;
    color: #1a1a1a;
    border-radius: 6px;
    font-size: 0.625rem;
    font-weight: 700;
    border: 2px solid #FF9800;
}
</style>

<style>
/* ===== Map Container ===== */
.map-container {
    background: #ffffff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 4px 4px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
    position: relative;
    height: calc(100vh - 280px);
    min-height: 500px;
}

#map {
    width: 100%;
    height: 100%;
}

/* Map Controls */
.map-controls {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 500;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.map-btn {
    width: 44px;
    height: 44px;
    background: #FF9800;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.map-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

/* Info Card */
.map-info-card {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    max-width: 450px;
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 6px 6px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
    display: none;
    z-index: 500;
}

.map-info-card.show {
    display: block;
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.info-card-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 32px;
    height: 32px;
    background: #FFE4B5;
    border: 2px solid #1a1a1a;
    border-radius: 50%;
    color: #1a1a1a;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.info-card-close:hover {
    background: #FF9800;
    color: #ffffff;
}

.info-card-header {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.info-card-image {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #1a1a1a;
    box-shadow: 3px 3px 0px #FF9800;
}

.info-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.info-card-image i {
    font-size: 2rem;
    color: #FF9800;
}

.info-card-content {
    flex: 1;
    min-width: 0;
}

.info-card-name {
    font-size: 1.125rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.375rem;
    line-height: 1.3;
}

.info-card-address {
    font-size: 0.8125rem;
    color: #2d2d2d;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    line-height: 1.4;
}

.info-card-address i {
    color: #FF9800;
    margin-top: 2px;
}

.info-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #FFE4B5;
}

.info-btn {
    padding: 0.75rem 1rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: 3px solid #1a1a1a;
    cursor: pointer;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.info-btn.primary {
    background: #FF9800;
    color: #ffffff;
}

.info-btn.primary:hover {
    background: #F57C00;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.info-btn.secondary {
    background: #ffffff;
    color: #1a1a1a;
}

.info-btn.secondary:hover {
    background: #1a1a1a;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #FF9800;
}
</style>

<style>
/* ===== Custom Marker ===== */
.custom-marker {
    background: transparent !important;
    border: none !important;
}

.marker-pin {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 4px 4px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
    transition: all 0.3s ease;
}

.marker-pin i {
    transform: rotate(45deg);
    color: white;
    font-size: 1rem;
}

.marker-pin:hover {
    transform: rotate(-45deg) scale(1.15);
    box-shadow: 6px 6px 0px #1a1a1a;
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: #ffffff;
    border-radius: 15px;
    border: 3px solid #1a1a1a;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border: 3px solid #1a1a1a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #FF9800;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.empty-state-desc {
    font-size: 0.9375rem;
    color: #2d2d2d;
    font-weight: 600;
}

/* ===== Responsive ===== */
@media (max-width: 1200px) {
    .map-layout {
        grid-template-columns: 340px 1fr;
    }
}

@media (max-width: 1024px) {
    .map-layout {
        grid-template-columns: 1fr;
    }
    
    .map-container {
        height: 500px;
        min-height: 400px;
    }
    
    .temple-list-container {
        max-height: 350px;
    }
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
    }
    
    .filter-search,
    .filter-select-wrapper {
        width: 100%;
    }
    
    .map-hero-title {
        font-size: 1.75rem;
    }
    
    .map-container {
        height: 450px;
    }
    
    .map-info-card {
        left: 0.5rem;
        right: 0.5rem;
        padding: 1.25rem;
    }
    
    .info-card-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .temple-card {
        padding: 0.875rem;
    }
    
    .temple-thumb {
        width: 60px;
        height: 60px;
    }
    
    .temple-name {
        font-size: 0.875rem;
    }
    
    .map-container {
        height: 400px;
    }
}
</style>

<!-- Hero Section -->
<section class="map-hero">
    <div class="container">
        <div class="map-hero-content">
            <h1 class="map-hero-title">🗺️ Bản Đồ Di Sản</h1>
            <p class="map-hero-subtitle">Khám phá các chùa Khmer trên bản đồ tương tác</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="map-main">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-form">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchTemple" placeholder="Tìm tên chùa...">
                </div>
                
                <div class="filter-select-wrapper">
                    <select id="filterProvince" class="filter-select">
                        <option value="">Tất cả tỉnh thành</option>
                        <?php foreach ($provinces as $prov): ?>
                        <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" class="filter-reset" id="resetFilters">
                    <i class="fas fa-redo"></i> Đặt lại
                </button>
            </div>
        </div>

        <!-- Map Layout -->
        <div class="map-layout">
            <!-- Temple List Sidebar -->
            <aside class="temple-sidebar">
                <div class="temple-list-container">
                    <div class="section-header-row">
                        <h3 class="section-title">
                            <i class="fas fa-list"></i>
                            Danh sách chùa
                        </h3>
                        <span class="results-count" id="templeCount">
                            <strong><?= $totalTemples ?></strong> chùa
                        </span>
                    </div>
                    
                    <div id="templeList">
                        <?php if (!empty($temples)): ?>
                            <?php foreach ($temples as $temple): 
                                $imgPath = $temple['hinh_anh_chinh'];
                                $imgUrl = $imgPath ? ((strpos($imgPath, 'uploads/') === 0) ? '/DoAn_ChuyenNganh/' . $imgPath : UPLOAD_PATH . 'chua/' . $imgPath) : '';
                            ?>
                            <div class="temple-card" 
                                 data-id="<?= $temple['ma_chua'] ?>" 
                                 data-province="<?= htmlspecialchars($temple['tinh_thanh']) ?>" 
                                 data-name="<?= strtolower($temple['ten_chua']) ?>" 
                                 data-lat="<?= $temple['vi_do'] ?>" 
                                 data-lng="<?= $temple['kinh_do'] ?>">
                                <div class="temple-thumb">
                                    <?php if ($imgPath): ?>
                                    <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($temple['ten_chua']) ?>">
                                    <?php else: ?>
                                    <i class="fas fa-place-of-worship"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="temple-info">
                                    <div class="temple-name"><?= htmlspecialchars($temple['ten_chua']) ?></div>
                                    <div class="temple-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($temple['dia_chi'] ?: 'Chưa cập nhật') ?></span>
                                    </div>
                                    <?php if ($temple['tinh_thanh']): ?>
                                    <span class="temple-province"><?= htmlspecialchars($temple['tinh_thanh']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-map-marked-alt"></i></div>
                                <h3 class="empty-state-title">Chưa có dữ liệu</h3>
                                <p class="empty-state-desc">Hiện chưa có chùa nào trên bản đồ</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>

            <!-- Map Container -->
            <div class="map-container">
                <div id="map"></div>
                
                <div class="map-controls">
                    <button class="map-btn" id="zoomIn" title="Phóng to">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="map-btn" id="zoomOut" title="Thu nhỏ">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button class="map-btn" id="resetView" title="Đặt lại vị trí">
                        <i class="fas fa-crosshairs"></i>
                    </button>
                </div>
                
                <div class="map-info-card" id="infoCard">
                    <button class="info-card-close" onclick="closeInfo()">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="info-card-header">
                        <div class="info-card-image" id="infoImage"></div>
                        <div class="info-card-content">
                            <h3 class="info-card-name" id="infoName"></h3>
                            <div class="info-card-address" id="infoAddress"></div>
                        </div>
                    </div>
                    <div class="info-card-actions">
                        <a href="#" class="info-btn primary" id="infoLink">
                            <i class="fas fa-info-circle"></i> Xem chi tiết
                        </a>
                        <a href="#" class="info-btn secondary" id="infoDirection" target="_blank">
                            <i class="fas fa-directions"></i> Chỉ đường
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
const temples = <?= json_encode($temples) ?>;
const BASE_URL = '<?= BASE_URL ?>';
const UPLOAD_PATH = '<?= UPLOAD_PATH ?>';

// Initialize map
const map = L.map('map', { zoomControl: false }).setView([10.0, 105.8], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Custom marker icon
const templeIcon = L.divIcon({
    html: '<div class="marker-pin"><i class="fas fa-place-of-worship"></i></div>',
    className: 'custom-marker',
    iconSize: [40, 40],
    iconAnchor: [20, 40]
});

// Marker cluster
const markers = L.markerClusterGroup({ maxClusterRadius: 50 });
const markerMap = {};

// Add markers
temples.forEach(t => {
    const m = L.marker([t.vi_do, t.kinh_do], { icon: templeIcon })
        .on('click', () => showInfo(t));
    markerMap[t.ma_chua] = m;
    markers.addLayer(m);
});
map.addLayer(markers);

// Map controls
document.getElementById('zoomIn').onclick = () => map.zoomIn();
document.getElementById('zoomOut').onclick = () => map.zoomOut();
document.getElementById('resetView').onclick = () => {
    map.setView([10.0, 105.8], 8);
    closeInfo();
};

// Filter elements
const searchInput = document.getElementById('searchTemple');
const filterSelect = document.getElementById('filterProvince');
const templeCards = document.querySelectorAll('.temple-card');
const templeCountEl = document.getElementById('templeCount');

// Filter function
function filterTemples() {
    const search = searchInput.value.toLowerCase();
    const province = filterSelect.value;
    markers.clearLayers();
    let visibleCount = 0;
    
    templeCards.forEach(card => {
        const matchSearch = !search || card.dataset.name.includes(search);
        const matchProvince = !province || card.dataset.province === province;
        const match = matchSearch && matchProvince;
        
        card.style.display = match ? '' : 'none';
        
        if (match) {
            visibleCount++;
            const t = temples.find(x => x.ma_chua == card.dataset.id);
            if (t && markerMap[t.ma_chua]) {
                markers.addLayer(markerMap[t.ma_chua]);
            }
        }
    });
    
    templeCountEl.innerHTML = `<strong>${visibleCount}</strong> chùa`;
}

searchInput.oninput = filterTemples;
filterSelect.onchange = filterTemples;

// Reset filters
document.getElementById('resetFilters').onclick = () => {
    searchInput.value = '';
    filterSelect.value = '';
    filterTemples();
    map.setView([10.0, 105.8], 8);
    closeInfo();
};

// Temple card click
templeCards.forEach(card => {
    card.onclick = () => {
        const t = temples.find(x => x.ma_chua == card.dataset.id);
        if (t) showInfo(t);
    };
});

// Get image URL
function getImageUrl(imgPath) {
    if (!imgPath) return '';
    return imgPath.startsWith('uploads/') ? '/DoAn_ChuyenNganh/' + imgPath : UPLOAD_PATH + 'chua/' + imgPath;
}

// Show info card
function showInfo(t) {
    const imgUrl = getImageUrl(t.hinh_anh_chinh);
    
    document.getElementById('infoImage').innerHTML = t.hinh_anh_chinh 
        ? `<img src="${imgUrl}" alt="${t.ten_chua}">` 
        : '<i class="fas fa-place-of-worship"></i>';
    document.getElementById('infoName').textContent = t.ten_chua;
    document.getElementById('infoAddress').innerHTML = `<i class="fas fa-map-marker-alt"></i><span>${t.dia_chi || 'Chưa cập nhật địa chỉ'}</span>`;
    document.getElementById('infoLink').href = `${BASE_URL}/chua-khmer-chi-tiet.php?id=${t.ma_chua}`;
    document.getElementById('infoDirection').href = `https://www.google.com/maps/dir/?api=1&destination=${t.vi_do},${t.kinh_do}`;
    document.getElementById('infoCard').classList.add('show');
    
    map.setView([t.vi_do, t.kinh_do], 14);
    
    // Highlight active card
    templeCards.forEach(c => c.classList.toggle('active', c.dataset.id == t.ma_chua));
    
    // Scroll to active card
    const activeCard = document.querySelector('.temple-card.active');
    if (activeCard) {
        activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Close info card
function closeInfo() {
    document.getElementById('infoCard').classList.remove('show');
    templeCards.forEach(c => c.classList.remove('active'));
}

// Close info when clicking on map
map.on('click', e => {
    if (!e.originalEvent.target.closest('.marker-pin')) {
        closeInfo();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
