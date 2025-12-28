<?php
/**
 * Danh sách Bài học tiếng Khmer từ CSDL
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Danh Sách Bài Học Tiếng Khmer';

$pdo = getDBConnection();

// Lấy bài học theo cấp độ
$levels = [
    'co_ban' => 'Cơ Bản',
    'trung_cap' => 'Trung Cấp',
    'nang_cao' => 'Nâng Cao'
];

$lessonsByLevel = [];
foreach ($levels as $key => $label) {
    $stmt = $pdo->prepare("SELECT * FROM bai_hoc WHERE cap_do = ? AND trang_thai IN ('hien_thi', 'xuat_ban') ORDER BY thu_tu ASC");
    $stmt->execute([$key]);
    $lessonsByLevel[$key] = $stmt->fetchAll();
}

// Đếm tổng số bài học
$totalStmt = $pdo->query("SELECT COUNT(*) as total FROM bai_hoc WHERE trang_thai IN ('hien_thi', 'xuat_ban')");
$totalLessons = $totalStmt->fetch()['total'] ?? 0;
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Lessons Hero Section ===== */
.lessons-hero {
    min-height: 40vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFCC80 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.lessons-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.lessons-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.2);
}

.lessons-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto 1rem;
    line-height: 1.6;
}

.lessons-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFE0B2 100%);
    min-height: 60vh;
}

.level-section {
    margin-bottom: 3rem;
}

.level-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.level-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.level-badge {
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 2px solid #1a1a1a;
}

.level-badge.co-ban {
    background: linear-gradient(135deg, #9CCC65 0%, #8BC34A 100%);
    color: #ffffff;
}

.level-badge.trung-cap {
    background: linear-gradient(135deg, #FFB74D 0%, #FFA726 100%);
    color: #ffffff;
}

.level-badge.nang-cao {
    background: linear-gradient(135deg, #FF8A65 0%, #FF7043 100%);
    color: #ffffff;
}

.lesson-count {
    font-size: 0.9375rem;
    color: #1a1a1a;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.8);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

/* Lessons Grid - Table Layout */
.lessons-grid {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.25);
    border: 3px solid #1a1a1a;
}

/* Table Header */
.lessons-table-header {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: #FFE0B2;
    border-bottom: 3px solid #1a1a1a;
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a1a;
}

.lessons-table-header-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lessons-table-header-cell i {
    color: #FF9800;
    font-size: 1.125rem;
}

/* Table Body */
.lessons-table-body {
    display: flex;
    flex-direction: column;
}

/* Lesson Card - Table Row */
.lesson-card {
    display: grid;
    grid-template-columns: 280px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: rgba(255, 255, 255, 0.8);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    text-decoration: none;
    align-items: center;
    cursor: pointer;
}

.lesson-card:last-child {
    border-bottom: none;
}

.lesson-card:hover {
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
}

/* Image Column */
.lesson-card-image {
    position: relative;
    width: 100%;
    height: 140px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #FF9800, #F57C00);
    flex-shrink: 0;
    border: 2px solid #1a1a1a;
}

.lesson-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.lesson-card:hover .lesson-card-image img {
    transform: scale(1.1);
}

.lesson-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: rgba(255,255,255,0.3);
}

.lesson-card-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    padding: 0.375rem 0.75rem;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    border: 2px solid #1a1a1a;
}

.lesson-card-badge i {
    font-size: 0.75rem;
    color: #FF9800;
}

/* Content Column */
.lesson-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0;
    margin-top: -5rem;
}

.lesson-card-header {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.lesson-card-meta-row {
    font-size: 0.8125rem;
    color: #2d2d2d;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.lesson-card-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    background: #FFE0B2;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.lesson-card-meta-item i {
    color: #FF9800;
    font-size: 0.875rem;
}

.lesson-card-title {
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

.lesson-card-desc {
    font-size: 0.9375rem;
    color: #2d2d2d;
    line-height: 1.6;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Action Column */
.lesson-card-footer {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 0.5rem 0;
    border: none;
    margin: 0;
    margin-bottom: -7rem;
}

.lesson-card-btn {
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
    transition: all 0.3s ease;
    white-space: nowrap;
    width: 100%;
    text-decoration: none;
}

.lesson-card:hover .lesson-card-btn {
    background: #F57C00;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
}

.lesson-card-btn i {
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.lesson-card:hover .lesson-card-btn i {
    transform: translateX(3px);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.25);
    border: 3px solid #1a1a1a;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: #FFE0B2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #FF9800;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 800;
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
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.empty-state-btn:hover {
    background: #F57C00;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
}

/* Tablet - Show simplified table */
@media (min-width: 769px) and (max-width: 1024px) {
    .lessons-table-header {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .lesson-card {
        grid-template-columns: 220px 1fr 180px;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }
    
    .lesson-card-image {
        height: 120px;
    }
    
    .lesson-card-title {
        font-size: 1rem;
    }
    
    .lesson-card-btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    /* Table Header - Hide on mobile */
    .lessons-table-header {
        display: none;
    }
    
    /* Table Body */
    .lessons-grid {
        border-radius: 12px;
    }
    
    .lesson-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 0;
    }
    
    .lesson-card:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .lesson-card:last-child {
        border-radius: 0 0 12px 12px;
    }
    
    .lesson-card-image {
        width: 100%;
        height: 180px;
    }
    
    .lesson-card-content {
        gap: 0.75rem;
        margin-top: 0;
    }
    
    .lesson-card-title {
        font-size: 1rem;
        -webkit-line-clamp: 2;
    }
    
    .lesson-card-desc {
        font-size: 0.875rem;
    }
    
    .lesson-card-meta-row {
        font-size: 0.8125rem;
        gap: 0.75rem;
    }
    
    .lesson-card-footer {
        width: 100%;
        margin-bottom: 0;
    }
    
    .lesson-card-btn {
        width: 100%;
        padding: 0.875rem 1.25rem;
    }
    
    .level-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .lessons-hero-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .lesson-card {
        padding: 1rem;
    }
    
    .lesson-card-image {
        height: 160px;
        border-radius: 10px;
    }
    
    .lesson-card-title {
        font-size: 0.9375rem;
    }
    
    .lesson-card-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .lesson-card-badge {
        padding: 0.375rem 0.625rem;
        font-size: 0.6875rem;
    }
}
</style>

<!-- Hero Section -->
<section class="lessons-hero">
    <div class="container">
        <div class="lessons-hero-content">
            <h1 class="lessons-hero-title">📚 Bài Học về Phật giáo Nam tông Khmer</h1>
            <p class="lessons-hero-subtitle">Tìm hiểu truyền thống tín ngưỡng và giá trị giáo dục cộng đồng Khmer Nam Bộ</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="lessons-main">
    <div class="container">
        <?php if ($totalLessons == 0): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="empty-state-title">Chưa có bài học nào</h3>
            <p class="empty-state-desc">Hiện tại chưa có bài học nào trong hệ thống. Vui lòng quay lại sau!</p>
            <a href="<?= BASE_URL ?>" class="empty-state-btn">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>
        <?php else: ?>
            <?php foreach ($levels as $levelKey => $levelLabel): ?>
                <?php if (!empty($lessonsByLevel[$levelKey])): ?>
                <div class="level-section">
                    <div class="level-header">
                        <h2 class="level-title">
                            <span class="level-badge <?= str_replace('_', '-', $levelKey) ?>">
                                <?= $levelLabel ?>
                            </span>
                        </h2>
                        <span class="lesson-count">
                            <?= count($lessonsByLevel[$levelKey]) ?> bài học
                        </span>
                    </div>
                    
                    <div class="lessons-grid">
                        <!-- Table Header -->
                        <div class="lessons-table-header">
                            <div class="lessons-table-header-cell">
                                Hình bài học
                            </div>
                            <div class="lessons-table-header-cell">
                                Tên bài học
                            </div>
                            <div class="lessons-table-header-cell">
                                Nút thao tác
                            </div>
                        </div>
                        
                        <!-- Table Body -->
                        <div class="lessons-table-body">
                        <?php foreach ($lessonsByLevel[$levelKey] as $lesson): 
                            // Xử lý đường dẫn ảnh
                            $imgPath = $lesson['hinh_anh'] ?? '';
                            if (empty($imgPath)) {
                                $imgUrl = '';
                            } elseif (strpos($imgPath, 'http') === 0) {
                                // URL đầy đủ
                                $imgUrl = $imgPath;
                            } elseif (strpos($imgPath, 'uploads/') === 0) {
                                // Đã có uploads/ ở đầu
                                $imgUrl = '/DoAn_ChuyenNganh/' . $imgPath;
                            } elseif (strpos($imgPath, '/') !== false) {
                                // Có dấu / trong path
                                $imgUrl = BASE_URL . '/' . $imgPath;
                            } else {
                                // Chỉ có tên file (như: lesson_1766238111_2995.jpg)
                                $imgUrl = '/DoAn_ChuyenNganh/uploads/lessons/' . $imgPath;
                            }
                        ?>
                        <div class="lesson-card" onclick="window.location.href='<?= BASE_URL ?>/bai-hoc-chi-tiet.php?id=<?= $lesson['ma_bai_hoc'] ?>'">
                            <!-- Image Column -->
                            <div class="lesson-card-image">
                                <?php if (!empty($imgUrl)): ?>
                                    <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($lesson['tieu_de']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="lesson-card-placeholder" style="display:none;"><i class="fas fa-graduation-cap"></i></div>
                                <?php else: ?>
                                    <div class="lesson-card-placeholder"><i class="fas fa-graduation-cap"></i></div>
                                <?php endif; ?>
                                <div class="lesson-card-badge">
                                    <i class="fas fa-clock"></i>
                                    <?= $lesson['thoi_luong'] ?? 30 ?> phút
                                </div>
                            </div>
                            
                            <!-- Content Column -->
                            <div class="lesson-card-content">
                                <div class="lesson-card-header">
                                    <div class="lesson-card-meta-row">
                                        <span class="lesson-card-meta-item">
                                            <i class="fas fa-star"></i>
                                            <?= $lesson['diem_thuong'] ?? 10 ?> điểm
                                        </span>
                                        <?php if (isset($lesson['luot_hoc']) && $lesson['luot_hoc'] > 0): ?>
                                        <span class="lesson-card-meta-item">
                                            <i class="fas fa-users"></i>
                                            <?= number_format($lesson['luot_hoc']) ?> học viên
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="lesson-card-title"><?= htmlspecialchars($lesson['tieu_de']) ?></h3>
                                </div>
                            </div>
                            
                            <!-- Action Column -->
                            <div class="lesson-card-footer">
                                <span class="lesson-card-btn">
                                    Bắt đầu học <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
