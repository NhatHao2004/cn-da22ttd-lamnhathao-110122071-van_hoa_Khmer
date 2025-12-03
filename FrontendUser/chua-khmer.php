<?php
/**
 * Danh sách Chùa Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Chùa Khmer';
$db = Database::getInstance();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Tìm kiếm & lọc
$search = trim($_GET['search'] ?? '');
$tinh_thanh = $_GET['tinh_thanh'] ?? '';
$loai_chua = $_GET['loai_chua'] ?? '';

// Build query
$where = ["trang_thai = 'hoat_dong'"];
$params = [];

if($search) {
    $where[] = "(ten_chua LIKE ? OR ten_tieng_khmer LIKE ? OR dia_chi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($tinh_thanh) {
    $where[] = "tinh_thanh = ?";
    $params[] = $tinh_thanh;
}

if($loai_chua) {
    $where[] = "loai_chua = ?";
    $params[] = $loai_chua;
}

$whereClause = implode(' AND ', $where);

// Đếm tổng số
$total = $db->count('chua_khmer', $whereClause, $params);
$totalPages = ceil($total / $limit);

// Lấy dữ liệu
$chuaList = $db->query(
    "SELECT * FROM chua_khmer WHERE $whereClause ORDER BY luot_xem DESC LIMIT $limit OFFSET $offset",
    $params
);

// Lấy danh sách tỉnh thành
$provinces = $db->query("SELECT DISTINCT tinh_thanh FROM chua_khmer WHERE trang_thai = 'hoat_dong' ORDER BY tinh_thanh");

// Lấy loại chùa
$loaiChuaList = ['Chùa Khmer', 'Chùa Mahatup', 'Chùa cổ', 'Chùa mới'];

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><i class="fas fa-place-of-worship"></i> Chùa Khmer Nam Bộ</h1>
            <p>Khám phá kiến trúc độc đáo và tâm linh sâu sắc của các ngôi chùa Khmer</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-section">
            <div class="search-box-large">
                <form method="GET" action="">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Tìm kiếm chùa theo tên, địa chỉ..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-map-marker-alt"></i> Tỉnh thành</label>
                    <select name="tinh_thanh" onchange="filterChange(this)">
                        <option value="">Tất cả tỉnh thành</option>
                        <?php foreach($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province['tinh_thanh']); ?>"
                                <?php echo $tinh_thanh === $province['tinh_thanh'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($province['tinh_thanh']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-dharmachakra"></i> Loại chùa</label>
                    <select name="loai_chua" onchange="filterChange(this)">
                        <option value="">Tất cả loại</option>
                        <?php foreach($loaiChuaList as $loai): ?>
                        <option value="<?php echo htmlspecialchars($loai); ?>"
                                <?php echo $loai_chua === $loai ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loai); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Results info -->
        <div class="results-info">
            <p>Tìm thấy <strong><?php echo number_format($total); ?></strong> ngôi chùa</p>
        </div>
        
        <!-- Content Grid -->
        <?php if($chuaList && count($chuaList) > 0): ?>
        <div class="content-grid temple-grid">
            <?php foreach($chuaList as $chua): ?>
            <article class="temple-card">
                <a href="chua-khmer-chi-tiet.php?id=<?php echo $chua['id']; ?>" class="temple-image-wrapper">
                    <img src="<?php echo $chua['hinh_anh'] ?: 'assets/images/placeholder-temple.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($chua['ten_chua']); ?>" 
                         class="temple-image"
                         loading="lazy">
                    <div class="temple-overlay">
                        <span class="temple-type">
                            <i class="fas fa-dharmachakra"></i>
                            <?php echo htmlspecialchars($chua['loai_chua'] ?: 'Chùa Khmer'); ?>
                        </span>
                    </div>
                </a>
                
                <div class="temple-body">
                    <h3 class="temple-title">
                        <a href="chua-khmer-chi-tiet.php?id=<?php echo $chua['id']; ?>">
                            <?php echo htmlspecialchars($chua['ten_chua']); ?>
                        </a>
                    </h3>
                    
                    <p class="temple-name-khmer">
                        <?php echo htmlspecialchars($chua['ten_tieng_khmer']); ?>
                    </p>
                    
                    <div class="temple-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($chua['tinh_thanh']); ?>
                        </div>
                        <?php if($chua['nam_xay_dung']): ?>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            Xây dựng <?php echo $chua['nam_xay_dung']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="temple-footer">
                        <div class="temple-stats">
                            <span><i class="fas fa-eye"></i> <?php echo number_format($chua['luot_xem']); ?></span>
                        </div>
                        <a href="chua-khmer-chi-tiet.php?id=<?php echo $chua['id']; ?>" class="btn-view-detail">
                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tinh_thanh ? '&tinh_thanh=' . urlencode($tinh_thanh) : ''; ?><?php echo $loai_chua ? '&loai_chua=' . urlencode($loai_chua) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tinh_thanh ? '&tinh_thanh=' . urlencode($tinh_thanh) : ''; ?><?php echo $loai_chua ? '&loai_chua=' . urlencode($loai_chua) : ''; ?>" 
               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tinh_thanh ? '&tinh_thanh=' . urlencode($tinh_thanh) : ''; ?><?php echo $loai_chua ? '&loai_chua=' . urlencode($loai_chua) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-place-of-worship"></i>
            <h3>Không tìm thấy chùa nào</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
            <a href="chua-khmer.php" class="btn btn-primary">Xem tất cả</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group select {
    padding: 12px 16px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 15px;
    background: white;
    cursor: pointer;
    transition: var(--transition-base);
}

.filter-group select:focus {
    outline: none;
    border-color: var(--primary);
}

.temple-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.temple-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.temple-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.temple-image-wrapper {
    position: relative;
    display: block;
    overflow: hidden;
    height: 220px;
}

.temple-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-base);
}

.temple-card:hover .temple-image {
    transform: scale(1.1);
}

.temple-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent);
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 16px;
}

.temple-type {
    padding: 6px 12px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.temple-body {
    padding: 20px;
}

.temple-title {
    font-size: 18px;
    margin-bottom: 8px;
}

.temple-title a {
    color: var(--gray-900);
}

.temple-name-khmer {
    font-size: 14px;
    color: var(--gray-600);
    margin-bottom: 16px;
    font-style: italic;
}

.temple-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
}

.info-item i {
    color: var(--secondary);
    width: 16px;
}

.temple-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
}

.temple-stats {
    display: flex;
    gap: 12px;
    font-size: 13px;
    color: var(--gray-500);
}

.btn-view-detail {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--primary);
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition-base);
}

.btn-view-detail:hover {
    gap: 10px;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .temple-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function filterChange(select) {
    const form = select.closest('form') || document.createElement('form');
    if(!select.closest('form')) {
        form.method = 'GET';
        form.action = window.location.pathname;
        
        // Add all current params
        const params = new URLSearchParams(window.location.search);
        params.forEach((value, key) => {
            if(key !== 'page') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        });
        
        // Update the changed value
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = select.name;
        input.value = select.value;
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
