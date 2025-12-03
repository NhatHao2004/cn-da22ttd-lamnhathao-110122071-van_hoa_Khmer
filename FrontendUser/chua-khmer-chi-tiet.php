<?php
/**
 * Chi tiết Chùa Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id) {
    header('Location: chua-khmer.php');
    exit;
}

// Lấy thông tin chùa
$chua = $db->querySingle(
    "SELECT * FROM chua_khmer WHERE id = ? AND trang_thai = 'hoat_dong'",
    [$id]
);

if(!$chua) {
    header('Location: chua-khmer.php');
    exit;
}

// Tăng lượt xem
$db->execute("UPDATE chua_khmer SET luot_xem = luot_xem + 1 WHERE id = ?", [$id]);

// Lấy chùa gần đó (cùng tỉnh)
$nearby = $db->query(
    "SELECT * FROM chua_khmer 
     WHERE id != ? AND tinh_thanh = ? AND trang_thai = 'hoat_dong'
     ORDER BY luot_xem DESC 
     LIMIT 3",
    [$id, $chua['tinh_thanh']]
);

$pageTitle = $chua['ten_chua'];
include 'includes/header.php';
?>

<article class="temple-detail">
    <!-- Temple Header -->
    <section class="temple-header">
        <div class="container">
            <div class="temple-breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="chua-khmer.php">Chùa Khmer</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($chua['ten_chua']); ?></span>
            </div>
            
            <div class="temple-header-content">
                <div class="temple-badge">
                    <i class="fas fa-dharmachakra"></i>
                    <?php echo htmlspecialchars($chua['loai_chua'] ?: 'Chùa Khmer'); ?>
                </div>
                
                <h1 class="temple-title"><?php echo htmlspecialchars($chua['ten_chua']); ?></h1>
                <p class="temple-name-khmer"><?php echo htmlspecialchars($chua['ten_tieng_khmer']); ?></p>
                
                <div class="temple-quick-info">
                    <div class="quick-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($chua['dia_chi'] . ', ' . $chua['tinh_thanh']); ?></span>
                    </div>
                    <?php if($chua['nam_xay_dung']): ?>
                    <div class="quick-info-item">
                        <i class="fas fa-calendar"></i>
                        <span>Xây dựng năm <?php echo $chua['nam_xay_dung']; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="quick-info-item">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($chua['luot_xem']); ?> lượt xem</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Temple Gallery -->
    <?php if($chua['hinh_anh']): ?>
    <section class="temple-gallery">
        <div class="container">
            <div class="main-image">
                <img src="<?php echo $chua['hinh_anh']; ?>" 
                     alt="<?php echo htmlspecialchars($chua['ten_chua']); ?>"
                     class="gallery-image">
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Temple Content -->
    <section class="temple-content-section">
        <div class="container">
            <div class="temple-layout">
                <!-- Main Content -->
                <div class="temple-main">
                    <!-- Description -->
                    <div class="content-card">
                        <h2><i class="fas fa-info-circle"></i> Giới thiệu</h2>
                        <div class="temple-description">
                            <?php echo nl2br(htmlspecialchars($chua['mo_ta'])); ?>
                        </div>
                    </div>
                    
                    <!-- History -->
                    <?php if($chua['lich_su']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-book-open"></i> Lịch sử</h2>
                        <div class="temple-history">
                            <?php echo nl2br(htmlspecialchars($chua['lich_su'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Architecture -->
                    <?php if($chua['kien_truc']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-building"></i> Kiến trúc</h2>
                        <div class="temple-architecture">
                            <?php echo nl2br(htmlspecialchars($chua['kien_truc'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Map -->
                    <?php if($chua['toa_do']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-map"></i> Bản đồ</h2>
                        <div class="temple-map" id="templeMap"></div>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($chua['dia_chi'] . ', ' . $chua['tinh_thanh']); ?>" 
                           target="_blank" 
                           class="btn btn-primary"
                           style="margin-top: 16px;">
                            <i class="fas fa-directions"></i>
                            Chỉ đường trên Google Maps
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <aside class="temple-sidebar">
                    <!-- Contact Info -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-phone"></i> Thông tin liên hệ</h3>
                        <div class="contact-info">
                            <?php if($chua['dien_thoai']): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo $chua['dien_thoai']; ?>">
                                    <?php echo htmlspecialchars($chua['dien_thoai']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($chua['dia_chi']); ?></span>
                            </div>
                            
                            <?php if($chua['email']): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo $chua['email']; ?>">
                                    <?php echo htmlspecialchars($chua['email']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Share -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-share-alt"></i> Chia sẻ</h3>
                        <div class="share-buttons-vertical">
                            <button class="share-btn facebook" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                            <button class="share-btn twitter" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button class="share-btn copy" onclick="copyLink()">
                                <i class="fas fa-link"></i> Sao chép link
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    
    <!-- Nearby Temples -->
    <?php if($nearby && count($nearby) > 0): ?>
    <section class="nearby-section">
        <div class="container">
            <h2 class="section-title">Chùa gần đây</h2>
            <div class="content-grid" style="grid-template-columns: repeat(3, 1fr);">
                <?php foreach($nearby as $item): ?>
                <article class="temple-card">
                    <a href="chua-khmer-chi-tiet.php?id=<?php echo $item['id']; ?>" class="temple-image-wrapper">
                        <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-temple.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['ten_chua']); ?>" 
                             class="temple-image">
                    </a>
                    <div class="temple-body">
                        <h3 class="temple-title">
                            <a href="chua-khmer-chi-tiet.php?id=<?php echo $item['id']; ?>">
                                <?php echo htmlspecialchars($item['ten_chua']); ?>
                            </a>
                        </h3>
                        <p class="temple-name-khmer"><?php echo htmlspecialchars($item['ten_tieng_khmer']); ?></p>
                        <div class="temple-stats">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['tinh_thanh']); ?></span>
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
.temple-header {
    padding: 120px 0 40px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.temple-breadcrumb {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    font-size: 14px;
}

.temple-breadcrumb a {
    color: rgba(255,255,255,0.9);
}

.temple-breadcrumb i {
    color: rgba(255,255,255,0.6);
    font-size: 12px;
}

.temple-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}

.temple-title {
    font-size: 48px;
    color: white;
    margin-bottom: 12px;
}

.temple-name-khmer {
    font-size: 24px;
    font-style: italic;
    opacity: 0.95;
    margin-bottom: 24px;
}

.temple-quick-info {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

.quick-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.temple-gallery {
    padding: 40px 0;
    background: var(--gray-900);
}

.main-image {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.gallery-image {
    width: 100%;
    max-height: 600px;
    object-fit: cover;
}

.temple-content-section {
    padding: 60px 0;
}

.temple-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
}

.content-card {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
    margin-bottom: 24px;
}

.content-card h2 {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    color: var(--secondary);
}

.temple-description,
.temple-history,
.temple-architecture {
    font-size: 16px;
    line-height: 1.8;
    color: var(--gray-700);
}

.temple-map {
    width: 100%;
    height: 400px;
    background: var(--gray-200);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-500);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 15px;
}

.contact-item i {
    color: var(--secondary);
    margin-top: 2px;
    width: 20px;
}

.contact-item a {
    color: var(--primary);
    font-weight: 500;
}

.share-buttons-vertical {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.share-buttons-vertical .share-btn {
    width: 100%;
    justify-content: center;
}

.nearby-section {
    padding: 60px 0;
    background: var(--gray-50);
}

@media (max-width: 768px) {
    .temple-title {
        font-size: 32px;
    }
    
    .temple-layout {
        grid-template-columns: 1fr;
    }
    
    .content-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function shareOnFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
}

function shareOnTwitter() {
    window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title), '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Đã sao chép link!');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
