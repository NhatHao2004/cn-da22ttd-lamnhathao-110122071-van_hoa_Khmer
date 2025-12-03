<?php
/**
 * Chi tiết Truyện dân gian - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id) {
    header('Location: truyen-dan-gian.php');
    exit;
}

$db = Database::getInstance();

// Lấy thông tin truyện
$truyen = $db->findById('truyen_dan_gian', $id);

if(!$truyen || $truyen['trang_thai'] !== 'hien_thi') {
    header('Location: truyen-dan-gian.php');
    exit;
}

// Cập nhật lượt xem
$db->update('truyen_dan_gian', ['luot_xem' => $truyen['luot_xem'] + 1], $id);

// Truyện liên quan
$related = $db->query(
    "SELECT * FROM truyen_dan_gian 
     WHERE id != ? 
     AND trang_thai = 'hien_thi' 
     AND (the_loai = ? OR tac_gia = ?)
     ORDER BY luot_xem DESC 
     LIMIT 4",
    [$id, $truyen['the_loai'], $truyen['tac_gia']]
);

// Check bookmark status
$isBookmarked = false;
if(isset($_SESSION['user_id'])) {
    // TODO: Check from database
}

$pageTitle = $truyen['tieu_de'] . ' - Truyện dân gian Khmer';
include 'includes/header.php';
?>

<article class="story-detail">
    <!-- Header -->
    <section class="story-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="truyen-dan-gian.php">Truyện dân gian</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($truyen['tieu_de']); ?></span>
            </div>
            
            <div class="story-header-content">
                <div class="story-header-left">
                    <div class="story-category-badge">
                        <i class="fas fa-book-open"></i>
                        <?php echo htmlspecialchars($truyen['the_loai'] ?: 'Truyện dân gian'); ?>
                    </div>
                    <h1 class="story-header-title"><?php echo htmlspecialchars($truyen['tieu_de']); ?></h1>
                    
                    <?php if($truyen['tac_gia']): ?>
                    <p class="story-author-large">
                        <i class="fas fa-user"></i>
                        Tác giả: <strong><?php echo htmlspecialchars($truyen['tac_gia']); ?></strong>
                    </p>
                    <?php endif; ?>
                    
                    <div class="story-header-meta">
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span><?php echo number_format($truyen['luot_xem']); ?> lượt xem</span>
                        </div>
                        <?php if($truyen['thoi_luong']): ?>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $truyen['thoi_luong']; ?> phút đọc</span>
                        </div>
                        <?php endif; ?>
                        <?php if($truyen['co_audio']): ?>
                        <div class="meta-item">
                            <i class="fas fa-headphones"></i>
                            <span>Có audio</span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('d/m/Y', strtotime($truyen['ngay_tao'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="story-actions-header">
                        <button class="btn btn-primary" onclick="scrollToStory()">
                            <i class="fas fa-book-reader"></i> Đọc truyện
                        </button>
                        
                        <?php if($truyen['co_audio']): ?>
                        <button class="btn btn-secondary" id="audioToggleBtn">
                            <i class="fas fa-play"></i> Nghe audio
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline <?php echo $isBookmarked ? 'active' : ''; ?>" 
                                id="bookmarkBtn" 
                                onclick="toggleBookmark(<?php echo $id; ?>)">
                            <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                            <?php echo $isBookmarked ? 'Đã lưu' : 'Lưu truyện'; ?>
                        </button>
                        
                        <button class="btn btn-outline" onclick="shareStory()">
                            <i class="fas fa-share-alt"></i> Chia sẻ
                        </button>
                    </div>
                </div>
                
                <div class="story-header-right">
                    <img src="<?php echo $truyen['hinh_anh'] ?: 'assets/images/placeholder-story.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($truyen['tieu_de']); ?>"
                         class="story-header-image">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Audio Player -->
    <?php if($truyen['co_audio'] && $truyen['file_audio']): ?>
    <section class="audio-player-section" id="audioPlayerSection" style="display: none;">
        <div class="container">
            <div class="audio-player-card">
                <div class="audio-player-info">
                    <div class="audio-player-icon">
                        <i class="fas fa-headphones"></i>
                    </div>
                    <div>
                        <h4>Đang phát audio</h4>
                        <p><?php echo htmlspecialchars($truyen['tieu_de']); ?></p>
                    </div>
                </div>
                
                <audio id="audioPlayer" controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($truyen['file_audio']); ?>" type="audio/mpeg">
                    Trình duyệt của bạn không hỗ trợ audio.
                </audio>
                
                <button class="btn-close-audio" onclick="closeAudio()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Summary -->
    <?php if($truyen['tom_tat']): ?>
    <section class="story-summary">
        <div class="container">
            <div class="summary-card">
                <h3><i class="fas fa-align-left"></i> Tóm tắt nội dung</h3>
                <p><?php echo nl2br(htmlspecialchars($truyen['tom_tat'])); ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Main Content -->
    <section class="story-content-section" id="storyContent">
        <div class="container">
            <div class="story-content-wrapper">
                <div class="story-content">
                    <?php echo $truyen['noi_dung']; ?>
                </div>
                
                <!-- Tags -->
                <?php if($truyen['tags']): 
                    $tags = explode(',', $truyen['tags']);
                ?>
                <div class="story-tags">
                    <i class="fas fa-tags"></i>
                    <?php foreach($tags as $tag): ?>
                    <a href="truyen-dan-gian.php?search=<?php echo urlencode(trim($tag)); ?>" class="tag">
                        <?php echo htmlspecialchars(trim($tag)); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Share -->
                <div class="story-share-section">
                    <h4>Chia sẻ truyện này:</h4>
                    <div class="share-buttons">
                        <button class="share-btn facebook" onclick="shareToFacebook()">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                        <button class="share-btn twitter" onclick="shareToTwitter()">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button class="share-btn telegram" onclick="shareToTelegram()">
                            <i class="fab fa-telegram-plane"></i> Telegram
                        </button>
                        <button class="share-btn copy" onclick="copyLink()">
                            <i class="fas fa-link"></i> Copy link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related Stories -->
    <?php if($related && count($related) > 0): ?>
    <section class="related-stories">
        <div class="container">
            <h2 class="section-title"><i class="fas fa-book"></i> Truyện liên quan</h2>
            <div class="related-stories-grid">
                <?php foreach($related as $item): ?>
                <article class="story-card-mini">
                    <a href="truyen-chi-tiet.php?id=<?php echo $item['id']; ?>" class="story-card-mini-image">
                        <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder-story.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['tieu_de']); ?>"
                             loading="lazy">
                        <div class="story-card-badge">
                            <?php echo htmlspecialchars($item['the_loai'] ?: 'Truyện'); ?>
                        </div>
                    </a>
                    <div class="story-card-mini-body">
                        <h4>
                            <a href="truyen-chi-tiet.php?id=<?php echo $item['id']; ?>">
                                <?php echo htmlspecialchars($item['tieu_de']); ?>
                            </a>
                        </h4>
                        <?php if($item['tac_gia']): ?>
                        <p class="mini-author"><?php echo htmlspecialchars($item['tac_gia']); ?></p>
                        <?php endif; ?>
                        <div class="mini-meta">
                            <span><i class="fas fa-eye"></i> <?php echo number_format($item['luot_xem']); ?></span>
                            <?php if($item['co_audio']): ?>
                            <span><i class="fas fa-headphones"></i></span>
                            <?php endif; ?>
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
.story-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 40px 0 60px;
}

.story-header-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 48px;
    margin-top: 32px;
}

.story-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}

.story-header-title {
    font-size: 42px;
    line-height: 1.2;
    margin-bottom: 16px;
}

.story-author-large {
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 24px;
}

.story-header-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-bottom: 32px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
}

.story-actions-header {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.story-actions-header .btn {
    border: 2px solid white;
}

.story-actions-header .btn-outline {
    background: transparent;
    color: white;
}

.story-actions-header .btn-outline:hover {
    background: white;
    color: var(--primary);
}

.story-actions-header .btn-outline.active {
    background: white;
    color: var(--primary);
}

.story-header-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
}

.audio-player-section {
    padding: 24px 0;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.audio-player-card {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 20px 24px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.audio-player-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.audio-player-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border-radius: 12px;
    font-size: 20px;
}

.audio-player-card h4 {
    font-size: 16px;
    margin-bottom: 4px;
}

.audio-player-card p {
    font-size: 14px;
    color: var(--gray-600);
}

#audioPlayer {
    flex: 1;
    height: 40px;
}

.btn-close-audio {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    border: none;
    border-radius: 8px;
    color: var(--gray-700);
    cursor: pointer;
    transition: var(--transition-base);
}

.btn-close-audio:hover {
    background: var(--danger);
    color: white;
}

.story-summary {
    padding: 40px 0;
    background: var(--gray-50);
}

.summary-card {
    padding: 32px;
    background: white;
    border-left: 4px solid var(--primary);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.summary-card h3 {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 20px;
    margin-bottom: 16px;
    color: var(--primary);
}

.summary-card p {
    font-size: 16px;
    line-height: 1.8;
    color: var(--gray-700);
}

.story-content-section {
    padding: 60px 0;
}

.story-content-wrapper {
    max-width: 800px;
    margin: 0 auto;
}

.story-content {
    font-size: 18px;
    line-height: 1.9;
    color: var(--gray-800);
    margin-bottom: 48px;
}

.story-content p {
    margin-bottom: 20px;
}

.story-content h2 {
    font-size: 28px;
    margin: 40px 0 20px;
    color: var(--gray-900);
}

.story-content h3 {
    font-size: 22px;
    margin: 32px 0 16px;
    color: var(--gray-900);
}

.story-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    padding: 24px 0;
    border-top: 1px solid var(--gray-200);
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: 32px;
}

.story-tags i {
    color: var(--primary);
}

.tag {
    padding: 8px 16px;
    background: var(--gray-100);
    color: var(--gray-700);
    border-radius: 20px;
    font-size: 14px;
    text-decoration: none;
    transition: var(--transition-base);
}

.tag:hover {
    background: var(--primary);
    color: white;
}

.story-share-section {
    padding: 32px;
    background: var(--gray-50);
    border-radius: 12px;
    text-align: center;
}

.story-share-section h4 {
    font-size: 18px;
    margin-bottom: 20px;
}

.share-buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
}

.share-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: var(--transition-base);
}

.share-btn.facebook {
    background: #1877f2;
}

.share-btn.twitter {
    background: #1da1f2;
}

.share-btn.telegram {
    background: #0088cc;
}

.share-btn.copy {
    background: var(--gray-700);
}

.share-btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.related-stories {
    padding: 60px 0;
    background: var(--gray-50);
}

.related-stories-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-top: 32px;
}

.story-card-mini {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.story-card-mini:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.story-card-mini-image {
    position: relative;
    display: block;
    height: 180px;
    overflow: hidden;
}

.story-card-mini-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-card-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 12px;
    background: rgba(99, 102, 241, 0.95);
    color: white;
    font-size: 11px;
    font-weight: 600;
    border-radius: 12px;
}

.story-card-mini-body {
    padding: 16px;
}

.story-card-mini-body h4 {
    font-size: 15px;
    margin-bottom: 8px;
    line-height: 1.4;
}

.story-card-mini-body h4 a {
    color: var(--gray-900);
}

.mini-author {
    font-size: 13px;
    color: var(--gray-600);
    margin-bottom: 8px;
}

.mini-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--gray-500);
}

@media (max-width: 768px) {
    .story-header-content {
        grid-template-columns: 1fr;
    }
    
    .story-header-image {
        height: 300px;
    }
    
    .story-header-title {
        font-size: 28px;
    }
    
    .related-stories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function scrollToStory() {
    document.getElementById('storyContent').scrollIntoView({ behavior: 'smooth' });
}

// Audio Player
<?php if($truyen['co_audio']): ?>
const audioSection = document.getElementById('audioPlayerSection');
const audioPlayer = document.getElementById('audioPlayer');
const audioToggleBtn = document.getElementById('audioToggleBtn');

audioToggleBtn.addEventListener('click', function() {
    audioSection.style.display = 'block';
    audioPlayer.play();
    this.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng';
});

audioPlayer.addEventListener('play', function() {
    audioToggleBtn.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng';
});

audioPlayer.addEventListener('pause', function() {
    audioToggleBtn.innerHTML = '<i class="fas fa-play"></i> Nghe audio';
});

function closeAudio() {
    audioPlayer.pause();
    audioSection.style.display = 'none';
}
<?php endif; ?>

// Bookmark
function toggleBookmark(storyId) {
    <?php if(!isset($_SESSION['user_id'])): ?>
    alert('Vui lòng đăng nhập để lưu truyện');
    window.location.href = 'login.php';
    <?php else: ?>
    const btn = document.getElementById('bookmarkBtn');
    const icon = btn.querySelector('i');
    
    // TODO: Ajax call to save/remove bookmark
    
    if(icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.classList.add('active');
        btn.innerHTML = '<i class="fas fa-bookmark"></i> Đã lưu';
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.classList.remove('active');
        btn.innerHTML = '<i class="far fa-bookmark"></i> Lưu truyện';
    }
    <?php endif; ?>
}

// Share functions
const storyUrl = window.location.href;
const storyTitle = <?php echo json_encode($truyen['tieu_de']); ?>;

function shareToFacebook() {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(storyUrl)}`, '_blank', 'width=600,height=400');
}

function shareToTwitter() {
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(storyUrl)}&text=${encodeURIComponent(storyTitle)}`, '_blank', 'width=600,height=400');
}

function shareToTelegram() {
    window.open(`https://t.me/share/url?url=${encodeURIComponent(storyUrl)}&text=${encodeURIComponent(storyTitle)}`, '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(storyUrl).then(() => {
        alert('Đã copy link!');
    });
}

function shareStory() {
    if (navigator.share) {
        navigator.share({
            title: storyTitle,
            url: storyUrl
        });
    } else {
        copyLink();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
