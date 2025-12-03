<?php
/**
 * Chi tiết Văn hóa Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id) {
    header('Location: van-hoa.php');
    exit;
}

// Lấy thông tin văn hóa
$vanHoa = $db->querySingle(
    "SELECT * FROM van_hoa WHERE id = ? AND trang_thai = 'xuat_ban'",
    [$id]
);

if(!$vanHoa) {
    header('Location: van-hoa.php');
    exit;
}

// Tăng lượt xem
$db->execute("UPDATE van_hoa SET luot_xem = luot_xem + 1 WHERE id = ?", [$id]);

// Lấy bài viết liên quan
$related = $db->query(
    "SELECT * FROM van_hoa 
     WHERE id != ? AND trang_thai = 'xuat_ban' 
     AND (danh_muc = ? OR MATCH(tieu_de, mo_ta) AGAINST(? IN NATURAL LANGUAGE MODE))
     ORDER BY luot_xem DESC 
     LIMIT 3",
    [$id, $vanHoa['danh_muc'], $vanHoa['tieu_de']]
);

$pageTitle = $vanHoa['tieu_de'];
include 'includes/header.php';
?>

<article class="article-detail">
    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <div class="article-breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="van-hoa.php">Văn hóa</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($vanHoa['tieu_de']); ?></span>
            </div>
            
            <?php if($vanHoa['danh_muc']): ?>
            <div class="article-category">
                <i class="fas fa-folder"></i>
                <?php echo htmlspecialchars($vanHoa['danh_muc']); ?>
            </div>
            <?php endif; ?>
            
            <h1 class="article-title"><?php echo htmlspecialchars($vanHoa['tieu_de']); ?></h1>
            
            <div class="article-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('d/m/Y', strtotime($vanHoa['ngay_tao'])); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-eye"></i>
                    <?php echo number_format($vanHoa['luot_xem']); ?> lượt xem
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <?php echo ceil(str_word_count(strip_tags($vanHoa['noi_dung'])) / 200); ?> phút đọc
                </div>
            </div>
            
            <div class="article-actions">
                <button class="action-btn" onclick="shareArticle()">
                    <i class="fas fa-share-alt"></i> Chia sẻ
                </button>
                <button class="action-btn" onclick="bookmarkArticle(<?php echo $id; ?>)">
                    <i class="far fa-bookmark"></i> Lưu
                </button>
                <button class="action-btn" onclick="printArticle()">
                    <i class="fas fa-print"></i> In
                </button>
            </div>
        </div>
    </section>
    
    <!-- Featured Image -->
    <?php if($vanHoa['hinh_anh']): ?>
    <section class="article-featured-image">
        <div class="container">
            <img src="<?php echo $vanHoa['hinh_anh']; ?>" 
                 alt="<?php echo htmlspecialchars($vanHoa['tieu_de']); ?>"
                 class="featured-image">
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Article Content -->
    <section class="article-content-section">
        <div class="container">
            <div class="article-layout">
                <!-- Main Content -->
                <div class="article-main">
                    <!-- Introduction -->
                    <?php if($vanHoa['mo_ta']): ?>
                    <div class="article-intro">
                        <?php echo nl2br(htmlspecialchars($vanHoa['mo_ta'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Content -->
                    <div class="article-content">
                        <?php echo $vanHoa['noi_dung']; ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if($vanHoa['tu_khoa']): ?>
                    <div class="article-tags">
                        <i class="fas fa-tags"></i>
                        <?php 
                        $tags = explode(',', $vanHoa['tu_khoa']);
                        foreach($tags as $tag): 
                        ?>
                        <a href="van-hoa.php?search=<?php echo urlencode(trim($tag)); ?>" class="tag">
                            <?php echo htmlspecialchars(trim($tag)); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share -->
                    <div class="article-share">
                        <h3>Chia sẻ bài viết</h3>
                        <div class="share-buttons">
                            <a href="#" class="share-btn facebook" onclick="shareOnFacebook(); return false;">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="#" class="share-btn twitter" onclick="shareOnTwitter(); return false;">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="#" class="share-btn telegram" onclick="shareOnTelegram(); return false;">
                                <i class="fab fa-telegram"></i> Telegram
                            </a>
                            <button class="share-btn copy" onclick="copyLink()">
                                <i class="fas fa-link"></i> Sao chép link
                            </button>
                        </div>
                    </div>
                    
                    <!-- Comments Section (Coming soon) -->
                    <div class="comments-section">
                        <h3><i class="fas fa-comments"></i> Bình luận</h3>
                        <div class="coming-soon-box">
                            <i class="fas fa-tools"></i>
                            <p>Tính năng bình luận đang được phát triển</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <aside class="article-sidebar">
                    <!-- Table of Contents -->
                    <div class="sidebar-card sticky-sidebar">
                        <h3><i class="fas fa-list"></i> Mục lục</h3>
                        <div id="tableOfContents" class="toc-list"></div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    
    <!-- Related Articles -->
    <?php if($related && count($related) > 0): ?>
    <section class="related-section">
        <div class="container">
            <h2 class="section-title">Bài viết liên quan</h2>
            <div class="content-grid" style="grid-template-columns: repeat(3, 1fr);">
                <?php foreach($related as $item): ?>
                <article class="content-card">
                    <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>" class="card-image-wrapper">
                        <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['tieu_de']); ?>" 
                             class="card-image">
                    </a>
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>">
                                <?php echo htmlspecialchars($item['tieu_de']); ?>
                            </a>
                        </h3>
                        <div class="card-meta">
                            <span><i class="fas fa-eye"></i> <?php echo number_format($item['luot_xem']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['ngay_tao'])); ?></span>
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
.article-header {
    padding: 120px 0 40px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.article-breadcrumb {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    font-size: 14px;
}

.article-breadcrumb a {
    color: rgba(255,255,255,0.9);
    transition: var(--transition-base);
}

.article-breadcrumb a:hover {
    color: white;
}

.article-breadcrumb i {
    color: rgba(255,255,255,0.6);
    font-size: 12px;
}

.article-category {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}

.article-title {
    font-size: 42px;
    color: white;
    margin-bottom: 24px;
    line-height: 1.2;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-bottom: 24px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0.9;
}

.article-actions {
    display: flex;
    gap: 12px;
}

.action-btn {
    padding: 10px 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 8px;
    color: white;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition-base);
}

.action-btn:hover {
    background: rgba(255,255,255,0.3);
}

.article-featured-image {
    padding: 40px 0;
    background: var(--gray-50);
}

.featured-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
}

.article-content-section {
    padding: 60px 0;
}

.article-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 40px;
}

.article-intro {
    padding: 24px;
    background: var(--gray-50);
    border-left: 4px solid var(--primary);
    border-radius: 8px;
    margin-bottom: 32px;
    font-size: 18px;
    line-height: 1.8;
    color: var(--gray-700);
}

.article-content {
    font-size: 17px;
    line-height: 1.8;
    color: var(--gray-800);
}

.article-content h2 {
    margin-top: 40px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--gray-200);
}

.article-content h3 {
    margin-top: 32px;
    margin-bottom: 12px;
    color: var(--primary);
}

.article-content p {
    margin-bottom: 20px;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 24px 0;
}

.article-content ul, .article-content ol {
    margin: 20px 0;
    padding-left: 24px;
}

.article-content li {
    margin-bottom: 8px;
}

.article-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    padding: 24px;
    background: var(--gray-50);
    border-radius: 12px;
    margin: 40px 0;
}

.article-tags i {
    color: var(--primary);
}

.tag {
    padding: 6px 16px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition-base);
}

.tag:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.article-share {
    padding: 32px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    margin: 40px 0;
}

.article-share h3 {
    margin-bottom: 20px;
}

.share-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.share-btn {
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition-base);
}

.share-btn:hover {
    transform: translateY(-2px);
}

.share-btn.facebook { background: #1877F2; }
.share-btn.twitter { background: #1DA1F2; }
.share-btn.telegram { background: #0088cc; }
.share-btn.copy { background: var(--gray-700); border: none; cursor: pointer; }

.comments-section {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 2px solid var(--gray-200);
}

.coming-soon-box {
    text-align: center;
    padding: 60px 24px;
    background: var(--gray-50);
    border-radius: 12px;
    margin-top: 24px;
}

.coming-soon-box i {
    font-size: 48px;
    color: var(--gray-300);
    margin-bottom: 16px;
}

.sidebar-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.sidebar-card h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    color: var(--gray-900);
}

.sticky-sidebar {
    position: sticky;
    top: 100px;
}

.toc-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.toc-list a {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    color: var(--gray-700);
    transition: var(--transition-base);
}

.toc-list a:hover {
    background: var(--gray-100);
    color: var(--primary);
}

.related-section {
    padding: 60px 0;
    background: var(--gray-50);
}

@media (max-width: 768px) {
    .article-title {
        font-size: 28px;
    }
    
    .article-layout {
        grid-template-columns: 1fr;
    }
    
    .article-sidebar {
        display: none;
    }
    
    .share-buttons {
        flex-direction: column;
    }
    
    .content-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
// Generate Table of Contents
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.article-content');
    const toc = document.getElementById('tableOfContents');
    const headings = content.querySelectorAll('h2, h3');
    
    if(headings.length > 0) {
        headings.forEach((heading, index) => {
            const id = 'heading-' + index;
            heading.id = id;
            
            const link = document.createElement('a');
            link.href = '#' + id;
            link.textContent = heading.textContent;
            link.style.paddingLeft = heading.tagName === 'H3' ? '20px' : '0';
            
            toc.appendChild(link);
        });
    } else {
        toc.innerHTML = '<p style="color: var(--gray-500); font-size: 14px;">Không có mục lục</p>';
    }
});

// Share functions
function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    } else {
        alert('Trình duyệt không hỗ trợ chia sẻ');
    }
}

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

function bookmarkArticle(id) {
    // TODO: Implement bookmark functionality
    alert('Tính năng đang phát triển');
}

function printArticle() {
    window.print();
}
</script>

<?php include 'includes/footer.php'; ?>
