<?php
/**
 * Danh sách Lễ hội Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Lễ hội Khmer';
$db = Database::getInstance();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Tìm kiếm & lọc
$search = trim($_GET['search'] ?? '');
$time_filter = $_GET['time'] ?? 'all'; // all, upcoming, past

// Build query
$where = ["trang_thai = 'hien_thi'"];
$params = [];

if($search) {
    $where[] = "(ten_le_hoi LIKE ? OR ten_tieng_khmer LIKE ? OR dia_diem LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($time_filter === 'upcoming') {
    $where[] = "ngay_bat_dau >= CURDATE()";
} elseif($time_filter === 'past') {
    $where[] = "ngay_ket_thuc < CURDATE()";
}

$whereClause = implode(' AND ', $where);

// Đếm tổng số
$total = $db->count('le_hoi', $whereClause, $params);
$totalPages = ceil($total / $limit);

// Lấy dữ liệu
$leHoiList = $db->query(
    "SELECT * FROM le_hoi WHERE $whereClause ORDER BY ngay_bat_dau ASC LIMIT $limit OFFSET $offset",
    $params
);

// Lấy lễ hội nổi bật sắp diễn ra
$featured = $db->query(
    "SELECT * FROM le_hoi 
     WHERE ngay_bat_dau >= CURDATE() AND trang_thai = 'hien_thi'
     ORDER BY ngay_bat_dau ASC 
     LIMIT 3"
);

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><i class="fas fa-calendar-alt"></i> Lễ hội Khmer Nam Bộ</h1>
            <p>Khám phá và tham gia các lễ hội truyền thống đặc sắc của người Khmer</p>
        </div>
    </div>
</section>

<!-- Featured Festivals -->
<?php if($featured && count($featured) > 0): ?>
<section class="featured-festivals">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-star"></i> Lễ hội sắp diễn ra</h2>
        <div class="featured-grid">
            <?php foreach($featured as $item): ?>
            <div class="featured-festival-card">
                <div class="featured-date">
                    <div class="date-day"><?php echo date('d', strtotime($item['ngay_bat_dau'])); ?></div>
                    <div class="date-month">Tháng <?php echo date('m', strtotime($item['ngay_bat_dau'])); ?></div>
                </div>
                <div class="featured-image" style="background-image: url('<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-festival.jpg'; ?>')"></div>
                <div class="featured-content">
                    <h3><?php echo htmlspecialchars($item['ten_le_hoi']); ?></h3>
                    <p class="festival-name-khmer"><?php echo htmlspecialchars($item['ten_tieng_khmer']); ?></p>
                    <div class="festival-info">
                        <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['dia_diem']); ?></div>
                        <div><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['ngay_bat_dau'])); ?></div>
                    </div>
                    <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                        Xem chi tiết <i class="fas fa-arrow-right"></i>
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
                        placeholder="Tìm kiếm lễ hội theo tên, địa điểm..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
            </div>
            
            <div class="filter-chips">
                <a href="?time=all" class="filter-chip <?php echo $time_filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Tất cả
                </a>
                <a href="?time=upcoming" class="filter-chip <?php echo $time_filter === 'upcoming' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-plus"></i> Sắp diễn ra
                </a>
                <a href="?time=past" class="filter-chip <?php echo $time_filter === 'past' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Đã diễn ra
                </a>
            </div>
        </div>
        
        <!-- Results info -->
        <div class="results-info">
            <p>Tìm thấy <strong><?php echo number_format($total); ?></strong> lễ hội</p>
        </div>
        
        <!-- Content Grid -->
        <?php if($leHoiList && count($leHoiList) > 0): ?>
        <div class="festivals-timeline">
            <?php foreach($leHoiList as $item): 
                $isUpcoming = strtotime($item['ngay_bat_dau']) >= strtotime('today');
                $isPast = strtotime($item['ngay_ket_thuc']) < strtotime('today');
            ?>
            <article class="festival-card <?php echo $isPast ? 'past' : ''; ?>">
                <div class="festival-date-badge">
                    <div class="date-large"><?php echo date('d', strtotime($item['ngay_bat_dau'])); ?></div>
                    <div class="date-small">Tháng <?php echo date('m/Y', strtotime($item['ngay_bat_dau'])); ?></div>
                    <?php if($isUpcoming): ?>
                    <span class="date-status upcoming">Sắp diễn ra</span>
                    <?php elseif($isPast): ?>
                    <span class="date-status past">Đã kết thúc</span>
                    <?php else: ?>
                    <span class="date-status ongoing">Đang diễn ra</span>
                    <?php endif; ?>
                </div>
                
                <div class="festival-image-wrapper">
                    <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>">
                        <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-festival.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['ten_le_hoi']); ?>" 
                             class="festival-image"
                             loading="lazy">
                    </a>
                </div>
                
                <div class="festival-content">
                    <h3 class="festival-title">
                        <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>">
                            <?php echo htmlspecialchars($item['ten_le_hoi']); ?>
                        </a>
                    </h3>
                    
                    <p class="festival-name-khmer">
                        <?php echo htmlspecialchars($item['ten_tieng_khmer']); ?>
                    </p>
                    
                    <p class="festival-description">
                        <?php echo htmlspecialchars(mb_substr($item['mo_ta'], 0, 150)); ?>...
                    </p>
                    
                    <div class="festival-meta">
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($item['dia_diem']); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <?php echo date('d/m/Y', strtotime($item['ngay_bat_dau'])); ?>
                            <?php if($item['ngay_ket_thuc']): ?>
                            - <?php echo date('d/m/Y', strtotime($item['ngay_ket_thuc'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="festival-actions">
                        <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                            Xem chi tiết
                        </a>
                        <?php if($isUpcoming): ?>
                        <button class="btn btn-outline" onclick="remindMe(<?php echo $item['id']; ?>)">
                            <i class="fas fa-bell"></i> Nhắc nhở
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $time_filter !== 'all' ? '&time=' . $time_filter : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $time_filter !== 'all' ? '&time=' . $time_filter : ''; ?>" 
               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $time_filter !== 'all' ? '&time=' . $time_filter : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Không tìm thấy lễ hội nào</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
            <a href="le-hoi.php" class="btn btn-primary">Xem tất cả</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.featured-festivals {
    padding: 60px 0;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.featured-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 32px;
}

.featured-festival-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    position: relative;
}

.featured-date {
    position: absolute;
    top: 16px;
    left: 16px;
    background: var(--danger);
    color: white;
    padding: 12px;
    border-radius: 12px;
    text-align: center;
    z-index: 10;
    box-shadow: var(--shadow-md);
}

.date-day {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
}

.date-month {
    font-size: 12px;
    margin-top: 4px;
}

.featured-image {
    height: 200px;
    background-size: cover;
    background-position: center;
}

.featured-content {
    padding: 24px;
}

.featured-content h3 {
    font-size: 20px;
    margin-bottom: 8px;
}

.festival-name-khmer {
    font-size: 14px;
    color: var(--gray-600);
    font-style: italic;
    margin-bottom: 16px;
}

.festival-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 14px;
    color: var(--gray-600);
}

.festival-info i {
    color: var(--danger);
    width: 16px;
}

.festivals-timeline {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.festival-card {
    display: grid;
    grid-template-columns: 120px 250px 1fr;
    gap: 24px;
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.festival-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.festival-card.past {
    opacity: 0.7;
}

.festival-date-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}

.date-large {
    font-size: 36px;
    font-weight: 700;
    line-height: 1;
}

.date-small {
    font-size: 12px;
    margin-top: 4px;
    opacity: 0.9;
}

.date-status {
    margin-top: 12px;
    padding: 4px 8px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.festival-image-wrapper {
    border-radius: 12px;
    overflow: hidden;
    height: 180px;
}

.festival-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-base);
}

.festival-card:hover .festival-image {
    transform: scale(1.05);
}

.festival-content {
    display: flex;
    flex-direction: column;
}

.festival-title {
    font-size: 22px;
    margin-bottom: 8px;
}

.festival-title a {
    color: var(--gray-900);
}

.festival-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 16px;
    flex: 1;
}

.festival-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 14px;
    color: var(--gray-600);
}

.festival-actions {
    display: flex;
    gap: 12px;
}

@media (max-width: 768px) {
    .featured-grid {
        grid-template-columns: 1fr;
    }
    
    .festival-card {
        grid-template-columns: 1fr;
    }
    
    .festival-date-badge {
        display: inline-flex;
        flex-direction: row;
        gap: 12px;
        width: fit-content;
    }
    
    .date-large {
        font-size: 24px;
    }
}
</style>

<script>
function remindMe(festivalId) {
    // TODO: Implement reminder functionality
    alert('Tính năng nhắc nhở đang được phát triển');
}
</script>

<?php include 'includes/footer.php'; ?>
