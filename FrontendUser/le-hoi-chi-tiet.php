<?php
/**
 * Chi tiết lễ hội - Unified Design
 */
require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/le-hoi.php');

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM le_hoi WHERE ma_le_hoi = ? AND trang_thai = 'hien_thi'");
$stmt->execute([$id]);
$festival = $stmt->fetch();

if (!$festival) redirect(BASE_URL . '/le-hoi.php', 'Lễ hội không tồn tại.', 'warning');

// Cập nhật lượt xem
try {
    $pdo->prepare("UPDATE le_hoi SET luot_xem = luot_xem + 1 WHERE ma_le_hoi = ?")->execute([$id]);
} catch (Exception $e) {
    error_log("Error updating view count: " . $e->getMessage());
}

$pageTitle = $festival['ten_le_hoi'];
$isUpcoming = strtotime($festival['ngay_bat_dau']) >= strtotime('today');

// Get related festivals
$relatedStmt = $pdo->prepare("SELECT * FROM le_hoi WHERE ma_le_hoi != ? AND trang_thai = 'hien_thi' ORDER BY RAND() LIMIT 4");
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
            WHERE bl.loai_noi_dung = 'le_hoi' AND bl.ma_noi_dung = ? AND bl.trang_thai = 'hien_thi' AND bl.ma_binh_luan_cha IS NULL
            ORDER BY bl.ngay_tao DESC
        ");
        $commentsStmt->execute([$id]);
        $comments = $commentsStmt->fetchAll();
        
        $totalComments = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE loai_noi_dung = 'le_hoi' AND ma_noi_dung = ? AND trang_thai = 'hien_thi'");
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

<main class="detail-page">
    <!-- Hero Section -->
    <?php 
    $imagePath = $festival['anh_dai_dien'];
    $hasImage = !empty($imagePath);
    if ($hasImage) {
        if (strpos($imagePath, 'uploads/') === 0) {
            $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
        } else {
            $imageUrl = BASE_URL . '/uploads/lehoi/' . $imagePath;
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
                <i class="fas fa-calendar-alt"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Below Image -->
        <div class="detail-hero-info">
            <div class="container">
                <nav class="detail-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/le-hoi.php">Lễ hội</a>
                    <span>/</span>
                    <span><?= sanitize($festival['ten_le_hoi']) ?></span>
                </nav>
                
                <h1 class="detail-title">
                    <?= sanitize($festival['ten_le_hoi']) ?>
                </h1>
                
                <?php if (!empty($festival['dia_diem'])): ?>
                <p class="detail-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= sanitize($festival['dia_diem']) ?>
                </p>
                <?php endif; ?>
                
                <div class="detail-meta">
                    <span class="detail-badge <?= $isUpcoming ? 'upcoming' : 'past' ?>">
                        <i class="fas fa-<?= $isUpcoming ? 'calendar-plus' : 'calendar-check' ?>"></i>
                        <?= $isUpcoming ? 'Sắp diễn ra' : 'Đã qua' ?>
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-calendar-alt"></i>
                        <?= formatDate($festival['ngay_bat_dau'], 'd/m/Y') ?>
                        <?php if (!empty($festival['ngay_ket_thuc']) && $festival['ngay_ket_thuc'] !== $festival['ngay_bat_dau']): ?>
                        - <?= formatDate($festival['ngay_ket_thuc'], 'd/m/Y') ?>
                        <?php endif; ?>
                    </span>
                    <?php if (!empty($festival['tinh_thanh'])): ?>
                    <span class="detail-badge">
                        <i class="fas fa-city"></i>
                        <?= sanitize($festival['tinh_thanh']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="detail-main-content">
        <div class="container">
            <!-- Description -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Giới thiệu</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?= !empty($festival['mo_ta']) ? $festival['mo_ta'] : '<p style="color: #94a3b8;">Chưa có thông tin chi tiết.</p>' ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($festival['y_nghia'])): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Ý nghĩa</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?= nl2br(sanitize($festival['y_nghia'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($festival['nguon_goc'])): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-history"></i>
                    <h3>Nguồn gốc</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?= nl2br(sanitize($festival['nguon_goc'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($festival['video_url'])): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-video"></i>
                    <h3>Video</h3>
                </div>
                <div class="content-card-body">
                    <div class="video-wrapper">
                        <div class="video-container">
                            <iframe src="<?= $festival['video_url'] ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quiz Section -->
            <?php 
            $quiz_type = 'le_hoi';
            $content_id = $id;
            include __DIR__ . '/includes/quiz-section.php';
            ?>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <button onclick="saveArticle(<?= $id ?>)" id="saveBtn" class="action-btn primary">
                    <i class="far fa-bookmark" id="saveIcon"></i>
                    <span id="saveText">Lưu lễ hội</span>
                </button>
                <button onclick="shareArticle()" class="action-btn outline">
                    <i class="fas fa-share-alt"></i>
                    Chia sẻ
                </button>
                <a href="<?= BASE_URL ?>/le-hoi.php" class="action-btn outline">
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
                                <input type="hidden" name="loai_noi_dung" value="le_hoi">
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
            
            <!-- Related Festivals -->
            <?php if (!empty($related)): ?>
            <div class="related-section">
                <div class="related-header">
                    <i class="fas fa-th-large"></i>
                    <h4>Lễ hội khác</h4>
                </div>
                <div class="related-grid">
                    <?php foreach ($related as $item): 
                        $relImg = $item['anh_dai_dien'];
                        if (strpos($relImg, 'uploads/') === 0) {
                            $relImgUrl = '/DoAn_ChuyenNganh/' . $relImg;
                        } else {
                            $relImgUrl = BASE_URL . '/uploads/lehoi/' . $relImg;
                        }
                    ?>
                    <a href="<?= BASE_URL ?>/le-hoi-chi-tiet.php?id=<?= $item['ma_le_hoi'] ?>" class="related-card">
                        <div class="related-card-image">
                            <img src="<?= $relImgUrl ?>" alt="<?= sanitize($item['ten_le_hoi']) ?>">
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?= sanitize($item['ten_le_hoi']) ?></div>
                            <div class="related-card-meta">
                                <i class="fas fa-calendar-alt"></i>
                                <?= formatDate($item['ngay_bat_dau'], 'd/m/Y') ?>
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
