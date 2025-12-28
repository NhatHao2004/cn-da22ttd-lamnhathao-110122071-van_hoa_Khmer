<?php
/**
 * Bảng xếp hạng - Modern Design
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('leaderboard');

$pdo = getDBConnection();

// Get top users
$query = "SELECT n.ma_nguoi_dung, n.ho_ten, n.anh_dai_dien, n.tong_diem, 
          (SELECT COUNT(*) FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = n.ma_nguoi_dung AND trang_thai = 'hoan_thanh') as lessons,
          (SELECT COUNT(*) FROM huy_hieu_nguoi_dung WHERE ma_nguoi_dung = n.ma_nguoi_dung) as badges
          FROM nguoi_dung n 
          WHERE n.trang_thai = 'hoat_dong' 
          ORDER BY n.tong_diem DESC 
          LIMIT 50";

$topUsers = $pdo->query($query)->fetchAll();

// Get current user rank
$currentUserRank = null;
$currentUserData = null;
if (isLoggedIn()) {
    $rankStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM nguoi_dung WHERE tong_diem > (SELECT tong_diem FROM nguoi_dung WHERE ma_nguoi_dung = ?)");
    $rankStmt->execute([$_SESSION['user_id']]);
    $currentUserRank = $rankStmt->fetchColumn();
    
    $userStmt = $pdo->prepare("SELECT tong_diem FROM nguoi_dung WHERE ma_nguoi_dung = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $currentUserData = $userStmt->fetch();
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Leaderboard Hero Section ===== */
.leaderboard-hero {
    min-height: 40vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #000000;
    padding: 1rem 0;
}

.hero-icon {
    width: 80px;
    height: 80px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #000000 !important;
}

.hero-subtitle {
    font-size: 1.125rem;
    color: #000000;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto 1rem;
    line-height: 1.6;
}

/* ===== Main Content Area ===== */
.leaderboard-main {
    background: #ffffff;
    min-height: 60vh;
    padding: 2rem 0 4rem;
}

/* ===== Top 3 Podium ===== */
.podium-section {
    margin-bottom: 3rem;
    position: relative;
    z-index: 10;
}

.podium-grid {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 1.5rem;
    max-width: 900px;
    margin: 0 auto;
}

.podium-item {
    text-align: center;
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    flex: 1;
    max-width: 260px;
    border: 2px solid #000000;
}

.podium-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.podium-item.first {
    order: 2;
    padding: 2.5rem 2rem;
    background: #ffffff;
    border: 3px solid #000000;
}

.podium-item.second {
    order: 1;
}

.podium-item.third {
    order: 3;
}

.podium-crown {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
}

.podium-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 1rem;
    border: 3px solid #000000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.podium-item.first .podium-avatar {
    width: 110px;
    height: 110px;
    border-width: 4px;
}

.podium-rank {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.125rem;
    margin: -24px auto 0.75rem;
    position: relative;
    z-index: 1;
    background: #ffffff;
    border: 2px solid #000000;
    color: #000000;
}

.podium-item.first .podium-rank {
    background: #ffffff;
    color: #000000;
    width: 48px;
    height: 48px;
    font-size: 1.375rem;
    margin-top: -28px;
    border-width: 3px;
}

.podium-item.second .podium-rank {
    background: #94a3b8;
    color: #ffffff;
}

.podium-item.third .podium-rank {
    background: #f97316;
    color: #ffffff;
}

.podium-name {
    font-size: 1.125rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.podium-item.first .podium-name {
    font-size: 1.25rem;
}

.podium-points {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
}

.podium-item.first .podium-points {
    font-size: 1.75rem;
}

.podium-stats {
    display: flex;
    justify-content: center;
    gap: 1.25rem;
    margin-top: 1rem;
    font-size: 0.8125rem;
    color: #000000;
    font-weight: 700;
}

.podium-stats span {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

/* ===== Leaderboard Table ===== */
.leaderboard-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    max-width: 900px;
    margin: 0 auto;
    border: 2px solid #000000;
}

.leaderboard-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #000000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
}

.leaderboard-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.leaderboard-title i {
    color: #f59e0b;
}

.leaderboard-list {
    padding: 0;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 2rem;
    transition: all 0.3s ease;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.leaderboard-item:last-child {
    border-bottom: none;
}

.leaderboard-item:hover {
    background: #f8fafc;
}

.leaderboard-item.current-user {
    background: #ffffff;
    border-left: 4px solid #000000;
}

.item-rank {
    width: 50px;
    font-weight: 900;
    font-size: 1.125rem;
    color: #000000;
    text-align: center;
}

.item-rank.top {
    font-size: 1.75rem;
}

.item-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 2px solid #000000;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-weight: 900;
    font-size: 1.125rem;
    color: #000000;
    margin-bottom: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.item-name .you-badge {
    font-size: 0.6875rem;
    padding: 0.25rem 0.625rem;
    background: #000000;
    color: #ffffff;
    border-radius: 50px;
    font-weight: 700;
}

.item-stats {
    display: flex;
    gap: 1.25rem;
    font-size: 0.875rem;
    color: #000000;
    font-weight: 700;
}

.item-stats span {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.item-stats i {
    font-size: 0.875rem;
    color: #f59e0b;
}

.item-points {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    min-width: 100px;
    text-align: right;
}

/* ===== How to Earn Section ===== */
.earn-section {
    max-width: 900px;
    margin: 3rem auto 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    padding: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.earn-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.earn-title {
    color: #ffffff;
    font-size: 1.75rem;
    font-weight: 900;
    margin-bottom: 2.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.earn-title-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.earn-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

.earn-item {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem 1.5rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid rgba(255, 255, 255, 0.5);
    position: relative;
    overflow: hidden;
}

.earn-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.earn-item:hover::before {
    opacity: 1;
}

.earn-item:hover {
    transform: translateY(-10px) scale(1.05);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    border-color: #ffffff;
}

.earn-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1.25rem;
    position: relative;
    transition: all 0.3s ease;
}

.earn-item:hover .earn-icon {
    transform: scale(1.1) rotate(5deg);
}

.earn-icon::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: inherit;
    opacity: 0.2;
    z-index: -1;
}

.earn-icon.green { 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

.earn-icon.blue { 
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.earn-icon.red { 
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.earn-info {
    position: relative;
}

.earn-name {
    font-weight: 900;
    font-size: 1.125rem;
    color: #1a202c;
    margin-bottom: 0.75rem;
    line-height: 1.3;
    white-space: nowrap;
}

.earn-points {
    font-size: 1.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
}

.earn-item.green .earn-points {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.earn-item.blue .earn-points {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.earn-item.red .earn-points {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ===== Responsive ===== */
@media (max-width: 1024px) {
    .podium-grid {
        max-width: 700px;
    }
    
    .podium-item {
        max-width: 220px;
        padding: 1.75rem 1.25rem;
    }
    
    .podium-item.first {
        padding: 2.25rem 1.75rem;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 1.75rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .podium-grid {
        flex-direction: column;
        align-items: center;
    }
    
    .podium-item {
        order: unset !important;
        max-width: 320px;
        width: 100%;
    }
    
    .podium-item.first {
        order: -1 !important;
    }
    
    .leaderboard-item {
        padding: 1rem 1.25rem;
        gap: 1rem;
    }
    
    .item-rank {
        width: 40px;
        font-size: 1rem;
    }
    
    .item-rank.top {
        font-size: 1.5rem;
    }
    
    .item-avatar {
        width: 48px;
        height: 48px;
    }
    
    .item-name {
        font-size: 1rem;
    }
    
    .item-stats {
        display: none;
    }
    
    .item-points {
        font-size: 1.25rem;
        min-width: 80px;
    }
    
    .earn-grid {
        grid-template-columns: 1fr;
    }
    
    .earn-section {
        padding: 2.5rem 1.5rem;
    }
    
    .earn-title {
        font-size: 1.5rem;
    }
    
    .earn-title-icon {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .leaderboard-header {
        padding: 1.25rem 1.5rem;
    }
    
    .leaderboard-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .hero-icon {
        width: 64px;
        height: 64px;
        font-size: 2rem;
    }
    
    .podium-item {
        padding: 1.5rem 1rem;
    }
    
    .podium-item.first {
        padding: 2rem 1.5rem;
    }
    
    .podium-avatar {
        width: 70px;
        height: 70px;
    }
    
    .podium-item.first .podium-avatar {
        width: 90px;
        height: 90px;
    }
    
    .leaderboard-item {
        padding: 1rem;
    }
    
    .item-rank {
        width: 36px;
    }
    
    .item-avatar {
        width: 44px;
        height: 44px;
    }
    
    .earn-item {
        padding: 1.25rem;
    }
    
    .earn-icon {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
    
    .earn-name {
        font-size: 1rem;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.75rem;
    }
    
    .podium-grid {
        flex-direction: column;
        align-items: center;
    }
    
    .podium-item {
        order: unset !important;
        max-width: 280px;
        width: 100%;
    }
    
    .podium-item.first {
        order: -1 !important;
    }
    
    .leaderboard-item {
        padding: 1rem;
    }
    
    .item-stats {
        display: none;
    }
    
    .earn-grid {
        grid-template-columns: 1fr;
    }
    
    .earn-item {
        padding: 1.75rem 1.25rem;
    }
    
    .earn-icon {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
    }
    
    .earn-name {
        font-size: 1rem;
    }
    
    .earn-points {
        font-size: 1.375rem;
    }
}
</style>

<main>
    <!-- Hero Section -->
    <section class="leaderboard-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-icon">🏆</div>
                <h1 class="hero-title"><?= __('leaderboard') ?></h1>
                <p class="hero-subtitle"><?= __('leaderboard_desc') ?></p>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="leaderboard-main">
        <div class="container">
            <!-- Top 3 Podium -->
            <?php if (count($topUsers) >= 3): ?>
            <div class="podium-section">
                <div class="podium-grid">
                    <!-- 2nd Place -->
                    <div class="podium-item second">
                        <img src="<?= !empty($topUsers[1]['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $topUsers[1]['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                             alt="" class="podium-avatar">
                        <div class="podium-rank">2</div>
                        <div class="podium-name"><?= sanitize($topUsers[1]['ho_ten']) ?></div>
                        <div class="podium-points"><?= formatNumber($topUsers[1]['tong_diem']) ?></div>
                        <div class="podium-stats">
                            <span><i class="fas fa-book"></i> <?= $topUsers[1]['lessons'] ?></span>
                            <span><i class="fas fa-medal"></i> <?= $topUsers[1]['badges'] ?></span>
                        </div>
                    </div>
                    
                    <!-- 1st Place -->
                    <div class="podium-item first">
                        <div class="podium-crown">👑</div>
                        <img src="<?= !empty($topUsers[0]['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $topUsers[0]['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                             alt="" class="podium-avatar">
                        <div class="podium-rank">1</div>
                        <div class="podium-name"><?= sanitize($topUsers[0]['ho_ten']) ?></div>
                        <div class="podium-points"><?= formatNumber($topUsers[0]['tong_diem']) ?></div>
                        <div class="podium-stats">
                            <span><i class="fas fa-book"></i> <?= $topUsers[0]['lessons'] ?></span>
                            <span><i class="fas fa-medal"></i> <?= $topUsers[0]['badges'] ?></span>
                        </div>
                    </div>
                    
                    <!-- 3rd Place -->
                    <div class="podium-item third">
                        <img src="<?= !empty($topUsers[2]['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $topUsers[2]['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                             alt="" class="podium-avatar">
                        <div class="podium-rank">3</div>
                        <div class="podium-name"><?= sanitize($topUsers[2]['ho_ten']) ?></div>
                        <div class="podium-points"><?= formatNumber($topUsers[2]['tong_diem']) ?></div>
                        <div class="podium-stats">
                            <span><i class="fas fa-book"></i> <?= $topUsers[2]['lessons'] ?></span>
                            <span><i class="fas fa-medal"></i> <?= $topUsers[2]['badges'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Full Leaderboard -->
            <div class="leaderboard-card">
                <div class="leaderboard-header">
                    <h2 class="leaderboard-title">
                        <i class="fas fa-list-ol"></i>
                        <?= __('leaderboard') ?>
                    </h2>
                    <span style="color: #64748b; font-size: 0.875rem;"><?= count($topUsers) ?> <?= __('user') ?></span>
                </div>
                
                <div class="leaderboard-list">
                    <?php foreach ($topUsers as $rank => $user): 
                        $isCurrentUser = isLoggedIn() && $user['ma_nguoi_dung'] == $_SESSION['user_id'];
                    ?>
                    <div class="leaderboard-item <?= $isCurrentUser ? 'current-user' : '' ?>">
                        <div class="item-rank <?= $rank < 3 ? 'top' : '' ?>">
                            <?php if ($rank < 3): ?>
                            <?= ['🥇', '🥈', '🥉'][$rank] ?>
                            <?php else: ?>
                            <?= $rank + 1 ?>
                            <?php endif; ?>
                        </div>
                        
                        <img src="<?= !empty($user['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $user['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                             alt="" class="item-avatar">
                        
                        <div class="item-info">
                            <div class="item-name">
                                <?= sanitize($user['ho_ten']) ?>
                                <?php if ($isCurrentUser): ?>
                                <span class="you-badge"><?= __('you') ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="item-stats">
                                <span><i class="fas fa-graduation-cap"></i> <?= $user['lessons'] ?> <?= __('lessons') ?></span>
                                <span><i class="fas fa-medal"></i> <?= $user['badges'] ?> <?= __('badges') ?></span>
                            </div>
                        </div>
                        
                        <div class="item-points"><?= formatNumber($user['tong_diem']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- How to Earn Points -->
            <div class="earn-section">
                <h3 class="earn-title">
                    <div class="earn-title-icon">💡</div>
                    Cách Kiếm Điểm
                </h3>
                <div class="earn-grid">
                    <div class="earn-item green">
                        <div class="earn-icon green"><i class="fas fa-graduation-cap"></i></div>
                        <div class="earn-info">
                            <div class="earn-name">Hoàn thành bài học</div>
                            <div class="earn-points">+20 điểm</div>
                        </div>
                    </div>
                    <div class="earn-item blue">
                        <div class="earn-icon blue"><i class="fas fa-book-open"></i></div>
                        <div class="earn-info">
                            <div class="earn-name">Đọc truyện</div>
                            <div class="earn-points">+10 điểm</div>
                        </div>
                    </div>
                    <div class="earn-item red">
                        <div class="earn-icon red"><i class="fas fa-question-circle"></i></div>
                        <div class="earn-info">
                            <div class="earn-name">Làm Quiz</div>
                            <div class="earn-points">+50 điểm</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
