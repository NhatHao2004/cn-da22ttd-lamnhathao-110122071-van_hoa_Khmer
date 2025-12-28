<?php
/**
 * Thông báo người dùng - User Notifications
 */
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php', 'Vui lòng đăng nhập', 'warning');
}

$pageTitle = 'Thông báo';
$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Đánh dấu đã đọc
if (isset($_GET['read']) && $_GET['read'] === 'all') {
    $pdo->prepare("UPDATE thong_bao_nguoi_dung SET da_doc = 1 WHERE ma_nguoi_nhan = ?")->execute([$userId]);
    redirect(BASE_URL . '/thong-bao.php', 'Đã đánh dấu tất cả là đã đọc', 'success');
}

if (isset($_GET['id'])) {
    $notifId = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM thong_bao_nguoi_dung WHERE ma_thong_bao = ? AND ma_nguoi_nhan = ?");
    $stmt->execute([$notifId, $userId]);
    $notif = $stmt->fetch();
    
    if ($notif) {
        $pdo->prepare("UPDATE thong_bao_nguoi_dung SET da_doc = 1 WHERE ma_thong_bao = ?")->execute([$notifId]);
        if ($notif['duong_dan']) {
            header('Location: ' . $notif['duong_dan']);
            exit;
        }
    }
}

// Lấy thông báo
$notifications = $pdo->prepare("SELECT tb.*, nd.ho_ten as nguoi_gui_ten, nd.anh_dai_dien as nguoi_gui_avatar
    FROM thong_bao_nguoi_dung tb
    LEFT JOIN nguoi_dung nd ON tb.ma_nguoi_gui = nd.ma_nguoi_dung
    WHERE tb.ma_nguoi_nhan = ?
    ORDER BY tb.ngay_tao DESC LIMIT 50");
$notifications->execute([$userId]);
$notifications = $notifications->fetchAll();

$unreadCount = $pdo->prepare("SELECT COUNT(*) FROM thong_bao_nguoi_dung WHERE ma_nguoi_nhan = ? AND da_doc = 0");
$unreadCount->execute([$userId]);
$unreadCount = $unreadCount->fetchColumn();

function getNotifIcon($loai) {
    $icons = [
        'tra_loi_binh_luan' => 'fas fa-reply',
        'like_binh_luan' => 'fas fa-heart',
        'tra_loi_bai_viet' => 'fas fa-comment',
        'like_bai_viet' => 'fas fa-thumbs-up',
        'duyet_binh_luan' => 'fas fa-check-circle',
        'he_thong' => 'fas fa-bell'
    ];
    return $icons[$loai] ?? 'fas fa-bell';
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
.notifications-page { min-height: 100vh; background: #f8fafc; padding-top: 100px; padding-bottom: 3rem; }
.notifications-container { max-width: 700px; margin: 0 auto; padding: 0 1.5rem; }

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.notifications-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.notifications-header h1 i { color: #667eea; }

.unread-badge {
    background: #ef4444;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
}

.btn-mark-read {
    padding: 0.5rem 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-mark-read:hover { border-color: #667eea; color: #667eea; }

.notifications-list {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    overflow: hidden;
}

.notification-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
    text-decoration: none;
    color: inherit;
}

.notification-item:hover { background: #fafbfc; }
.notification-item:last-child { border-bottom: none; }
.notification-item.unread { background: rgba(102, 126, 234, 0.03); }

.notification-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.notification-icon.like { background: linear-gradient(135deg, #ef4444, #dc2626); }
.notification-icon.reply { background: linear-gradient(135deg, #10b981, #059669); }
.notification-icon.system { background: linear-gradient(135deg, #f59e0b, #d97706); }

.notification-content { flex: 1; min-width: 0; }

.notification-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.notification-text {
    font-size: 0.875rem;
    color: #64748b;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-time {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.375rem;
}

.notification-dot {
    width: 8px;
    height: 8px;
    background: #667eea;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
}

.notifications-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: #94a3b8;
}

.notifications-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
</style>

<main class="notifications-page">
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>
                <i class="fas fa-bell"></i> Thông báo
                <?php if ($unreadCount > 0): ?>
                <span class="unread-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </h1>
            <?php if ($unreadCount > 0): ?>
            <a href="?read=all" class="btn-mark-read">
                <i class="fas fa-check-double"></i> Đánh dấu đã đọc
            </a>
            <?php endif; ?>
        </div>

        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
            <div class="notifications-empty">
                <i class="fas fa-bell-slash"></i>
                <p>Chưa có thông báo nào</p>
            </div>
            <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
            <a href="?id=<?= $notif['ma_thong_bao'] ?>" class="notification-item <?= !$notif['da_doc'] ? 'unread' : '' ?>">
                <div class="notification-icon <?= strpos($notif['loai'], 'like') !== false ? 'like' : (strpos($notif['loai'], 'tra_loi') !== false ? 'reply' : '') ?>">
                    <i class="<?= getNotifIcon($notif['loai']) ?>"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title"><?= sanitize($notif['tieu_de']) ?></div>
                    <div class="notification-text"><?= sanitize($notif['noi_dung']) ?></div>
                    <div class="notification-time"><?= timeAgo($notif['ngay_tao']) ?></div>
                </div>
                <?php if (!$notif['da_doc']): ?>
                <div class="notification-dot"></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
