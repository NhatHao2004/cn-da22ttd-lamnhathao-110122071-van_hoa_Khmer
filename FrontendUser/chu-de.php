<?php
/**
 * Chi tiết chủ đề thảo luận - Thread Detail
 */
require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/dien-dan.php');

$pdo = getDBConnection();

// Lấy thông tin chủ đề với icon danh mục
$stmt = $pdo->prepare("SELECT cd.*, nd.ho_ten, nd.anh_dai_dien, nd.tong_diem, dm.ten_danh_muc, dm.mau_sac, dm.icon
    FROM chu_de_thao_luan cd
    JOIN nguoi_dung nd ON cd.ma_nguoi_tao = nd.ma_nguoi_dung
    JOIN danh_muc_dien_dan dm ON cd.ma_danh_muc = dm.ma_danh_muc
    WHERE cd.ma_chu_de = ?");
$stmt->execute([$id]);
$thread = $stmt->fetch();

if (!$thread) redirect(BASE_URL . '/dien-dan.php', 'Chủ đề không tồn tại', 'warning');

$pageTitle = $thread['tieu_de'];

// Tăng lượt xem
$pdo->prepare("UPDATE chu_de_thao_luan SET luot_xem = luot_xem + 1 WHERE ma_chu_de = ?")->execute([$id]);

// Lấy bình luận cha với số lượt thích
try {
    $commentsStmt = $pdo->prepare("SELECT bl.ma_binh_luan, bl.ma_chu_de, bl.ma_nguoi_dung, bl.noi_dung, 
        bl.ngay_tao, bl.ngay_cap_nhat, bl.ma_binh_luan_cha,
        nd.ho_ten, nd.anh_dai_dien, nd.tong_diem,
        COUNT(DISTINCT ltb.ma_luot_thich) as so_luot_thich,
        MAX(CASE WHEN ltb.ma_nguoi_dung = ? THEN 1 ELSE 0 END) as da_thich
        FROM binh_luan_dien_dan bl
        JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung
        LEFT JOIN luot_thich_binh_luan ltb ON bl.ma_binh_luan = ltb.ma_binh_luan
        WHERE bl.ma_chu_de = ? AND bl.ma_binh_luan_cha IS NULL
        GROUP BY bl.ma_binh_luan, bl.ma_chu_de, bl.ma_nguoi_dung, bl.noi_dung, 
                 bl.ngay_tao, bl.ngay_cap_nhat, bl.ma_binh_luan_cha,
                 nd.ho_ten, nd.anh_dai_dien, nd.tong_diem
        ORDER BY bl.ngay_tao ASC");
    $commentsStmt->execute([isLoggedIn() ? $_SESSION['user_id'] : 0, $id]);
    $comments = $commentsStmt->fetchAll();
    
    // Lấy replies cho mỗi comment
    foreach ($comments as &$comment) {
        $repliesStmt = $pdo->prepare("SELECT bl.ma_binh_luan, bl.ma_chu_de, bl.ma_nguoi_dung, bl.noi_dung,
            bl.ngay_tao, bl.ngay_cap_nhat, bl.ma_binh_luan_cha,
            nd.ho_ten, nd.anh_dai_dien, nd.tong_diem,
            COUNT(DISTINCT ltb.ma_luot_thich) as so_luot_thich,
            MAX(CASE WHEN ltb.ma_nguoi_dung = ? THEN 1 ELSE 0 END) as da_thich
            FROM binh_luan_dien_dan bl
            JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung
            LEFT JOIN luot_thich_binh_luan ltb ON bl.ma_binh_luan = ltb.ma_binh_luan
            WHERE bl.ma_binh_luan_cha = ?
            GROUP BY bl.ma_binh_luan, bl.ma_chu_de, bl.ma_nguoi_dung, bl.noi_dung,
                     bl.ngay_tao, bl.ngay_cap_nhat, bl.ma_binh_luan_cha,
                     nd.ho_ten, nd.anh_dai_dien, nd.tong_diem
            ORDER BY bl.ngay_tao ASC");
        $repliesStmt->execute([isLoggedIn() ? $_SESSION['user_id'] : 0, $comment['ma_binh_luan']]);
        $comment['replies'] = $repliesStmt->fetchAll();
    }
} catch (Exception $e) {
    $comments = [];
}

// Đếm tổng số bình luận (bao gồm cả replies)
$commentCount = count($comments);
foreach ($comments as $comment) {
    $commentCount += count($comment['replies'] ?? []);
}

// Xử lý đăng bài trả lời
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $noi_dung = trim($_POST['noi_dung'] ?? '');
        $parent_id = intval($_POST['parent_id'] ?? 0);
        
        if (strlen($noi_dung) >= 5) {
            try {
                if ($parent_id > 0) {
                    // Reply to a comment
                    $stmt = $pdo->prepare("INSERT INTO binh_luan_dien_dan (ma_chu_de, ma_nguoi_dung, noi_dung, ma_binh_luan_cha) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id, $_SESSION['user_id'], $noi_dung, $parent_id]);
                } else {
                    // New comment
                    $stmt = $pdo->prepare("INSERT INTO binh_luan_dien_dan (ma_chu_de, ma_nguoi_dung, noi_dung) VALUES (?, ?, ?)");
                    $stmt->execute([$id, $_SESSION['user_id'], $noi_dung]);
                }
                addUserPoints($_SESSION['user_id'], 2, 'Trả lời chủ đề');
                redirect(BASE_URL . "/chu-de.php?id=$id#comments-section", 'Đã đăng bài trả lời!', 'success');
            } catch (Exception $e) {
                error_log("Comment error: " . $e->getMessage());
            }
        }
    }
}

// Lấy chủ đề liên quan cùng danh mục
$relatedStmt = $pdo->prepare("SELECT cd.*, nd.ho_ten, COUNT(bl.ma_binh_luan) as so_binh_luan
    FROM chu_de_thao_luan cd
    JOIN nguoi_dung nd ON cd.ma_nguoi_tao = nd.ma_nguoi_dung
    LEFT JOIN binh_luan_dien_dan bl ON cd.ma_chu_de = bl.ma_chu_de
    WHERE cd.ma_danh_muc = ? AND cd.ma_chu_de != ? AND cd.trang_thai = 'mo'
    GROUP BY cd.ma_chu_de
    ORDER BY cd.ngay_tao DESC
    LIMIT 4");
$relatedStmt->execute([$thread['ma_danh_muc'], $id]);
$relatedThreads = $relatedStmt->fetchAll();

?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<?php if (isset($_GET['msg']) && isset($_GET['type'])): ?>
<div class="alert-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;">
    <?php if ($_GET['type'] === 'success'): ?>
    <div style="display: flex; align-items: center; gap: 1rem; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1.25rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
        <span style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($_GET['msg']) ?></span>
    </div>
    <?php else: ?>
    <div style="display: flex; align-items: center; gap: 1rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1.25rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
        <span style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($_GET['msg']) ?></span>
    </div>
    <?php endif; ?>
</div>
<script>
// Xóa query string khỏi URL ngay lập tức để tránh hiển thị lại khi reload
if (window.history.replaceState) {
    const url = new URL(window.location.href);
    url.searchParams.delete('msg');
    url.searchParams.delete('type');
    window.history.replaceState({path: url.href}, '', url.href);
}

// Ẩn thông báo sau 3 giây
setTimeout(() => {
    const alert = document.querySelector('.alert-container');
    if (alert) {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }
}, 3000);
</script>
<style>
.alert-container { animation: slideIn 0.3s ease; }
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(400px); opacity: 0; }
}
</style>
<?php endif; ?>

<!-- Unified Detail Page Styles -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/detail-page-unified.css">

<style>
/* Thread-Specific Styles - Unified Design */
.thread-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
}

.thread-category-badge i {
    font-size: 1rem;
}

/* Thread Image */
.thread-image {
    margin: 1.5rem 0;
    border-radius: 16px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.thread-image img {
    width: 100%;
    height: auto;
    display: block;
}

/* Original Post Styling */
.original-post-card {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
    border: 2px solid rgba(102, 126, 234, 0.2);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.post-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.post-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.5rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.post-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-author-info {
    flex: 1;
}

.post-author {
    font-weight: 900;
    color: #000000;
    font-size: 1.125rem;
    display: block;
    margin-bottom: 0.25rem;
}

.post-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-left: 0.5rem;
    border: 1px solid #fde68a;
}

.post-time {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.post-time i {
    color: #f59e0b;
}

.post-body {
    font-size: 1.0625rem;
    line-height: 1.8;
    color: #1e293b;
    font-weight: 500;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Comments Section */
.comments-wrapper {
    margin-top: 2rem;
}

.comment-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #000000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.125rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.comment-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.comment-author {
    font-weight: 900;
    color: #000000;
    font-size: 0.9375rem;
}

.comment-time {
    font-size: 0.8125rem;
    color: #64748b;
    font-weight: 600;
}

.comment-text {
    font-size: 1rem;
    line-height: 1.7;
    color: #475569;
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.comment-actions {
    display: flex;
    gap: 1rem;
}

.comment-action-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.comment-action-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #000000;
}

.comment-action-btn.liked {
    color: #ef4444;
    border-color: #fecaca;
    background: #fef2f2;
}

.comment-action-btn.liked i {
    color: #ef4444;
}

.comment-edit-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9375rem;
    min-height: 80px;
    resize: vertical;
    font-family: inherit;
    font-weight: 500;
}

.comment-edit-form textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Comment Form */
.comment-form-wrapper {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
}

.comment-form-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.125rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 2px solid white;
}

.comment-form-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comment-form {
    flex: 1;
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
    transition: all 0.3s ease;
    font-weight: 500;
    background: white;
    margin-bottom: 0.75rem;
}

.comment-form textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.comment-submit-btn {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.comment-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.comment-login-prompt {
    text-align: center;
    padding: 2rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.comment-login-prompt i {
    font-size: 2.5rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.comment-login-prompt p {
    color: #64748b;
    font-weight: 600;
    margin-bottom: 1rem;
}

.comment-login-prompt a {
    color: #667eea;
    font-weight: 700;
    text-decoration: none;
}

.comment-login-prompt a:hover {
    text-decoration: underline;
}

.no-comments {
    text-align: center;
    padding: 3rem 2rem;
    color: #94a3b8;
}

.no-comments i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

/* Locked Message */
.locked-message {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
    border: 2px solid rgba(239, 68, 68, 0.2);
    border-radius: 12px;
    color: #dc2626;
    font-weight: 700;
}

.locked-message i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    display: block;
}

/* Notification Animations */
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .original-post-card {
        padding: 1.5rem;
    }
    
    .post-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .post-avatar {
        width: 56px;
        height: 56px;
        font-size: 1.25rem;
    }
    
    .comment-form-wrapper {
        flex-direction: column;
        padding: 1rem;
    }
    
    .comment-item {
        flex-direction: column;
    }
    
    .comment-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

/* Notification Animations */
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
</style>

<main class="detail-page">
    <!-- Hero Section -->
    <?php 
    $imagePath = $thread['hinh_anh'] ?? '';
    $hasImage = !empty($imagePath);
    if ($hasImage) {
        if (strpos($imagePath, 'http') === 0) {
            $imageUrl = $imagePath;
        } elseif (strpos($imagePath, 'uploads/') === 0) {
            $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
        } else {
            // File được lưu ở DoAn_ChuyenNganh/uploads/forum/
            $imageUrl = '/DoAn_ChuyenNganh/uploads/forum/' . $imagePath;
        }
    }
    $categoryColor = $thread['mau_sac'] ?? '#667eea';
    ?>
    <section class="detail-hero">
        <div class="container">
                <nav class="detail-breadcrumb">
                    <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/dien-dan.php">Diễn đàn</a>
                    <span>/</span>
                    <a href="<?= BASE_URL ?>/dien-dan-danh-muc.php?id=<?= $thread['ma_danh_muc'] ?>"><?= sanitize($thread['ten_danh_muc']) ?></a>
                    <span>/</span>
                    <span><?= sanitize(mb_substr($thread['tieu_de'], 0, 40)) ?>...</span>
                </nav>
                
                <span class="thread-category-badge" style="background: <?= $categoryColor ?>;">
                    <?php if (!empty($thread['icon'])): ?>
                    <i class="<?= $thread['icon'] ?>"></i>
                    <?php endif; ?>
                    <?= sanitize($thread['ten_danh_muc']) ?>
                </span>
                
                <h1 class="detail-title"><?= sanitize($thread['tieu_de']) ?></h1>
                
                <div class="detail-meta">
                    <span class="detail-badge">
                        <i class="fas fa-user"></i>
                        <?= sanitize($thread['ho_ten']) ?>
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('d/m/Y', strtotime($thread['ngay_tao'])) ?>
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-eye"></i>
                        <?= number_format($thread['luot_xem']) ?> lượt xem
                    </span>
                    <span class="detail-badge">
                        <i class="fas fa-comments"></i>
                        <?= $commentCount ?> bình luận
                    </span>
                </div>
            </div>
    </section>

    <!-- Main Content -->
    <section class="detail-main-content">
        <div class="container">
            <!-- Original Post -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-file-alt"></i>
                    <h3>Nội dung chủ đề</h3>
                </div>
                <div class="content-card-body">
                    <div class="original-post-card">
                        <div class="post-header">
                            <div class="post-avatar">
                                <?php if ($thread['anh_dai_dien']): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $thread['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($thread['ho_ten'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="post-author-info">
                                <span class="post-author">
                                    <?= sanitize($thread['ho_ten']) ?>
                                    <?php if ($thread['tong_diem'] >= 100): ?>
                                    <span class="post-badge"><i class="fas fa-star"></i> Top User</span>
                                    <?php endif; ?>
                                </span>
                                <div class="post-time">
                                    <i class="fas fa-clock"></i>
                                    <?= timeAgo($thread['ngay_tao']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="post-body">
                            <?= $thread['noi_dung'] ?>
                        </div>
                        
                        <?php if ($hasImage): ?>
                        <div class="thread-image">
                            <img src="<?= $imageUrl ?>" alt="<?= sanitize($thread['tieu_de']) ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="content-card" id="comments-section">
                <div class="content-card-header">
                    <i class="fas fa-comments"></i>
                    <h3>Bình luận (<?= $commentCount ?>)</h3>
                </div>
                <div class="content-card-body">
                    <?php if (isLoggedIn()): ?>
                        <?php if ($thread['ghim']): ?>
                        <div class="locked-message">
                            <i class="fas fa-lock"></i>
                            <p>Chủ đề này đã bị khóa, không thể bình luận.</p>
                        </div>
                        <?php else: ?>
                        <?php $currentUser = getCurrentUser(); ?>
                        <div class="comment-form-wrapper">
                            <div class="comment-form-avatar">
                                <?php if (!empty($currentUser['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $currentUser['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($currentUser['ho_ten'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <form class="comment-form" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <textarea name="noi_dung" placeholder="Viết bình luận của bạn..." required minlength="5"></textarea>
                                <button type="submit" class="comment-submit-btn">
                                    <i class="fas fa-paper-plane"></i> Gửi bình luận
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="comment-login-prompt">
                        <i class="fas fa-user-circle"></i>
                        <p>Vui lòng <a href="<?= BASE_URL ?>/login.php">đăng nhập</a> để bình luận</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="comments-wrapper">
                        <?php if (empty($comments)): ?>
                        <div class="no-comments">
                            <i class="far fa-comment-dots"></i>
                            <p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?= $comment['ma_binh_luan'] ?>">
                            <div class="comment-avatar">
                                <?php if (!empty($comment['anh_dai_dien'])): ?>
                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $comment['anh_dai_dien'] ?>" alt="">
                                <?php else: ?>
                                <?= strtoupper(mb_substr($comment['ho_ten'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">
                                        <?= sanitize($comment['ho_ten']) ?>
                                        <?php if ($comment['tong_diem'] >= 100): ?>
                                        <span class="post-badge"><i class="fas fa-star"></i> Top</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="comment-time"><?= timeAgo($comment['ngay_tao']) ?></span>
                                </div>
                                <div class="comment-text-display"><?= nl2br(sanitize($comment['noi_dung'])) ?></div>
                                <div class="comment-edit-form" style="display: none;">
                                    <textarea class="comment-edit-textarea"><?= sanitize($comment['noi_dung']) ?></textarea>
                                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                        <button class="comment-submit-btn comment-save-btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            <i class="fas fa-check"></i> Lưu
                                        </button>
                                        <button class="comment-action-btn comment-cancel-btn">
                                            <i class="fas fa-times"></i> Hủy
                                        </button>
                                    </div>
                                </div>
                                <div class="comment-actions">
                                    <button class="comment-action-btn like-btn <?= $comment['da_thich'] ? 'liked' : '' ?>" 
                                            data-comment-id="<?= $comment['ma_binh_luan'] ?>"
                                            <?= !isLoggedIn() ? 'disabled' : '' ?>>
                                        <i class="<?= $comment['da_thich'] ? 'fas' : 'far' ?> fa-heart"></i> 
                                        Thích <span class="like-count"><?= $comment['so_luot_thich'] > 0 ? '(' . $comment['so_luot_thich'] . ')' : '' ?></span>
                                    </button>
                                    <?php if (isLoggedIn()): ?>
                                        <?php if ($_SESSION['user_id'] == $comment['ma_nguoi_dung']): ?>
                                        <button class="comment-action-btn edit-btn" 
                                                data-comment-id="<?= $comment['ma_binh_luan'] ?>"
                                                style="color: #3b82f6;">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <button class="comment-action-btn delete-btn" 
                                                data-comment-id="<?= $comment['ma_binh_luan'] ?>"
                                                style="color: #ef4444;">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                        <?php else: ?>
                                        <button class="comment-action-btn reply-btn" data-comment-id="<?= $comment['ma_binh_luan'] ?>">
                                            <i class="fas fa-reply"></i> Trả lời
                                        </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reply Form (hidden by default) -->
                                <div class="reply-form-wrapper" style="display: none; margin-top: 1rem;">
                                    <form class="reply-form" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="parent_id" value="<?= $comment['ma_binh_luan'] ?>">
                                        <textarea name="noi_dung" placeholder="Viết câu trả lời của bạn..." required minlength="5" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.9375rem; min-height: 80px; resize: vertical; font-family: inherit; font-weight: 500;"></textarea>
                                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                            <button type="submit" class="comment-submit-btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                                <i class="fas fa-paper-plane"></i> Gửi
                                            </button>
                                            <button type="button" class="comment-action-btn cancel-reply-btn">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Replies -->
                                <?php if (!empty($comment['replies'])): ?>
                                <div class="replies-wrapper" style="margin-top: 1rem; padding-left: 2rem; border-left: 2px solid #e2e8f0;">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                    <div class="comment-item reply-item" data-comment-id="<?= $reply['ma_binh_luan'] ?>" style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                                        <div style="display: flex; gap: 0.75rem;">
                                            <div class="comment-avatar" style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                <?php if (!empty($reply['anh_dai_dien'])): ?>
                                                <img src="<?= UPLOAD_PATH ?>avatar/<?= $reply['anh_dai_dien'] ?>" alt="">
                                                <?php else: ?>
                                                <?= strtoupper(mb_substr($reply['ho_ten'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div style="flex: 1;">
                                                <div class="comment-header">
                                                    <span class="comment-author" style="font-size: 0.875rem;">
                                                        <?= sanitize($reply['ho_ten']) ?>
                                                        <?php if ($reply['tong_diem'] >= 100): ?>
                                                        <span class="post-badge" style="font-size: 0.6875rem;"><i class="fas fa-star"></i> Top</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="comment-time" style="font-size: 0.75rem;"><?= timeAgo($reply['ngay_tao']) ?></span>
                                                </div>
                                                <div class="comment-text-display" style="font-size: 0.9375rem;"><?= nl2br(sanitize($reply['noi_dung'])) ?></div>
                                                <div class="comment-edit-form" style="display: none;">
                                                    <textarea class="comment-edit-textarea"><?= sanitize($reply['noi_dung']) ?></textarea>
                                                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                                        <button class="comment-submit-btn comment-save-btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                                            <i class="fas fa-check"></i> Lưu
                                                        </button>
                                                        <button class="comment-action-btn comment-cancel-btn">
                                                            <i class="fas fa-times"></i> Hủy
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="comment-actions" style="margin-top: 0.5rem;">
                                                    <button class="comment-action-btn like-btn <?= $reply['da_thich'] ? 'liked' : '' ?>" 
                                                            data-comment-id="<?= $reply['ma_binh_luan'] ?>"
                                                            <?= !isLoggedIn() ? 'disabled' : '' ?>
                                                            style="padding: 0.25rem 0.625rem; font-size: 0.75rem;">
                                                        <i class="<?= $reply['da_thich'] ? 'fas' : 'far' ?> fa-heart"></i> 
                                                        Thích <span class="like-count"><?= $reply['so_luot_thich'] > 0 ? '(' . $reply['so_luot_thich'] . ')' : '' ?></span>
                                                    </button>
                                                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $reply['ma_nguoi_dung']): ?>
                                                    <button class="comment-action-btn edit-btn" 
                                                            data-comment-id="<?= $reply['ma_binh_luan'] ?>"
                                                            style="color: #3b82f6; padding: 0.25rem 0.625rem; font-size: 0.75rem;">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </button>
                                                    <button class="comment-action-btn delete-btn" 
                                                            data-comment-id="<?= $reply['ma_binh_luan'] ?>"
                                                            style="color: #ef4444; padding: 0.25rem 0.625rem; font-size: 0.75rem;">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                    <?php endif; ?>
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
            
            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <a href="<?= BASE_URL ?>/dien-dan-danh-muc.php?id=<?= $thread['ma_danh_muc'] ?>" class="action-btn outline">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh mục
                </a>
                <a href="<?= BASE_URL ?>/dien-dan.php" class="action-btn outline">
                    <i class="fas fa-list"></i>
                    Tất cả chủ đề
                </a>
                <?php if (isLoggedIn() && $_SESSION['user_id'] == $thread['ma_nguoi_tao']): ?>
                <a href="<?= BASE_URL ?>/sua-chu-de.php?id=<?= $id ?>" class="action-btn outline">
                    <i class="fas fa-edit"></i>
                    Sửa chủ đề
                </a>
                <button onclick="deleteThread(<?= $id ?>)" class="action-btn outline" style="color: #ef4444; border-color: #ef4444;">
                    <i class="fas fa-trash"></i>
                    Xóa chủ đề
                </button>
                <?php endif; ?>
                <button onclick="shareThread()" class="action-btn outline">
                    <i class="fas fa-share-alt"></i>
                    Chia sẻ
                </button>
            </div>
            
            <!-- Related Threads -->
            <?php if (!empty($relatedThreads)): ?>
            <div class="related-section">
                <div class="related-header">
                    <i class="fas fa-th-large"></i>
                    <h4>Chủ đề liên quan</h4>
                </div>
                <div class="related-grid">
                    <?php foreach ($relatedThreads as $item): ?>
                    <a href="<?= BASE_URL ?>/chu-de.php?id=<?= $item['ma_chu_de'] ?>" class="related-card">
                        <div class="related-card-image" style="background: <?= $categoryColor ?>;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?= sanitize($item['tieu_de']) ?></div>
                            <div class="related-card-meta">
                                <i class="fas fa-user"></i>
                                <?= sanitize($item['ho_ten']) ?>
                                <span style="margin-left: 0.5rem;">
                                    <i class="fas fa-comments"></i>
                                    <?= $item['so_binh_luan'] ?>
                                </span>
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
// Share function
function shareThread() {
    if (navigator.share) {
        navigator.share({
            title: '<?= addslashes($thread['tieu_de']) ?>',
            text: 'Xem chủ đề thảo luận này trên diễn đàn',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Đã sao chép link!');
        });
    }
}

// Delete thread function
function deleteThread(id) {
    if (confirm('Bạn có chắc chắn muốn xóa chủ đề này? Hành động này không thể hoàn tác!')) {
        const formData = new FormData();
        formData.append('thread_id', id);
        
        fetch('<?= BASE_URL ?>/api/delete-thread.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Chuyển hướng với thông báo success
                window.location.href = '<?= BASE_URL ?>/dien-dan.php?msg=' + encodeURIComponent('Đã xóa chủ đề thành công!') + '&type=success';
            } else {
                // Chuyển hướng với thông báo error
                window.location.href = '<?= BASE_URL ?>/chu-de.php?id=' + id + '&msg=' + encodeURIComponent(data.message || 'Không thể xóa chủ đề') + '&type=error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Chuyển hướng với thông báo error
            window.location.href = '<?= BASE_URL ?>/chu-de.php?id=' + id + '&msg=' + encodeURIComponent('Có lỗi xảy ra: ' + error.message) + '&type=error';
        });
    }
}

// Smooth scroll to comments
if (window.location.hash === '#comments-section') {
    setTimeout(() => {
        document.getElementById('comments-section').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

// ============================================
// COMMENT ACTIONS: Like, Edit, Delete, Reply
// ============================================

// Reply Button
document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentItem = this.closest('.comment-item');
        const replyForm = commentItem.querySelector('.reply-form-wrapper');
        
        // Hide all other reply forms
        document.querySelectorAll('.reply-form-wrapper').forEach(form => {
            if (form !== replyForm) form.style.display = 'none';
        });
        
        // Toggle this reply form
        replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
        
        // Focus textarea
        if (replyForm.style.display === 'block') {
            replyForm.querySelector('textarea').focus();
        }
    });
});

// Cancel Reply
document.querySelectorAll('.cancel-reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const replyForm = this.closest('.reply-form-wrapper');
        replyForm.style.display = 'none';
        replyForm.querySelector('textarea').value = '';
    });
});

// Like/Unlike Comment
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentId = this.dataset.commentId;
        const icon = this.querySelector('i');
        const likeCount = this.querySelector('.like-count');
        
        fetch('<?= BASE_URL ?>/api/like-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.liked) {
                    this.classList.add('liked');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    this.classList.remove('liked');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                likeCount.textContent = data.likeCount > 0 ? `(${data.likeCount})` : '';
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thích bình luận');
        });
    });
});

// Edit Comment
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentItem = this.closest('.comment-item');
        const textDisplay = commentItem.querySelector('.comment-text-display');
        const editForm = commentItem.querySelector('.comment-edit-form');
        const actions = commentItem.querySelector('.comment-actions');
        
        textDisplay.style.display = 'none';
        actions.style.display = 'none';
        editForm.style.display = 'block';
    });
});

// Cancel Edit
document.querySelectorAll('.comment-cancel-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentItem = this.closest('.comment-item');
        const textDisplay = commentItem.querySelector('.comment-text-display');
        const editForm = commentItem.querySelector('.comment-edit-form');
        const actions = commentItem.querySelector('.comment-actions');
        
        textDisplay.style.display = 'block';
        actions.style.display = 'flex';
        editForm.style.display = 'none';
    });
});

// Save Edit
document.querySelectorAll('.comment-save-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentItem = this.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        const textarea = commentItem.querySelector('.comment-edit-textarea');
        const noiDung = textarea.value.trim();
        
        if (noiDung.length < 5) {
            alert('Nội dung phải có ít nhất 5 ký tự');
            return;
        }
        
        fetch('<?= BASE_URL ?>/api/edit-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}&noi_dung=${encodeURIComponent(noiDung)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const textDisplay = commentItem.querySelector('.comment-text-display');
                const editForm = commentItem.querySelector('.comment-edit-form');
                const actions = commentItem.querySelector('.comment-actions');
                
                textDisplay.innerHTML = data.noi_dung.replace(/\n/g, '<br>');
                textDisplay.style.display = 'block';
                actions.style.display = 'flex';
                editForm.style.display = 'none';
                
                // Show success message
                showNotification('success', data.message);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi cập nhật bình luận');
        });
    });
});

// Delete Comment
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
            return;
        }
        
        const commentItem = this.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        
        // Ẩn form edit nếu đang mở
        const editForm = commentItem.querySelector('.comment-edit-form');
        if (editForm) {
            editForm.style.display = 'none';
        }
        
        fetch('<?= BASE_URL ?>/api/delete-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                commentItem.style.transition = 'opacity 0.3s ease';
                commentItem.style.opacity = '0';
                setTimeout(() => {
                    commentItem.remove();
                    // Update comment count
                    const countElement = document.querySelector('.content-card-header h3');
                    if (countElement) {
                        const match = countElement.textContent.match(/\d+/);
                        if (match) {
                            const currentCount = parseInt(match[0]);
                            countElement.innerHTML = `<i class="fas fa-comments"></i> Bình luận (${currentCount - 1})`;
                        }
                    }
                    showNotification('success', data.message);
                    
                    // Reload trang để cập nhật số lượng bình luận chính xác
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }, 300);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa bình luận');
        });
    });
});

// Notification helper
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 8px;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
