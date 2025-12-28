<?php
/**
 * Chi tiết văn hóa Khmer - Unified Design
 */
require_once __DIR__ . '/includes/header.php';

/**
 * Format plain text content to HTML
 */
function formatArticleContent($content) {
    if ($content !== strip_tags($content)) {
        return $content;
    }
    
    $lines = preg_split('/\r\n|\r|\n/', $content);
    $html = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        
        if (preg_match('/^(\d+)\.\s+(.+)$/', $line, $m)) {
            $html .= '<h2 class="content-heading">' . htmlspecialchars($m[1] . '. ' . $m[2]) . '</h2>';
        } elseif (preg_match('/^•\s*(.+)$/', $line, $m)) {
            $html .= '<div class="content-bullet"><span class="bullet">•</span><span class="bullet-text">' . htmlspecialchars($m[1]) . '</span></div>';
        } else {
            $html .= '<p class="content-para">' . htmlspecialchars($line) . '</p>';
        }
    }
    
    return $html;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/van-hoa.php');

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM van_hoa WHERE ma_van_hoa = ? AND trang_thai = 'xuat_ban'");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) redirect(BASE_URL . '/van-hoa.php', 'Bài viết không tồn tại.', 'warning');

$pageTitle = $article['tieu_de'];

// Update view count
$pdo->prepare("UPDATE van_hoa SET luot_xem = luot_xem + 1 WHERE ma_van_hoa = ?")->execute([$id]);

// Get related articles - Không dùng cột danh_muc vì có thể không tồn tại
$relatedStmt = $pdo->prepare("SELECT * FROM van_hoa WHERE ma_van_hoa != ? AND trang_thai = 'xuat_ban' ORDER BY ngay_tao DESC LIMIT 4");
$relatedStmt->execute([$id]);
$related = $relatedStmt->fetchAll();

// Check if bookmarked
$isBookmarked = false;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $bookmarkStmt = $pdo->prepare("SELECT ma_yeu_thich FROM yeu_thich WHERE ma_nguoi_dung = ? AND ma_doi_tuong = ? AND loai_doi_tuong = 'van_hoa'");
    $bookmarkStmt->execute([$currentUser['ma_nguoi_dung'], $id]);
    $isBookmarked = $bookmarkStmt->fetch() !== false;
}

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
            WHERE bl.loai_noi_dung = 'van_hoa' AND bl.ma_noi_dung = ? AND bl.trang_thai = 'hien_thi' AND bl.ma_binh_luan_cha IS NULL
            ORDER BY bl.ngay_tao DESC
        ");
        $commentsStmt->execute([$id]);
        $comments = $commentsStmt->fetchAll();
        
        // Get user likes if logged in
        $userLikes = [];
        if (isLoggedIn()) {
            $likesStmt = $pdo->prepare("SELECT ma_binh_luan FROM luot_thich_binh_luan WHERE ma_nguoi_dung = ?");
            $likesStmt->execute([$currentUser['ma_nguoi_dung']]);
            $userLikes = $likesStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $totalComments = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE loai_noi_dung = 'van_hoa' AND ma_noi_dung = ? AND trang_thai = 'hien_thi'");
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
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">

<main class="detail-page">
    <!-- Hero Section -->
    <?php 
    $imagePath = $article['hinh_anh_chinh'] ?? '';
    $hasImage = !empty($imagePath);
    if ($hasImage) {
        if (strpos($imagePath, 'http') === 0) {
            $imageUrl = $imagePath;
        } elseif (strpos($imagePath, 'uploads/') === 0) {
            $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
        } else {
            $imageUrl = UPLOAD_PATH . 'vanhoa/' . $imagePath;
        }
    }
    ?>
    <section class="detail-hero">
        <!-- Image Container -->
        <div class="detail-hero-image-wrapper">
            <?php if ($hasImage): ?>
            <div class="detail-hero-image" style="background-image: url('<?= $imageUrl ?>');"></div>
            <?php else: ?>
            <div class="detail-hero-image detail-hero-placeholder">
                <i class="fas fa-book-open"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Below Image -->
        <div class="detail-hero-info">
            <div class="container">
                <nav class="detail-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/van-hoa.php"><?= __('nav_culture') ?? 'Văn hóa' ?></a>
                    <span>/</span>
                    <span><?= htmlspecialchars($article['tieu_de']) ?></span>
                </nav>
                
                <h1 class="detail-title">
                    <?= htmlspecialchars($article['tieu_de']) ?>
                    <?php if (!empty($article['tieu_de_khmer'])): ?>
                    <span class="detail-title-khmer"><?= $article['tieu_de_khmer'] ?></span>
                    <?php endif; ?>
                </h1>
                
                <div class="detail-meta">
                    <?php if (!empty($article['tac_gia'])): ?>
                    <span class="detail-badge">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($article['tac_gia']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="detail-badge">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('d/m/Y', strtotime($article['ngay_tao'])) ?>
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-eye"></i>
                        <?= number_format($article['luot_xem']) ?> lượt xem
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="detail-main-content">
        <div class="container">
            <!-- Summary -->
            <?php if (!empty($article['tom_tat'])): ?>
            <div class="content-card">
                <div class="content-card-body">
                    <div class="article-intro">
                        <?= nl2br(htmlspecialchars($article['tom_tat'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-book-open"></i>
                    <h3>Nội dung chi tiết</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?php if (!empty($article['noi_dung'])): ?>
                            <?php echo formatArticleContent($article['noi_dung']); ?>
                        <?php else: ?>
                            <p style="color: #94a3b8; text-align: center;">Chưa có nội dung chi tiết.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Source Reference -->
            <?php if (!empty($article['nguon_tham_khao'])): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-link"></i>
                    <h3>Nguồn tham khảo</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?= nl2br(htmlspecialchars($article['nguon_tham_khao'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quiz Section -->
            <?php 
            $quiz_type = 'van_hoa';
            $content_id = $id;
            include __DIR__ . '/includes/quiz-section.php';
            ?>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <button onclick="saveArticle(<?= $id ?>)" id="saveBtn" class="action-btn primary <?= $isBookmarked ? 'saved' : '' ?>">
                    <i class="<?= $isBookmarked ? 'fas' : 'far' ?> fa-bookmark" id="saveIcon"></i>
                    <span id="saveText"><?= $isBookmarked ? 'Đã lưu' : 'Lưu bài viết' ?></span>
                </button>
                <button onclick="shareArticle()" class="action-btn outline">
                    <i class="fas fa-share-alt"></i>
                    Chia sẻ
                </button>
                <a href="<?= BASE_URL ?>/van-hoa.php" class="action-btn outline">
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
                                <input type="hidden" name="loai_noi_dung" value="van_hoa">
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
                        <?php foreach ($comments as $comment): 
                            $isOwner = isLoggedIn() && $currentUser['ma_nguoi_dung'] == $comment['ma_nguoi_dung'];
                            $hasLiked = isLoggedIn() && in_array($comment['ma_binh_luan'], $userLikes);
                        ?>
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
                                    <?php if ($isOwner): ?>
                                    <div class="comment-owner-actions">
                                        <button class="comment-action-btn-icon" onclick="editComment(<?= $comment['ma_binh_luan'] ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="comment-action-btn-icon" onclick="deleteComment(<?= $comment['ma_binh_luan'] ?>)" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-text" id="comment-text-<?= $comment['ma_binh_luan'] ?>"><?= nl2br(htmlspecialchars($comment['noi_dung'])) ?></div>
                                <div class="comment-edit-form" id="edit-form-<?= $comment['ma_binh_luan'] ?>" style="display: none;">
                                    <textarea class="comment-edit-textarea"><?= htmlspecialchars($comment['noi_dung']) ?></textarea>
                                    <div class="comment-edit-actions">
                                        <button class="btn-cancel" onclick="cancelEdit(<?= $comment['ma_binh_luan'] ?>)">Hủy</button>
                                        <button class="btn-save" onclick="saveEdit(<?= $comment['ma_binh_luan'] ?>)">Lưu</button>
                                    </div>
                                </div>
                                <div class="comment-actions">
                                    <button class="comment-action-btn like-btn <?= $hasLiked ? 'liked' : '' ?>" onclick="likeComment(<?= $comment['ma_binh_luan'] ?>)">
                                        <i class="<?= $hasLiked ? 'fas' : 'far' ?> fa-heart"></i> <span><?= $comment['so_like'] ?? 0 ?></span>
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
                                    <?php foreach ($replies as $reply): 
                                        $isReplyOwner = isLoggedIn() && $currentUser['ma_nguoi_dung'] == $reply['ma_nguoi_dung'];
                                    ?>
                                    <div class="comment-item reply" data-id="<?= $reply['ma_binh_luan'] ?>">
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
                                                <?php if ($isReplyOwner): ?>
                                                <div class="comment-owner-actions">
                                                    <button class="comment-action-btn-icon" onclick="editComment(<?= $reply['ma_binh_luan'] ?>)" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="comment-action-btn-icon" onclick="deleteComment(<?= $reply['ma_binh_luan'] ?>)" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-text" id="comment-text-<?= $reply['ma_binh_luan'] ?>"><?= nl2br(htmlspecialchars($reply['noi_dung'])) ?></div>
                                            <div class="comment-edit-form" id="edit-form-<?= $reply['ma_binh_luan'] ?>" style="display: none;">
                                                <textarea class="comment-edit-textarea"><?= htmlspecialchars($reply['noi_dung']) ?></textarea>
                                                <div class="comment-edit-actions">
                                                    <button class="btn-cancel" onclick="cancelEdit(<?= $reply['ma_binh_luan'] ?>)">Hủy</button>
                                                    <button class="btn-save" onclick="saveEdit(<?= $reply['ma_binh_luan'] ?>)">Lưu</button>
                                                </div>
                                            </div>
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
            
            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
            <div class="related-section">
                <div class="related-header">
                    <i class="fas fa-th-large"></i>
                    <h4>Bài viết liên quan</h4>
                </div>
                <div class="related-grid">
                    <?php foreach ($related as $item): 
                        $relImg = $item['hinh_anh_chinh'] ?? '';
                        if ($relImg) {
                            if (strpos($relImg, 'uploads/') === 0) {
                                $relImgUrl = '/DoAn_ChuyenNganh/' . $relImg;
                            } else {
                                $relImgUrl = UPLOAD_PATH . 'vanhoa/' . $relImg;
                            }
                        } else {
                            $relImgUrl = '';
                        }
                    ?>
                    <a href="<?= BASE_URL ?>/van-hoa-chi-tiet.php?id=<?= $item['ma_van_hoa'] ?>" class="related-card">
                        <div class="related-card-image">
                            <?php if ($relImgUrl): ?>
                            <img src="<?= $relImgUrl ?>" alt="<?= htmlspecialchars($item['tieu_de']) ?>">
                            <?php else: ?>
                            <i class="fas fa-book-open"></i>
                            <?php endif; ?>
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?= htmlspecialchars($item['tieu_de']) ?></div>
                            <div class="related-card-meta">
                                <i class="fas fa-eye"></i>
                                <?= number_format($item['luot_xem']) ?>
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
