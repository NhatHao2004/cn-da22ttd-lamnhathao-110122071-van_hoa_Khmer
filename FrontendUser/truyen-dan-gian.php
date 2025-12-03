<?php
/**
 * Danh sách Truyện dân gian Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Truyện dân gian Khmer';
$db = Database::getInstance();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Tìm kiếm & lọc
$search = trim($_GET['search'] ?? '');
$the_loai = $_GET['the_loai'] ?? '';

// Build query
$where = ["trang_thai = 'hien_thi'"];
$params = [];

if($search) {
    $where[] = "(tieu_de LIKE ? OR tac_gia LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($the_loai) {
    $where[] = "the_loai = ?";
    $params[] = $the_loai;
}

$whereClause = implode(' AND ', $where);

// Đếm tổng số
$total = $db->count('truyen_dan_gian', $whereClause, $params);
$totalPages = ceil($total / $limit);

// Lấy dữ liệu
$truyenList = $db->query(
    "SELECT * FROM truyen_dan_gian WHERE $whereClause ORDER BY luot_xem DESC LIMIT $limit OFFSET $offset",
    $params
);

// Lấy thể loại
$theLoaiList = $db->query("SELECT DISTINCT the_loai FROM truyen_dan_gian WHERE trang_thai = 'hien_thi' AND the_loai IS NOT NULL ORDER BY the_loai");

// Truyện nổi bật
$featured = $db->query(
    "SELECT * FROM truyen_dan_gian 
     WHERE trang_thai = 'hien_thi' 
     ORDER BY luot_xem DESC 
     LIMIT 3"
);

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><i class="fas fa-book"></i> Truyện dân gian Khmer</h1>
            <p>Khám phá kho tàng truyện dân gian đầy ý nghĩa của người Khmer Nam Bộ</p>
        </div>
    </div>
</section>

<!-- Featured Stories -->
<?php if($featured && count($featured) > 0): ?>
<section class="featured-stories">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-star"></i> Truyện nổi bật</h2>
        <div class="featured-stories-grid">
            <?php foreach($featured as $item): ?>
            <div class="featured-story-card">
                <div class="featured-story-image" style="background-image: url('<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-story.jpg'; ?>')">
                    <div class="featured-story-overlay">
                        <div class="story-type">
                            <i class="fas fa-book-open"></i>
                            <?php echo htmlspecialchars($item['the_loai'] ?: 'Truyện dân gian'); ?>
                        </div>
                    </div>
                </div>
                <div class="featured-story-content">
                    <h3><?php echo htmlspecialchars($item['tieu_de']); ?></h3>
                    <?php if($item['tac_gia']): ?>
                    <p class="story-author">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($item['tac_gia']); ?>
                    </p>
                    <?php endif; ?>
                    <p class="story-excerpt">
                        <?php echo htmlspecialchars(mb_substr($item['tom_tat'], 0, 120)); ?>...
                    </p>
                    <div class="story-meta-small">
                        <span><i class="fas fa-eye"></i> <?php echo number_format($item['luot_xem']); ?></span>
                        <?php if($item['thoi_luong']): ?>
                        <span><i class="fas fa-clock"></i> <?php echo $item['thoi_luong']; ?> phút</span>
                        <?php endif; ?>
                    </div>
                    <a href="truyen-chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-book-reader"></i> Đọc truyện
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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
                        placeholder="Tìm kiếm truyện theo tên, tác giả..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
            </div>
            
            <div class="filter-chips">
                <a href="truyen-dan-gian.php" class="filter-chip <?php echo !$the_loai ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i> Tất cả
                </a>
                <?php foreach($theLoaiList as $tl): ?>
                <a href="?the_loai=<?php echo urlencode($tl['the_loai']); ?>" 
                   class="filter-chip <?php echo $the_loai === $tl['the_loai'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($tl['the_loai']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Results info -->
        <div class="results-info">
            <p>Tìm thấy <strong><?php echo number_format($total); ?></strong> truyện</p>
        </div>
        
        <!-- Stories Grid -->
        <?php if($truyenList && count($truyenList) > 0): ?>
        <div class="stories-grid">
            <?php foreach($truyenList as $truyen): ?>
            <article class="story-card">
                <a href="truyen-chi-tiet.php?id=<?php echo $truyen['id']; ?>" class="story-image-wrapper">
                    <img src="<?php echo $truyen['hinh_anh'] ?: 'assets/images/placeholder-story.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($truyen['tieu_de']); ?>" 
                         class="story-image"
                         loading="lazy">
                    <div class="story-badge">
                        <?php echo htmlspecialchars($truyen['the_loai'] ?: 'Truyện dân gian'); ?>
                    </div>
                </a>
                
                <div class="story-body">
                    <h3 class="story-title">
                        <a href="truyen-chi-tiet.php?id=<?php echo $truyen['id']; ?>">
                            <?php echo htmlspecialchars($truyen['tieu_de']); ?>
                        </a>
                    </h3>
                    
                    <?php if($truyen['tac_gia']): ?>
                    <p class="story-author">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($truyen['tac_gia']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="story-excerpt">
                        <?php echo htmlspecialchars(mb_substr($truyen['tom_tat'], 0, 100)); ?>...
                    </p>
                    
                    <div class="story-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format($truyen['luot_xem']); ?></span>
                        <?php if($truyen['thoi_luong']): ?>
                        <span><i class="fas fa-clock"></i> <?php echo $truyen['thoi_luong']; ?> phút</span>
                        <?php endif; ?>
                        <?php if($truyen['co_audio']): ?>
                        <span><i class="fas fa-headphones"></i> Có audio</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="story-actions">
                        <a href="truyen-chi-tiet.php?id=<?php echo $truyen['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-book-reader"></i> Đọc truyện
                        </a>
                        <button class="btn-icon" onclick="bookmarkStory(<?php echo $truyen['id']; ?>)">
                            <i class="far fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $the_loai ? '&the_loai=' . urlencode($the_loai) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $the_loai ? '&the_loai=' . urlencode($the_loai) : ''; ?>" 
               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $the_loai ? '&the_loai=' . urlencode($the_loai) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>Không tìm thấy truyện nào</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
            <a href="truyen-dan-gian.php" class="btn btn-primary">Xem tất cả</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.featured-stories {
    padding: 60px 0;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.featured-stories-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 32px;
}

.featured-story-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.featured-story-image {
    height: 250px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.featured-story-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent);
    padding: 16px;
}

.story-type {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
}

.featured-story-content {
    padding: 24px;
}

.featured-story-content h3 {
    font-size: 20px;
    margin-bottom: 12px;
}

.story-author {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--gray-600);
    margin-bottom: 12px;
}

.story-excerpt {
    font-size: 14px;
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 16px;
}

.story-meta-small {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: var(--gray-500);
}

.story-meta-small i {
    color: var(--primary);
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.story-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.story-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.story-image-wrapper {
    position: relative;
    display: block;
    overflow: hidden;
    height: 240px;
}

.story-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-base);
}

.story-card:hover .story-image {
    transform: scale(1.1);
}

.story-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 12px;
    background: rgba(99, 102, 241, 0.95);
    backdrop-filter: blur(10px);
    color: white;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
}

.story-body {
    padding: 20px;
}

.story-title {
    font-size: 18px;
    margin-bottom: 12px;
}

.story-title a {
    color: var(--gray-900);
}

.story-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: var(--gray-500);
}

.story-meta i {
    color: var(--primary);
}

.story-actions {
    display: flex;
    gap: 12px;
}

.story-actions .btn {
    flex: 1;
}

.btn-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    color: var(--gray-700);
    cursor: pointer;
    transition: var(--transition-base);
}

.btn-icon:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .featured-stories-grid {
        grid-template-columns: 1fr;
    }
    
    .stories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function bookmarkStory(storyId) {
    <?php if(!isset($_SESSION['user_id'])): ?>
    alert('Vui lòng đăng nhập để lưu truyện');
    window.location.href = 'login.php';
    <?php else: ?>
    // TODO: Implement bookmark functionality
    alert('Tính năng đang được phát triển');
    <?php endif; ?>
}
</script>

<?php include 'includes/footer.php'; ?>
