<?php
/**
 * Comment Section Component
 * Sử dụng: include với các biến $comment_type và $content_id
 * 
 * Ví dụ:
 * $comment_type = 'bai_hoc';
 * $content_id = $lesson['ma_bai_hoc'];
 * include 'includes/comment-section.php';
 */

if (!isset($comment_type) || !isset($content_id)) {
    return;
}

// Đảm bảo các hàm cần thiết đã được load
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/functions.php';
}
if (!function_exists('countComments')) {
    require_once __DIR__ . '/comments.php';
}

$totalComments = countComments($comment_type, $content_id);
$currentUserData = isLoggedIn() ? getCurrentUser() : null;
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">

<div class="comments-section" id="commentsSection">
    <div class="comments-header">
        <h3><i class="fas fa-comments"></i> Bình luận</h3>
        <span class="comments-count"><?= $totalComments ?></span>
    </div>
    
    <div class="comments-body">
        <?php if (isLoggedIn()): ?>
        <!-- Comment Form -->
        <form class="comment-form">
            <div class="comment-form-wrapper">
                <div class="comment-form-avatar">
                    <?php if ($currentUserData && $currentUserData['anh_dai_dien']): ?>
                    <img src="<?= UPLOAD_PATH ?>avatar/<?= $currentUserData['anh_dai_dien'] ?>" alt="">
                    <?php else: ?>
                    <?= strtoupper(substr($currentUserData['ho_ten'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="comment-form-input">
                    <textarea class="comment-textarea" placeholder="Viết bình luận của bạn..." rows="3"></textarea>
                    <div class="comment-form-actions">
                        <button type="submit" class="btn-comment btn-comment-primary">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <!-- Login Prompt -->
        <div class="comment-login-prompt">
            <p>Đăng nhập để tham gia bình luận</p>
            <a href="<?= BASE_URL ?>/login.php">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Comments List -->
        <div class="comments-list">
            <div class="comments-loading">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="report-modal" id="reportModal">
    <div class="report-modal-content">
        <div class="report-modal-header">
            <h4>Báo cáo vi phạm</h4>
            <button class="report-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form class="report-form">
            <input type="hidden" name="ma_binh_luan" value="">
            <div class="report-options">
                <label class="report-option">
                    <input type="radio" name="ly_do" value="spam" required>
                    <span>Spam hoặc quảng cáo</span>
                </label>
                <label class="report-option">
                    <input type="radio" name="ly_do" value="xuc_pham">
                    <span>Nội dung xúc phạm</span>
                </label>
                <label class="report-option">
                    <input type="radio" name="ly_do" value="sai_su_that">
                    <span>Thông tin sai sự thật</span>
                </label>
                <label class="report-option">
                    <input type="radio" name="ly_do" value="khac">
                    <span>Lý do khác</span>
                </label>
            </div>
            <textarea class="report-textarea" name="mo_ta" placeholder="Mô tả chi tiết (không bắt buộc)..."></textarea>
            <button type="submit" class="report-submit">
                <i class="fas fa-flag"></i> Gửi báo cáo
            </button>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/comments.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new CommentSystem({
        container: '#commentsSection',
        loai: '<?= $comment_type ?>',
        ma_noi_dung: <?= $content_id ?>,
        csrfToken: '<?= generateCSRFToken() ?>',
        isLoggedIn: <?= isLoggedIn() ? 'true' : 'false' ?>,
        currentUser: <?= $currentUserData ? json_encode(['ma_nguoi_dung' => $currentUserData['ma_nguoi_dung'], 'ho_ten' => $currentUserData['ho_ten']]) : 'null' ?>
    });
});
</script>
