<?php
/**
 * Học tiếng Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Học tiếng Khmer';
$db = Database::getInstance();

// Lấy tham số
$cap_do = $_GET['cap_do'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build query
$where = ["trang_thai = 'xuat_ban'"];
$params = [];

if($cap_do) {
    $where[] = "cap_do = ?";
    $params[] = $cap_do;
}

if($search) {
    $where[] = "(tieu_de LIKE ? OR mo_ta LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Lấy bài học
$baiHocList = $db->query(
    "SELECT * FROM bai_hoc WHERE $whereClause ORDER BY thu_tu ASC, ngay_tao DESC",
    $params
);

// Thống kê
$stats = [
    'tong' => $db->count('bai_hoc', "trang_thai = 'xuat_ban'"),
    'co_ban' => $db->count('bai_hoc', "trang_thai = 'xuat_ban' AND cap_do = 'co_ban'"),
    'trung_cap' => $db->count('bai_hoc', "trang_thai = 'xuat_ban' AND cap_do = 'trung_cap'"),
    'nang_cao' => $db->count('bai_hoc', "trang_thai = 'xuat_ban' AND cap_do = 'nang_cao'")
];

// Nếu đã đăng nhập, lấy tiến trình học
$user_progress = [];
if(isset($_SESSION['user_id'])) {
    // TODO: Query user progress from database
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><i class="fas fa-graduation-cap"></i> Học tiếng Khmer</h1>
            <p>Khóa học tiếng Khmer từ cơ bản đến nâng cao với phương pháp hiện đại</p>
        </div>
    </div>
</section>

<!-- Learning Stats -->
<section class="learning-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card-large" onclick="window.location='?cap_do='">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-light));">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['tong']; ?></div>
                    <div class="stat-label">Tổng bài học</div>
                </div>
            </div>
            
            <div class="stat-card-large" onclick="window.location='?cap_do=co_ban'">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['co_ban']; ?></div>
                    <div class="stat-label">Cơ bản</div>
                </div>
            </div>
            
            <div class="stat-card-large" onclick="window.location='?cap_do=trung_cap'">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['trung_cap']; ?></div>
                    <div class="stat-label">Trung cấp</div>
                </div>
            </div>
            
            <div class="stat-card-large" onclick="window.location='?cap_do=nang_cao'">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--danger), #dc2626);">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['nang_cao']; ?></div>
                    <div class="stat-label">Nâng cao</div>
                </div>
            </div>
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
                        placeholder="Tìm kiếm bài học..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <?php if($cap_do): ?>
                    <input type="hidden" name="cap_do" value="<?php echo htmlspecialchars($cap_do); ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>
            </div>
            
            <div class="filter-chips">
                <a href="hoc-tieng-khmer.php" class="filter-chip <?php echo !$cap_do ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i> Tất cả
                </a>
                <a href="?cap_do=co_ban" class="filter-chip <?php echo $cap_do === 'co_ban' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Cơ bản
                </a>
                <a href="?cap_do=trung_cap" class="filter-chip <?php echo $cap_do === 'trung_cap' ? 'active' : ''; ?>">
                    <i class="fas fa-star-half-alt"></i> Trung cấp
                </a>
                <a href="?cap_do=nang_cao" class="filter-chip <?php echo $cap_do === 'nang_cao' ? 'active' : ''; ?>">
                    <i class="fas fa-crown"></i> Nâng cao
                </a>
            </div>
        </div>
        
        <!-- Lessons Grid -->
        <?php if($baiHocList && count($baiHocList) > 0): ?>
        <div class="lessons-grid">
            <?php 
            $capDoColors = [
                'co_ban' => 'var(--success)',
                'trung_cap' => 'var(--warning)',
                'nang_cao' => 'var(--danger)'
            ];
            $capDoLabels = [
                'co_ban' => 'Cơ bản',
                'trung_cap' => 'Trung cấp',
                'nang_cao' => 'Nâng cao'
            ];
            $capDoIcons = [
                'co_ban' => 'fa-star',
                'trung_cap' => 'fa-star-half-alt',
                'nang_cao' => 'fa-crown'
            ];
            
            foreach($baiHocList as $baiHoc): 
            ?>
            <article class="lesson-card">
                <a href="bai-hoc-chi-tiet.php?id=<?php echo $baiHoc['id']; ?>" class="lesson-image-wrapper">
                    <img src="<?php echo $baiHoc['hinh_anh'] ?: 'assets/images/placeholder-lesson.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($baiHoc['tieu_de']); ?>" 
                         class="lesson-image"
                         loading="lazy">
                    <div class="lesson-overlay">
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </a>
                
                <div class="lesson-body">
                    <div class="lesson-header-row">
                        <span class="lesson-level" style="background: <?php echo $capDoColors[$baiHoc['cap_do']]; ?>;">
                            <i class="fas <?php echo $capDoIcons[$baiHoc['cap_do']]; ?>"></i>
                            <?php echo $capDoLabels[$baiHoc['cap_do']]; ?>
                        </span>
                        <?php if($baiHoc['thu_tu']): ?>
                        <span class="lesson-number">Bài <?php echo $baiHoc['thu_tu']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="lesson-title">
                        <a href="bai-hoc-chi-tiet.php?id=<?php echo $baiHoc['id']; ?>">
                            <?php echo htmlspecialchars($baiHoc['tieu_de']); ?>
                        </a>
                    </h3>
                    
                    <p class="lesson-description">
                        <?php echo htmlspecialchars(mb_substr($baiHoc['mo_ta'], 0, 100)); ?>...
                    </p>
                    
                    <div class="lesson-meta">
                        <span><i class="fas fa-clock"></i> <?php echo $baiHoc['thoi_luong']; ?> phút</span>
                        <span><i class="fas fa-users"></i> <?php echo number_format($baiHoc['luot_hoc']); ?></span>
                        <?php if($baiHoc['so_tu_vung']): ?>
                        <span><i class="fas fa-book"></i> <?php echo $baiHoc['so_tu_vung']; ?> từ</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Progress bar if logged in -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="lesson-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                        <span class="progress-text">Chưa học</span>
                    </div>
                    <?php endif; ?>
                    
                    <a href="bai-hoc-chi-tiet.php?id=<?php echo $baiHoc['id']; ?>" class="btn btn-primary btn-block">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <i class="fas fa-play"></i> Bắt đầu học
                        <?php else: ?>
                            <i class="fas fa-eye"></i> Xem chi tiết
                        <?php endif; ?>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>Không tìm thấy bài học nào</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
            <a href="hoc-tieng-khmer.php" class="btn btn-primary">Xem tất cả</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Learning Path CTA -->
<?php if(!isset($_SESSION['user_id'])): ?>
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">Sẵn sàng học tiếng Khmer?</h2>
        <p class="cta-description">
            Đăng ký ngay để theo dõi tiến trình học tập và nhận chứng chỉ
        </p>
        <div class="hero-buttons">
            <a href="register.php" class="btn btn-primary" style="background: white; color: var(--primary);">
                <i class="fas fa-user-plus"></i> Đăng ký ngay
            </a>
            <a href="login.php" class="btn btn-outline" style="background: rgba(255,255,255,0.2); border-color: white; color: white;">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.learning-stats {
    padding: 40px 0;
    background: var(--gray-50);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.stat-card-large {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: var(--transition-base);
    cursor: pointer;
}

.stat-card-large:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 28px;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-600);
    margin-top: 4px;
}

.lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.lesson-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.lesson-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.lesson-image-wrapper {
    position: relative;
    display: block;
    overflow: hidden;
    height: 200px;
}

.lesson-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-base);
}

.lesson-card:hover .lesson-image {
    transform: scale(1.1);
}

.lesson-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition-base);
}

.lesson-card:hover .lesson-overlay {
    opacity: 1;
}

.play-button {
    width: 64px;
    height: 64px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--primary);
}

.lesson-body {
    padding: 20px;
}

.lesson-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.lesson-level {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.lesson-number {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-500);
}

.lesson-title {
    font-size: 18px;
    margin-bottom: 12px;
}

.lesson-title a {
    color: var(--gray-900);
}

.lesson-description {
    color: var(--gray-600);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.lesson-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: var(--gray-500);
}

.lesson-meta i {
    color: var(--primary);
}

.lesson-progress {
    margin-bottom: 16px;
}

.progress-bar {
    height: 6px;
    background: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success), var(--primary));
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 12px;
    color: var(--gray-600);
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .lessons-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
