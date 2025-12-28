<?php
/**
 * Chi tiết chùa Khmer - Unified Design
 */
require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/chua-khmer.php');

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM chua_khmer WHERE ma_chua = ? AND trang_thai = 'hoat_dong'");
$stmt->execute([$id]);
$temple = $stmt->fetch();

if (!$temple) redirect(BASE_URL . '/chua-khmer.php', 'Chùa không tồn tại.', 'warning');

$pageTitle = $temple['ten_chua'];

// Update view count
$pdo->prepare("UPDATE chua_khmer SET luot_xem = luot_xem + 1 WHERE ma_chua = ?")->execute([$id]);

// Get nearby temples
$nearbyStmt = $pdo->prepare("SELECT * FROM chua_khmer WHERE ma_chua != ? AND tinh_thanh = ? AND trang_thai = 'hoat_dong' LIMIT 4");
$nearbyStmt->execute([$id, $temple['tinh_thanh']]);
$nearby = $nearbyStmt->fetchAll();

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
            WHERE bl.loai_noi_dung = 'chua' AND bl.ma_noi_dung = ? AND bl.trang_thai = 'hien_thi' AND bl.ma_binh_luan_cha IS NULL
            ORDER BY bl.ngay_tao DESC
        ");
        $commentsStmt->execute([$id]);
        $comments = $commentsStmt->fetchAll();
        
        $totalComments = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE loai_noi_dung = 'chua' AND ma_noi_dung = ? AND trang_thai = 'hien_thi'");
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
    $imagePath = $temple['hinh_anh_chinh'];
    if (strpos($imagePath, 'uploads/') === 0) {
        $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
    } else {
        $imageUrl = UPLOAD_PATH . 'chua/' . $imagePath;
    }
    ?>
    <section class="detail-hero">
        <!-- Image Container -->
        <div class="detail-hero-image-wrapper">
            <div class="detail-hero-image" style="background-image: url('<?= $imageUrl ?>');"></div>
        </div>
        
        <!-- Content Below Image -->
        <div class="detail-hero-info">
            <div class="container">
                <nav class="detail-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/chua-khmer.php"><?= __('nav_temples') ?></a>
                    <span>/</span>
                    <span><?= sanitize($temple['ten_chua']) ?></span>
                </nav>
                
                <h1 class="detail-title">
                    <?= sanitize($temple['ten_chua']) ?>
                    <?php if (!empty($temple['ten_tieng_khmer'])): ?>
                    <span class="detail-title-khmer"><?= $temple['ten_tieng_khmer'] ?></span>
                    <?php endif; ?>
                </h1>
                
                <p class="detail-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= sanitize($temple['dia_chi']) ?>
                </p>
                
                <div class="detail-meta">
                    <?php if ($temple['nam_thanh_lap']): ?>
                    <span class="detail-badge">
                        <i class="fas fa-calendar-alt"></i>
                        Thành lập: <?= $temple['nam_thanh_lap'] ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($temple['tinh_thanh']): ?>
                    <span class="detail-badge">
                        <i class="fas fa-city"></i>
                        <?= sanitize($temple['tinh_thanh']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="detail-badge">
                        <i class="fas fa-eye"></i>
                        <?= formatNumber($temple['luot_xem']) ?> lượt xem
                    </span>
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
                        <?php if (!empty($temple['mo_ta_ngan'])): ?>
                            <?= $temple['mo_ta_ngan'] ?>
                        <?php else: ?>
                            <p style="color: #94a3b8; font-style: italic;">Chưa có thông tin giới thiệu.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- History -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-landmark"></i>
                    <h3>Lịch sử</h3>
                </div>
                <div class="content-card-body">
                    <div class="article-content">
                        <?php if (!empty($temple['lich_su'])): ?>
                            <?= $temple['lich_su'] ?>
                        <?php else: ?>
                            <p style="color: #94a3b8; font-style: italic;">Chưa có thông tin lịch sử.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Map -->
            <?php if ($temple['vi_do'] && $temple['kinh_do']): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>Vị trí</h3>
                </div>
                <div class="content-card-body">
                    <div class="map-wrapper">
                        <div id="detail-map" 
                             data-lat="<?= $temple['vi_do'] ?>" 
                             data-lng="<?= $temple['kinh_do'] ?>"
                             data-name="<?= sanitize($temple['ten_chua']) ?>">
                        </div>
                    </div>
                    <div class="map-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $temple['vi_do'] ?>,<?= $temple['kinh_do'] ?>" 
                           target="_blank" class="map-action-btn primary">
                            <i class="fas fa-directions"></i>
                            Chỉ đường
                        </a>
                        <a href="https://www.google.com/maps?q=<?= $temple['vi_do'] ?>,<?= $temple['kinh_do'] ?>" 
                           target="_blank" class="map-action-btn secondary">
                            <i class="fas fa-external-link-alt"></i>
                            Google Maps
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quiz Section -->
            <?php 
            $quiz_type = 'chua';
            $content_id = $id;
            include __DIR__ . '/includes/quiz-section.php';
            ?>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <button onclick="saveArticle(<?= $id ?>)" id="saveBtn" class="action-btn primary">
                    <i class="far fa-bookmark" id="saveIcon"></i>
                    <span id="saveText">Lưu chùa</span>
                </button>
                <button onclick="shareArticle()" class="action-btn outline">
                    <i class="fas fa-share-alt"></i>
                    Chia sẻ
                </button>
                <a href="<?= BASE_URL ?>/chua-khmer.php" class="action-btn outline">
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
                                <input type="hidden" name="loai_noi_dung" value="chua">
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
            
            <!-- Nearby Temples -->
            <?php if (!empty($nearby)): ?>
            <div class="related-section">
                <div class="related-header">
                    <i class="fas fa-gopuram"></i>
                    <h4>Chùa lân cận</h4>
                </div>
                <div class="related-grid">
                    <?php foreach ($nearby as $item): 
                        $relImg = $item['hinh_anh_chinh'];
                        if (strpos($relImg, 'uploads/') === 0) {
                            $relImgUrl = '/DoAn_ChuyenNganh/' . $relImg;
                        } else {
                            $relImgUrl = UPLOAD_PATH . 'chua/' . $relImg;
                        }
                    ?>
                    <a href="<?= BASE_URL ?>/chua-khmer-chi-tiet.php?id=<?= $item['ma_chua'] ?>" class="related-card">
                        <div class="related-card-image">
                            <img src="<?= $relImgUrl ?>" alt="<?= sanitize($item['ten_chua']) ?>">
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?= sanitize($item['ten_chua']) ?></div>
                            <div class="related-card-meta">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= sanitize($item['tinh_thanh']) ?>
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

<!-- Leaflet for Map -->
<?php if ($temple['vi_do'] && $temple['kinh_do']): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>
