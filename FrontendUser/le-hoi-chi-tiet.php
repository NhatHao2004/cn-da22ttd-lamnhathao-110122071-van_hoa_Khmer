<?php
/**
 * Chi tiết Lễ hội Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id) {
    header('Location: le-hoi.php');
    exit;
}

// Lấy thông tin lễ hội
$leHoi = $db->querySingle(
    "SELECT * FROM le_hoi WHERE id = ? AND trang_thai = 'hien_thi'",
    [$id]
);

if(!$leHoi) {
    header('Location: le-hoi.php');
    exit;
}

// Tăng lượt xem
$db->execute("UPDATE le_hoi SET luot_xem = luot_xem + 1 WHERE id = ?", [$id]);

// Check nếu đang diễn ra
$isUpcoming = strtotime($leHoi['ngay_bat_dau']) >= strtotime('today');
$isOngoing = strtotime($leHoi['ngay_bat_dau']) <= strtotime('today') && 
             strtotime($leHoi['ngay_ket_thuc']) >= strtotime('today');
$isPast = strtotime($leHoi['ngay_ket_thuc']) < strtotime('today');

// Lấy lễ hội liên quan
$related = $db->query(
    "SELECT * FROM le_hoi 
     WHERE id != ? AND trang_thai = 'hien_thi'
     ORDER BY ngay_bat_dau DESC 
     LIMIT 3",
    [$id]
);

$pageTitle = $leHoi['ten_le_hoi'];
include 'includes/header.php';
?>

<article class="festival-detail">
    <!-- Festival Header -->
    <section class="festival-header">
        <div class="container">
            <div class="festival-breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="le-hoi.php">Lễ hội</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($leHoi['ten_le_hoi']); ?></span>
            </div>
            
            <div class="festival-header-content">
                <?php if($isUpcoming): ?>
                <div class="festival-status upcoming">
                    <i class="fas fa-calendar-plus"></i> Sắp diễn ra
                </div>
                <?php elseif($isOngoing): ?>
                <div class="festival-status ongoing">
                    <i class="fas fa-play-circle"></i> Đang diễn ra
                </div>
                <?php else: ?>
                <div class="festival-status past">
                    <i class="fas fa-check-circle"></i> Đã kết thúc
                </div>
                <?php endif; ?>
                
                <h1 class="festival-title"><?php echo htmlspecialchars($leHoi['ten_le_hoi']); ?></h1>
                <p class="festival-name-khmer"><?php echo htmlspecialchars($leHoi['ten_tieng_khmer']); ?></p>
                
                <div class="festival-quick-info">
                    <div class="quick-info-item">
                        <i class="fas fa-calendar"></i>
                        <span>
                            <?php echo date('d/m/Y', strtotime($leHoi['ngay_bat_dau'])); ?>
                            <?php if($leHoi['ngay_ket_thuc']): ?>
                            - <?php echo date('d/m/Y', strtotime($leHoi['ngay_ket_thuc'])); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="quick-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($leHoi['dia_diem']); ?></span>
                    </div>
                    <div class="quick-info-item">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($leHoi['luot_xem']); ?> lượt xem</span>
                    </div>
                </div>
                
                <?php if($isUpcoming): ?>
                <div class="countdown-wrapper">
                    <div class="countdown" id="countdown" 
                         data-date="<?php echo date('Y-m-d H:i:s', strtotime($leHoi['ngay_bat_dau'])); ?>">
                        <div class="countdown-item">
                            <div class="countdown-value" id="days">00</div>
                            <div class="countdown-label">Ngày</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="hours">00</div>
                            <div class="countdown-label">Giờ</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="minutes">00</div>
                            <div class="countdown-label">Phút</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="seconds">00</div>
                            <div class="countdown-label">Giây</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Image -->
    <?php if($leHoi['hinh_anh']): ?>
    <section class="festival-featured-image">
        <div class="container">
            <img src="<?php echo $leHoi['hinh_anh']; ?>" 
                 alt="<?php echo htmlspecialchars($leHoi['ten_le_hoi']); ?>"
                 class="featured-image">
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Festival Content -->
    <section class="festival-content-section">
        <div class="container">
            <div class="festival-layout">
                <!-- Main Content -->
                <div class="festival-main">
                    <!-- Description -->
                    <div class="content-card">
                        <h2><i class="fas fa-info-circle"></i> Giới thiệu</h2>
                        <div class="festival-description">
                            <?php echo nl2br(htmlspecialchars($leHoi['mo_ta'])); ?>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <?php if($leHoi['noi_dung']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-book-open"></i> Chi tiết lễ hội</h2>
                        <div class="festival-content">
                            <?php echo $leHoi['noi_dung']; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Meaning -->
                    <?php if($leHoi['y_nghia']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-heart"></i> Ý nghĩa</h2>
                        <div class="festival-meaning">
                            <?php echo nl2br(htmlspecialchars($leHoi['y_nghia'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Video -->
                    <?php if($leHoi['video']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-video"></i> Video</h2>
                        <div class="video-wrapper">
                            <iframe 
                                src="<?php echo htmlspecialchars($leHoi['video']); ?>" 
                                frameborder="0" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share -->
                    <div class="article-share">
                        <h3>Chia sẻ lễ hội</h3>
                        <div class="share-buttons">
                            <button class="share-btn facebook" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                            <button class="share-btn twitter" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button class="share-btn telegram" onclick="shareOnTelegram()">
                                <i class="fab fa-telegram"></i> Telegram
                            </button>
                            <button class="share-btn copy" onclick="copyLink()">
                                <i class="fas fa-link"></i> Sao chép link
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <aside class="festival-sidebar">
                    <!-- Event Info -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-calendar-check"></i> Thông tin sự kiện</h3>
                        <div class="event-info">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-calendar-day"></i> Ngày bắt đầu
                                </div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y', strtotime($leHoi['ngay_bat_dau'])); ?>
                                </div>
                            </div>
                            
                            <?php if($leHoi['ngay_ket_thuc']): ?>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-calendar-times"></i> Ngày kết thúc
                                </div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y', strtotime($leHoi['ngay_ket_thuc'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt"></i> Địa điểm
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($leHoi['dia_diem']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($isUpcoming): ?>
                        <button class="btn btn-primary btn-block" onclick="remindMe(<?php echo $id; ?>)">
                            <i class="fas fa-bell"></i> Nhắc nhở tôi
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Calendar Widget -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-calendar"></i> Lịch lễ hội</h3>
                        <div class="calendar-widget">
                            <div class="calendar-month">
                                <?php 
                                $monthNames = ['', 'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                                              'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                                echo $monthNames[(int)date('n', strtotime($leHoi['ngay_bat_dau']))];
                                echo ' ' . date('Y', strtotime($leHoi['ngay_bat_dau']));
                                ?>
                            </div>
                            <div class="calendar-day">
                                <?php echo date('d', strtotime($leHoi['ngay_bat_dau'])); ?>
                            </div>
                        </div>
                        <a href="le-hoi.php" class="btn btn-outline btn-block">
                            Xem lịch lễ hội
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    
    <!-- Related Festivals -->
    <?php if($related && count($related) > 0): ?>
    <section class="related-section">
        <div class="container">
            <h2 class="section-title">Lễ hội khác</h2>
            <div class="content-grid" style="grid-template-columns: repeat(3, 1fr);">
                <?php foreach($related as $item): ?>
                <article class="content-card">
                    <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>" class="card-image-wrapper">
                        <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-festival.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['ten_le_hoi']); ?>" 
                             class="card-image">
                        <span class="card-badge" style="background: var(--danger);">
                            <?php echo date('d/m/Y', strtotime($item['ngay_bat_dau'])); ?>
                        </span>
                    </a>
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="le-hoi-chi-tiet.php?id=<?php echo $item['id']; ?>">
                                <?php echo htmlspecialchars($item['ten_le_hoi']); ?>
                            </a>
                        </h3>
                        <p style="font-size: 14px; color: var(--gray-600); margin-bottom: 8px;">
                            <?php echo htmlspecialchars($item['ten_tieng_khmer']); ?>
                        </p>
                        <div class="card-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['dia_diem']); ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</article>

<style>
.festival-header {
    padding: 120px 0 40px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.festival-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}

.festival-status.upcoming { background: rgba(16, 185, 129, 0.3); }
.festival-status.ongoing { background: rgba(59, 130, 246, 0.3); }
.festival-status.past { background: rgba(107, 114, 128, 0.3); }

.countdown-wrapper {
    margin-top: 32px;
}

.countdown {
    display: flex;
    justify-content: center;
    gap: 24px;
}

.countdown-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 12px;
    min-width: 80px;
}

.countdown-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
}

.countdown-label {
    font-size: 12px;
    margin-top: 8px;
    opacity: 0.9;
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 12px;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--gray-600);
    font-weight: 500;
}

.info-value {
    font-size: 15px;
    color: var(--gray-900);
    font-weight: 600;
    padding-left: 28px;
}

.calendar-widget {
    text-align: center;
    padding: 24px;
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
    border-radius: 12px;
    margin-bottom: 16px;
}

.calendar-month {
    font-size: 14px;
    margin-bottom: 8px;
}

.calendar-day {
    font-size: 48px;
    font-weight: 700;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

@media (max-width: 768px) {
    .countdown {
        gap: 12px;
    }
    
    .countdown-item {
        padding: 12px;
        min-width: 60px;
    }
    
    .countdown-value {
        font-size: 24px;
    }
}
</style>

<script>
// Countdown timer
<?php if($isUpcoming): ?>
const countdownDate = new Date('<?php echo date('Y-m-d H:i:s', strtotime($leHoi['ngay_bat_dau'])); ?>').getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const distance = countdownDate - now;
    
    if(distance < 0) {
        document.getElementById('countdown').innerHTML = '<p>Lễ hội đã bắt đầu!</p>';
        return;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('days').textContent = String(days).padStart(2, '0');
    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);
<?php endif; ?>

function shareOnFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
}

function shareOnTwitter() {
    window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title), '_blank');
}

function shareOnTelegram() {
    window.open('https://t.me/share/url?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title), '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Đã sao chép link!');
    });
}

function remindMe(festivalId) {
    alert('Tính năng nhắc nhở đang được phát triển');
}
</script>

<?php include 'includes/footer.php'; ?>
