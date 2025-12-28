<?php
/**
 * Chi tiết truyện dân gian - Unified Design
 */
// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    // Debug: Show error instead of redirect
    die("Error: No ID provided. Please access this page with ?id=X where X is a valid story ID.");
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM truyen_dan_gian WHERE ma_truyen = ?");
    $stmt->execute([$id]);
    $story = $stmt->fetch();

    if (!$story) {
        // Debug: Show what we got
        die("Error: Story with ID $id not found in database. Available stories: " . 
            $pdo->query("SELECT GROUP_CONCAT(ma_truyen) FROM truyen_dan_gian")->fetchColumn());
    }
    
    // Check if story is hidden
    if ($story['trang_thai'] !== 'hien_thi') {
        die("Error: Story exists but status is '{$story['trang_thai']}', not 'hien_thi'");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$pageTitle = $story['tieu_de'];

// Update view count
try {
    $pdo->prepare("UPDATE truyen_dan_gian SET luot_xem = luot_xem + 1 WHERE ma_truyen = ?")->execute([$id]);
} catch (Exception $e) {
    error_log("Error updating view count: " . $e->getMessage());
}

// Add points for reading
if (isLoggedIn()) {
    try {
        if (function_exists('addUserPoints')) {
            addUserPoints($_SESSION['user_id'], 5, 'Đọc truyện: ' . $story['tieu_de']);
        }
    } catch (Exception $e) {
        error_log("Error adding user points: " . $e->getMessage());
    }
}

// Get related stories
$relatedStmt = $pdo->prepare("SELECT * FROM truyen_dan_gian WHERE ma_truyen != ? AND trang_thai = 'hien_thi' ORDER BY RAND() LIMIT 4");
$relatedStmt->execute([$id]);
$related = $relatedStmt->fetchAll();

// Comments setup
$tableExists = false;
$comments = [];
$commentCount = 0;

try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'binh_luan'")->rowCount() > 0;
    if ($checkTable) {
        $columns = $pdo->query("SHOW COLUMNS FROM binh_luan LIKE 'loai_noi_dung'")->rowCount();
        $tableExists = $columns > 0;
    }
    
    if ($tableExists) {
        $commentsStmt = $pdo->prepare("
            SELECT bl.*, nd.ho_ten, nd.anh_dai_dien 
            FROM binh_luan bl 
            JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung 
            WHERE bl.loai_noi_dung = 'truyen' AND bl.ma_noi_dung = ? AND bl.trang_thai = 'hien_thi' AND bl.ma_binh_luan_cha IS NULL
            ORDER BY bl.ngay_tao DESC
        ");
        $commentsStmt->execute([$id]);
        $comments = $commentsStmt->fetchAll();
        
        $totalComments = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE loai_noi_dung = 'truyen' AND ma_noi_dung = ? AND trang_thai = 'hien_thi'");
        $totalComments->execute([$id]);
        $commentCount = $totalComments->fetchColumn();
    }
} catch (Exception $e) {
    $tableExists = false;
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- Unified Detail Page Styles -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/detail-page-unified.css">

<style>
/* Audio Player */
.audio-player {
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border-radius: 16px;
    margin-bottom: 1.5rem;
}

.audio-player-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.audio-player-header i {
    color: #667eea;
    font-size: 1.25rem;
}

.audio-player-header span {
    font-weight: 600;
    color: #1e293b;
}

.audio-player audio {
    width: 100%;
    border-radius: 8px;
}
</style>

<main class="detail-page">
    <!-- Hero Section -->
    <?php 
    $imagePath = $story['anh_dai_dien'];
    if ($imagePath) {
        if (strpos($imagePath, 'uploads/') === 0) {
            $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
        } else {
            $imageUrl = UPLOAD_PATH . 'truyendangian/' . $imagePath;
        }
    } else {
        $imageUrl = '';
    }
    ?>
    <section class="detail-hero">
        <!-- Image Container -->
        <div class="detail-hero-image-wrapper">
            <?php if ($imageUrl): ?>
            <div class="detail-hero-image" style="background-image: url('<?= $imageUrl ?>');"></div>
            <?php else: ?>
            <div class="detail-hero-image detail-hero-placeholder">
                <i class="fas fa-book"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Below Image -->
        <div class="detail-hero-info">
            <div class="container">
                <nav class="detail-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/truyen-dan-gian.php"><?= __('nav_stories') ?></a>
                    <span>/</span>
                    <span><?= sanitize($story['tieu_de']) ?></span>
                </nav>
                
                <h1 class="detail-title"><?= sanitize($story['tieu_de']) ?></h1>
                
                <div class="detail-meta">
                    <?php if (!empty($story['the_loai'])): ?>
                    <span class="detail-badge">
                        <i class="fas fa-tag"></i>
                        <?= $story['the_loai'] ?>
                    </span>
                    <?php endif; ?>
                    <span class="detail-badge">
                        <i class="fas fa-clock"></i>
                        <?= $story['thoi_luong_doc'] ?? 5 ?> phút đọc
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-eye"></i>
                        <?= formatNumber($story['luot_xem']) ?> lượt xem
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-calendar"></i>
                        <?= formatDate($story['ngay_tao']) ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="detail-main-content">
        <div class="container">
            <!-- Story Content -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-book-open"></i>
                    <h3>Nội dung truyện</h3>
                </div>
                <div class="content-card-body">
                    <?php if (!empty($story['file_audio'])): ?>
                    <div class="audio-player">
                        <div class="audio-player-header">
                            <i class="fas fa-headphones"></i>
                            <span>Nghe truyện</span>
                        </div>
                        <audio controls>
                            <source src="<?= UPLOAD_PATH ?>audio/<?= $story['file_audio'] ?>" type="audio/mpeg">
                        </audio>
                    </div>
                    <?php endif; ?>
                    
                    <div id="storyContent" class="article-content">
                        <?= $story['noi_dung'] ?>
                    </div>
                </div>
            </div>
            
            <!-- Quiz Section -->
            <?php 
            $quiz_type = 'truyen';
            $content_id = $id;
            include __DIR__ . '/includes/quiz-section.php';
            ?>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <button onclick="toggleBookmark(<?= $id ?>)" class="action-btn primary">
                    <i class="far fa-bookmark"></i>
                    Lưu truyện
                </button>
                <button onclick="shareStory()" class="action-btn outline">
                    <i class="fas fa-share-alt"></i>
                    Chia sẻ
                </button>
                <a href="<?= BASE_URL ?>/truyen-dan-gian.php" class="action-btn outline">
                    <i class="fas fa-list"></i>
                    Xem tất cả
                </a>
            </div>
            
            <!-- Comments Section -->
            <div class="content-card" id="comments-section">
                <div class="content-card-header">
                    <i class="fas fa-comments"></i>
                    <h3>Bình luận (<?= $commentCount ?>)</h3>
                </div>
                <div class="content-card-body">
                    <?php if ($tableExists): ?>
                        <?php if (isLoggedIn()): ?>
                        <div class="comment-form-wrapper">
                            <div class="comment-form-avatar">
                                <?php if (!empty($currentUser['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $currentUser['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($currentUser['ho_ten'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <form class="comment-form" id="commentForm">
                                <input type="hidden" name="loai_noi_dung" value="truyen">
                                <input type="hidden" name="ma_noi_dung" value="<?= $id ?>">
                                <textarea name="noi_dung" id="commentContent" placeholder="Viết bình luận của bạn..." required></textarea>
                                <button type="submit" class="comment-submit-btn">
                                    <i class="fas fa-paper-plane"></i> Gửi bình luận
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="comment-login-prompt">
                            <i class="fas fa-user-circle"></i>
                            <p>Vui lòng <a href="<?= BASE_URL ?>/login.php">đăng nhập</a> để bình luận</p>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="comments-list" id="commentsList">
                        <?php if (!$tableExists): ?>
                        <div class="no-comments">
                            <i class="fas fa-database"></i>
                            <p>Hệ thống bình luận đang được cập nhật.</p>
                        </div>
                        <?php elseif (empty($comments)): ?>
                        <div class="no-comments">
                            <i class="far fa-comment-dots"></i>
                            <p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-id="<?= $comment['ma_binh_luan'] ?>">
                            <div class="comment-avatar">
                                <?php if (!empty($comment['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $comment['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($comment['ho_ten'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($comment['ho_ten']) ?></span>
                                    <span class="comment-time"><?= timeAgo($comment['ngay_tao']) ?></span>
                                </div>
                                <div class="comment-text"><?= nl2br(htmlspecialchars($comment['noi_dung'])) ?></div>
                                <div class="comment-actions">
                                    <button class="comment-action-btn like-btn" onclick="likeComment(<?= $comment['ma_binh_luan'] ?>)">
                                        <i class="far fa-heart"></i> <span><?= $comment['so_like'] ?></span>
                                    </button>
                                    <?php if (isLoggedIn()): ?>
                                    <button class="comment-action-btn reply-btn" onclick="showReplyForm(<?= $comment['ma_binh_luan'] ?>)">
                                        <i class="fas fa-reply"></i> Trả lời
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="reply-form-wrapper" id="replyForm-<?= $comment['ma_binh_luan'] ?>" style="display: none;">
                                    <form class="reply-form" onsubmit="submitReply(event, <?= $comment['ma_binh_luan'] ?>)">
                                        <textarea placeholder="Viết trả lời..." required></textarea>
                                        <div class="reply-form-actions">
                                            <button type="button" onclick="hideReplyForm(<?= $comment['ma_binh_luan'] ?>)">Hủy</button>
                                            <button type="submit">Gửi</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <?php
                                $replies = [];
                                if ($tableExists) {
                                    $repliesStmt = $pdo->prepare("SELECT bl.*, nd.ho_ten, nd.anh_dai_dien FROM binh_luan bl JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung WHERE bl.ma_binh_luan_cha = ? AND bl.trang_thai = 'hien_thi' ORDER BY bl.ngay_tao ASC");
                                    $repliesStmt->execute([$comment['ma_binh_luan']]);
                                    $replies = $repliesStmt->fetchAll();
                                }
                                ?>
                                <?php if (!empty($replies)): ?>
                                <div class="comment-replies">
                                    <?php foreach ($replies as $reply): ?>
                                    <div class="comment-item reply">
                                        <div class="comment-avatar small">
                                            <?php if (!empty($reply['anh_dai_dien'])): ?>
                                            <img src="<?= UPLOAD_PATH ?>avatar/<?= $reply['anh_dai_dien'] ?>" alt="">
                                            <?php else: ?>
                                            <?= strtoupper(mb_substr($reply['ho_ten'], 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-author"><?= htmlspecialchars($reply['ho_ten']) ?></span>
                                                <span class="comment-time"><?= timeAgo($reply['ngay_tao']) ?></span>
                                            </div>
                                            <div class="comment-text"><?= nl2br(htmlspecialchars($reply['noi_dung'])) ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Related Stories -->
            <?php if (!empty($related)): ?>
            <div class="related-section">
                <div class="related-header">
                    <i class="fas fa-book"></i>
                    <h4>Truyện khác</h4>
                </div>
                <div class="related-grid">
                    <?php foreach ($related as $item): 
                        $relImg = $item['anh_dai_dien'];
                        if ($relImg) {
                            if (strpos($relImg, 'uploads/') === 0) {
                                $relImgUrl = '/DoAn_ChuyenNganh/' . $relImg;
                            } else {
                                $relImgUrl = UPLOAD_PATH . 'truyendangian/' . $relImg;
                            }
                        } else {
                            $relImgUrl = '';
                        }
                    ?>
                    <a href="<?= BASE_URL ?>/truyen-chi-tiet.php?id=<?= $item['ma_truyen'] ?>" class="related-card">
                        <div class="related-card-image">
                            <?php if ($relImgUrl): ?>
                            <img src="<?= $relImgUrl ?>" alt="<?= sanitize($item['tieu_de']) ?>">
                            <?php else: ?>
                            <i class="fas fa-book"></i>
                            <?php endif; ?>
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?= sanitize($item['tieu_de']) ?></div>
                            <div class="related-card-meta">
                                <i class="fas fa-eye"></i>
                                <?= formatNumber($item['luot_xem']) ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Unified Detail Page Scripts -->
<script src="<?= BASE_URL ?>/assets/js/detail-page-unified.js"></script>

<script>
// Bookmark toggle
function toggleBookmark(id) {
    <?php if (!isLoggedIn()): ?>
    window.location.href = '<?= BASE_URL ?>/login.php';
    return;
    <?php endif; ?>
    
    fetch('<?= BASE_URL ?>/api/bookmark.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({type: 'truyen', id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        }
    });
}

// Share story
function shareStory() {
    if (navigator.share) {
        navigator.share({
            title: '<?= addslashes($story['tieu_de']) ?>',
            url: window.location.href
        });
    } else {
        const url = window.location.href;
        navigator.clipboard.writeText(url);
        alert('Đã sao chép link!');
    }
}
</script>
