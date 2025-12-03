<?php
/**
 * Danh sách Văn hóa Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Văn hóa Khmer';
$db = Database::getInstance();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Tìm kiếm & lọc
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';

// Build query
$where = ["trang_thai = 'xuat_ban'"];
$params = [];

if($search) {
    $where[] = "(tieu_de LIKE ? OR mo_ta LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($category) {
    $where[] = "danh_muc = ?";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);

// Đếm tổng số
$total = $db->count('van_hoa', $whereClause, $params);
$totalPages = ceil($total / $limit);

// Lấy dữ liệu
$vanHoa = $db->query(
    "SELECT * FROM van_hoa WHERE $whereClause ORDER BY ngay_tao DESC LIMIT $limit OFFSET $offset",
    $params
);

// Lấy danh mục
$categories = $db->query("SELECT DISTINCT danh_muc FROM van_hoa WHERE trang_thai = 'xuat_ban' AND danh_muc IS NOT NULL ORDER BY danh_muc");

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><i class="fas fa-book-open"></i> Văn hóa Khmer Nam Bộ</h1>
            <p>Khám phá lịch sử, phong tục, nghệ thuật và tín ngưỡng của người Khmer</p>
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
                        placeholder="Tìm kiếm bài viết văn hóa..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
            </div>
            
            <div class="filter-chips">
                <a href="van-hoa.php" class="filter-chip <?php echo !$category ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i> Tất cả
                </a>
                <?php foreach($categories as $cat): ?>
                <a href="?category=<?php echo urlencode($cat['danh_muc']); ?>" 
                   class="filter-chip <?php echo $category === $cat['danh_muc'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['danh_muc']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Results info -->
        <div class="results-info">
            <p>Tìm thấy <strong><?php echo number_format($total); ?></strong> bài viết</p>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid">
                    <i class="fas fa-th-large"></i>
                </button>
                <button class="view-btn" data-view="list">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Grid -->
        <?php if($vanHoa && count($vanHoa) > 0): ?>
        <div class="content-grid" id="contentGrid">
            <?php foreach($vanHoa as $item): ?>
            <article class="content-card">
                <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>" class="card-image-wrapper">
                    <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($item['tieu_de']); ?>" 
                         class="card-image"
                         loading="lazy">
                    <?php if($item['noi_bat']): ?>
                    <span class="card-badge badge-featured">
                        <i class="fas fa-star"></i> Nổi bật
                    </span>
                    <?php endif; ?>
                </a>
                
                <div class="card-body">
                    <?php if($item['danh_muc']): ?>
                    <span class="card-category"><?php echo htmlspecialchars($item['danh_muc']); ?></span>
                    <?php endif; ?>
                    
                    <h3 class="card-title">
                        <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>">
                            <?php echo htmlspecialchars($item['tieu_de']); ?>
                        </a>
                    </h3>
                    
                    <p class="card-description">
                        <?php echo htmlspecialchars(mb_substr($item['mo_ta'], 0, 120)); ?>...
                    </p>
                    
                    <div class="card-footer">
                        <div class="card-meta">
                            <span><i class="fas fa-eye"></i> <?php echo number_format($item['luot_xem']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['ngay_tao'])); ?></span>
                        </div>
                        <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn-read-more">
                            Đọc thêm <i class="fas fa-arrow-right"></i>
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
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" 
               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Không tìm thấy kết quả</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
            <a href="van-hoa.php" class="btn btn-primary">Xem tất cả</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.page-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 120px 0 60px;
    text-align: center;
}

.page-header-content h1 {
    font-size: 42px;
    color: white;
    margin-bottom: 16px;
}

.page-header-content p {
    font-size: 18px;
    opacity: 0.95;
}

.filters-section {
    margin-bottom: 32px;
}

.search-box-large {
    margin-bottom: 24px;
}

.search-box-large form {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    padding: 8px 8px 8px 20px;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.search-box-large i {
    color: var(--gray-400);
    font-size: 20px;
}

.search-box-large input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 16px;
    padding: 8px;
}

.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.filter-chip {
    padding: 10px 20px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 24px;
    font-weight: 500;
    transition: var(--transition-base);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.filter-chip:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-chip.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.results-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--gray-200);
}

.view-toggle {
    display: flex;
    gap: 8px;
}

.view-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    color: var(--gray-600);
    cursor: pointer;
    transition: var(--transition-base);
}

.view-btn:hover,
.view-btn.active {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--gray-50);
}

.card-image-wrapper {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: 12px;
    margin-bottom: 16px;
}

.card-image {
    width: 100%;
    height: 240px;
    object-fit: cover;
    transition: var(--transition-base);
}

.content-card:hover .card-image {
    transform: scale(1.05);
}

.card-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    background: var(--secondary);
    color: white;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.badge-featured {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.card-category {
    display: inline-block;
    padding: 4px 12px;
    background: var(--gray-100);
    color: var(--gray-700);
    font-size: 12px;
    font-weight: 600;
    border-radius: 16px;
    margin-bottom: 12px;
}

.card-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 16px;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
}

.btn-read-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--primary);
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition-base);
}

.btn-read-more:hover {
    gap: 10px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 48px;
}

.pagination-btn {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition-base);
}

.pagination-btn:hover,
.pagination-btn.active {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 80px 24px;
}

.empty-state i {
    font-size: 64px;
    color: var(--gray-300);
    margin-bottom: 24px;
}

.empty-state h3 {
    font-size: 24px;
    margin-bottom: 12px;
}

.empty-state p {
    color: var(--gray-600);
    margin-bottom: 24px;
}

@media (max-width: 768px) {
    .page-header-content h1 {
        font-size: 32px;
    }
    
    .results-info {
        flex-direction: column;
        gap: 16px;
    }
    
    .filter-chips {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 8px;
    }
    
    .filter-chip {
        white-space: nowrap;
    }
}
</style>

<script>
// View toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const view = this.dataset.view;
        const grid = document.getElementById('contentGrid');
        
        if(view === 'list') {
            grid.style.gridTemplateColumns = '1fr';
        } else {
            grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
