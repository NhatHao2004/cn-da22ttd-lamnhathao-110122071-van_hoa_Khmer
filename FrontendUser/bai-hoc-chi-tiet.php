<?php
/**
 * Chi tiết bài học - Unified Design
 */
require_once __DIR__ . '/includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect(BASE_URL . '/danh-sach-bai-hoc.php');
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM bai_hoc WHERE ma_bai_hoc = ? AND trang_thai IN ('hien_thi', 'xuat_ban')");
$stmt->execute([$id]);
$lesson = $stmt->fetch();

if (!$lesson) redirect(BASE_URL . '/danh-sach-bai-hoc.php', 'Bài học không tồn tại.', 'warning');

$pageTitle = $lesson['tieu_de'];

// Get vocabulary (if table exists)
$vocabulary = [];
try {
    $vocabStmt = $pdo->prepare("SELECT * FROM tu_vung WHERE ma_bai_hoc = ? ORDER BY thu_tu ASC");
    $vocabStmt->execute([$id]);
    $vocabulary = $vocabStmt->fetchAll();
} catch (PDOException $e) {
    // Table doesn't exist or query failed
    error_log("Vocabulary query failed: " . $e->getMessage());
    $vocabulary = [];
}

// Get user progress
$userProgress = null;
if (isLoggedIn()) {
    try {
        $progressStmt = $pdo->prepare("SELECT * FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND ma_bai_hoc = ?");
        $progressStmt->execute([$_SESSION['user_id'], $id]);
        $userProgress = $progressStmt->fetch();
    } catch (PDOException $e) {
        // Table doesn't exist
        error_log("Progress table error: " . $e->getMessage());
        $userProgress = null;
    }
}

// Handle lesson completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'complete') {
        $points = $lesson['diem_thuong'] ?? 20;
        
        // DEBUG: Log thông tin
        error_log("=== BẮT ĐẦU HOÀN THÀNH BÀI HỌC ===");
        error_log("User ID: " . $_SESSION['user_id']);
        error_log("Bài học ID: " . $id);
        error_log("Điểm: " . $points);
        
        try {
            $pdo->beginTransaction();
            
            // Kiểm tra lại tiến trình (tránh race condition)
            $checkStmt = $pdo->prepare("SELECT * FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND ma_bai_hoc = ?");
            $checkStmt->execute([$_SESSION['user_id'], $id]);
            $currentProgress = $checkStmt->fetch();
            
            error_log("Tiến trình hiện tại: " . json_encode($currentProgress));
            
            if ($currentProgress && $currentProgress['trang_thai'] === 'hoan_thanh') {
                // Đã hoàn thành rồi
                error_log("→ ĐÃ HOÀN THÀNH RỒI");
                $pdo->rollBack();
                redirect(BASE_URL . "/bai-hoc-chi-tiet.php?id=$id", "Bạn đã hoàn thành bài học này rồi!", 'info');
            } elseif ($currentProgress) {
                // Có tiến trình nhưng chưa hoàn thành -> UPDATE
                error_log("→ UPDATE tiến trình");
                $stmt = $pdo->prepare("UPDATE tien_trinh_hoc_tap SET trang_thai = 'hoan_thanh', tien_do = 100, diem = ?, ngay_hoan_thanh = NOW(), ngay_cap_nhat = NOW() WHERE ma_tien_trinh = ?");
                $stmt->execute([$points, $currentProgress['ma_tien_trinh']]);
                error_log("UPDATE rows: " . $stmt->rowCount());
                
                // Cộng điểm cho user
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?");
                $stmt->execute([$points, $_SESSION['user_id']]);
                error_log("Cộng điểm rows: " . $stmt->rowCount());
                
                $pdo->commit();
                error_log("✓ COMMIT thành công");
                
                // Kiểm tra và trao huy hiệu tự động
                $newBadges = checkBadges($_SESSION['user_id']);
                
                $message = "🎉 Chúc mừng! Bạn đã nhận được $points điểm!";
                if (!empty($newBadges)) {
                    $message .= " Và đạt được huy hiệu: " . implode(', ', $newBadges) . "!";
                }
                
                redirect(BASE_URL . "/bai-hoc-chi-tiet.php?id=$id", $message, 'success');
            } else {
                // Chưa có tiến trình -> INSERT
                error_log("→ INSERT tiến trình mới");
                $stmt = $pdo->prepare("INSERT INTO tien_trinh_hoc_tap (ma_nguoi_dung, ma_bai_hoc, trang_thai, tien_do, diem, ngay_bat_dau, ngay_hoan_thanh, ngay_cap_nhat) VALUES (?, ?, 'hoan_thanh', 100, ?, NOW(), NOW(), NOW())");
                $stmt->execute([$_SESSION['user_id'], $id, $points]);
                error_log("INSERT ID: " . $pdo->lastInsertId());
                
                // Cộng điểm cho user
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?");
                $stmt->execute([$points, $_SESSION['user_id']]);
                error_log("Cộng điểm rows: " . $stmt->rowCount());
                
                $pdo->commit();
                error_log("✓ COMMIT thành công");
                
                // Kiểm tra và trao huy hiệu tự động
                $newBadges = checkBadges($_SESSION['user_id']);
                
                $message = "🎉 Chúc mừng! Bạn đã nhận được $points điểm!";
                if (!empty($newBadges)) {
                    $message .= " Và đạt được huy hiệu: " . implode(', ', $newBadges) . "!";
                }
                
                redirect(BASE_URL . "/bai-hoc-chi-tiet.php?id=$id", $message, 'success');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = $e->getMessage();
            error_log("✗ LỖI: " . $errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Hiển thị lỗi chi tiết cho user (chỉ khi debug)
            redirect(BASE_URL . "/bai-hoc-chi-tiet.php?id=$id", "Có lỗi xảy ra: " . $errorMsg, 'error');
        }
    }
}

// Get next/prev lessons - Navigate through all lessons regardless of level
$nextStmt = $pdo->prepare("SELECT ma_bai_hoc, tieu_de FROM bai_hoc WHERE ma_bai_hoc > ? AND trang_thai IN ('hien_thi', 'xuat_ban') ORDER BY ma_bai_hoc ASC LIMIT 1");
$nextStmt->execute([$id]);
$nextLesson = $nextStmt->fetch();

$prevStmt = $pdo->prepare("SELECT ma_bai_hoc, tieu_de FROM bai_hoc WHERE ma_bai_hoc < ? AND trang_thai IN ('hien_thi', 'xuat_ban') ORDER BY ma_bai_hoc DESC LIMIT 1");
$prevStmt->execute([$id]);
$prevLesson = $prevStmt->fetch();
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Modern Lesson Detail Page ===== */
body {
    background: linear-gradient(135deg, #E6F4F1 0%, #D4EDE7 50%, #C2E6DD 100%);
}

.lesson-detail-page {
    padding-top: 100px;
    min-height: 100vh;
}

/* Breadcrumb */
.lesson-breadcrumb {
    background: rgba(255, 255, 255, 0.95);
    padding: 1rem 0;
    border-bottom: 3px solid #1a1a1a;
    margin-bottom: 2rem;
    position: sticky;
    top: 80px;
    z-index: 999;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #1a1a1a;
}

.breadcrumb-nav a {
    color: #00b894;
    text-decoration: none;
    transition: color 0.3s;
    font-weight: 700;
}

.breadcrumb-nav a:hover {
    color: #00a383;
}

.breadcrumb-nav span {
    color: #000000;
    font-weight: 700;
}

/* Main Layout */
.lesson-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 2rem 4rem; /* Removed top padding since page has padding-top */
}

.lesson-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
}

/* Left Column - Main Content */
.lesson-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Video Section */
.lesson-video-card {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    background: linear-gradient(135deg, #00b894, #00cec9);
    overflow: hidden;
}

.video-wrapper iframe,
.video-wrapper video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.video-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
}

.video-placeholder i {
    margin-bottom: 1rem;
    opacity: 0.3;
}

.video-placeholder p {
    font-size: 1rem;
    opacity: 0.7;
}

/* Lesson Header */
.lesson-header-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.lesson-title {
    font-size: 2rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 1rem;
    line-height: 1.3;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
}

.lesson-meta-row {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.lesson-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 800;
    border: 3px solid #1a1a1a;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.lesson-badge.level {
    background: #ffffff;
    color: #00b894;
    border-color: #1a1a1a;
}

.lesson-badge.duration {
    background: #ffffff;
    color: #f59e0b;
    border-color: #1a1a1a;
}

.lesson-badge.points {
    background: #ffffff;
    color: #00b894;
    border-color: #1a1a1a;
}

.lesson-badge.completed {
    background: linear-gradient(135deg, #00b894, #00cec9);
    color: white;
    border-color: #1a1a1a;
}

.lesson-badge.in-progress {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    border-color: #1a1a1a;
}

/* Content Card */
.lesson-content-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.content-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #00b894;
    background: linear-gradient(135deg, #E6F4F1 0%, #D4EDE7 100%);
    padding: 1rem;
    border-radius: 12px;
    margin: -1rem -1rem 1.5rem -1rem;
}

.content-header i {
    font-size: 1.5rem;
    color: #00b894;
}

.content-header h3 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #1a1a1a;
    margin: 0;
}

.lesson-content {
    font-size: 1rem;
    line-height: 1.8;
    color: #2d3436;
    font-weight: 600;
}

.lesson-content p {
    margin-bottom: 1rem;
}

.lesson-content h1,
.lesson-content h2,
.lesson-content h3 {
    color: #1a1a1a;
    font-weight: 900;
    margin: 1.5rem 0 1rem;
}

.lesson-content ul,
.lesson-content ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.lesson-content li {
    margin-bottom: 0.5rem;
}

/* Right Sidebar */
.lesson-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Progress Card */
.progress-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.progress-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.progress-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #00b894, #00cec9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    border: 3px solid #1a1a1a;
}

.progress-title {
    font-size: 1.125rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.progress-desc {
    font-size: 0.875rem;
    color: #2d3436;
    font-weight: 600;
}

.complete-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #00b894, #00a383);
    color: white;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
    margin-bottom: 1rem;
}

.complete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 184, 148, 0.4);
}

.complete-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
}

.share-btn {
    width: 100%;
    padding: 1rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.share-btn:hover {
    background: #E6F4F1;
    color: #00b894;
    border-color: #1a1a1a;
}

.login-prompt {
    text-align: center;
    padding: 2rem;
    background: #E6F4F1;
    border-radius: 12px;
    border: 3px solid #00b894;
}

.login-prompt i {
    font-size: 3rem;
    color: #00b894;
    margin-bottom: 1rem;
}

.login-prompt p {
    color: #2d3436;
    margin-bottom: 1rem;
    font-weight: 600;
}

.login-prompt a {
    color: #00b894;
    font-weight: 800;
    text-decoration: none;
}

/* Navigation Card */
.lesson-nav-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 4px solid #1a1a1a;
}

.nav-title {
    font-size: 1rem;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    text-decoration: none;
    color: #1a1a1a;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s;
}

.nav-link:hover {
    background: #00b894;
    color: white;
    border-color: #1a1a1a;
    transform: translateX(5px);
}

.nav-link i {
    font-size: 1rem;
}

.nav-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Vocabulary Section */
.vocab-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.vocab-card {
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.vocab-card:hover {
    border-color: #00b894;
    background: #E6F4F1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 184, 148, 0.2);
}

.vocab-khmer {
    font-size: 1.5rem;
    font-weight: 800;
    color: #00b894;
    font-family: 'Battambang', cursive;
    margin-bottom: 0.5rem;
}

.vocab-vietnamese {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.25rem;
}

.vocab-pronunciation {
    font-size: 0.875rem;
    color: #2d3436;
    font-style: italic;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 1024px) {
    .lesson-grid {
        grid-template-columns: 1fr;
    }
    
    .progress-card {
        position: static;
    }
}

@media (max-width: 768px) {
    .lesson-container {
        padding: 0 1rem 2rem;
    }
    
    .lesson-title {
        font-size: 1.5rem;
    }
    
    .lesson-header-card,
    .lesson-content-card,
    .lesson-video-card {
        padding: 1.5rem;
        border-radius: 16px;
    }
    
    .vocab-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="lesson-detail-page">
    <!-- Breadcrumb -->
    <section class="lesson-breadcrumb">
        <div class="container">
            <nav class="breadcrumb-nav">
                <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i></a>
                <span>/</span>
                <a href="<?= BASE_URL ?>/danh-sach-bai-hoc.php">Bài học</a>
                <span>/</span>
                <span><?= htmlspecialchars($lesson['tieu_de']) ?></span>
            </nav>
        </div>
    </section>

    <!-- Main Content -->
    <div class="lesson-container">
        <div class="lesson-grid">
            <!-- Left Column - Main Content -->
            <div class="lesson-main">
                <!-- Video Section -->
                <div class="lesson-video-card">
                    <div class="video-wrapper">
                        <?php if (!empty($lesson['video_url'])): ?>
                            <?php if (strpos($lesson['video_url'], 'youtube.com') !== false || strpos($lesson['video_url'], 'youtu.be') !== false): ?>
                                <?php
                                // Extract YouTube video ID
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $lesson['video_url'], $matches);
                                $videoId = $matches[1] ?? '';
                                ?>
                                <?php if ($videoId): ?>
                                <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" allowfullscreen></iframe>
                                <?php endif; ?>
                            <?php else: ?>
                                <video controls>
                                    <source src="<?= $lesson['video_url'] ?>" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="video-placeholder">
                                <i class="fas fa-play-circle"></i>
                                <p>Chưa có video bài học</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lesson Header -->
                <div class="lesson-header-card">
                    <h1 class="lesson-title"><?= htmlspecialchars($lesson['tieu_de']) ?></h1>
                    <div class="lesson-meta-row">
                        <span class="lesson-badge level">
                            <i class="fas fa-layer-group"></i>
                            <?php
                            $levelNames = ['co_ban' => 'Cơ bản', 'trung_cap' => 'Trung cấp', 'nang_cao' => 'Nâng cao'];
                            echo $levelNames[$lesson['cap_do']] ?? ucfirst($lesson['cap_do']);
                            ?>
                        </span>
                        <span class="lesson-badge duration">
                            <i class="fas fa-clock"></i>
                            <?= $lesson['thoi_luong'] ?? 30 ?> phút
                        </span>
                        <span class="lesson-badge points">
                            <i class="fas fa-star"></i>
                            +<?= $lesson['diem_thuong'] ?? 10 ?> điểm
                        </span>
                        <?php if ($userProgress && $userProgress['trang_thai'] === 'hoan_thanh'): ?>
                        <span class="lesson-badge completed">
                            <i class="fas fa-check-circle"></i>
                            Đã hoàn thành
                        </span>
                        <?php elseif ($userProgress): ?>
                        <span class="lesson-badge in-progress">
                            <i class="fas fa-spinner fa-spin"></i>
                            Đang học
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lesson Content -->
                <div class="lesson-content-card">
                    <div class="content-header">
                        <h3>Nội dung bài học</h3>
                    </div>
                    <div class="lesson-content">
                        <?= $lesson['noi_dung'] ?>
                    </div>
                </div>

                <!-- Vocabulary -->
                <?php if (!empty($vocabulary)): ?>
                <div class="lesson-content-card">
                    <div class="content-header">
                        <i class="fas fa-language"></i>
                        <h3>Từ vựng (<?= count($vocabulary) ?>)</h3>
                    </div>
                    <div class="vocab-grid">
                        <?php foreach ($vocabulary as $word): ?>
                        <div class="vocab-card">
                            <div class="vocab-khmer"><?= $word['tu_khmer'] ?></div>
                            <div class="vocab-vietnamese"><?= $word['nghia_tieng_viet'] ?></div>
                            <?php if (!empty($word['phien_am'])): ?>
                            <div class="vocab-pronunciation"><?= $word['phien_am'] ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar -->
            <div class="lesson-sidebar">
                <!-- Progress Card -->
                <div class="progress-card">
                    <?php if (isLoggedIn()): ?>
                        <div class="progress-header">
                            <div class="progress-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="progress-title">Tiến độ học tập</h3>
                            <p class="progress-desc">Hoàn thành bài học để nhận điểm thưởng</p>
                        </div>
                        
                        <?php if (!$userProgress || $userProgress['trang_thai'] !== 'hoan_thanh'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" class="complete-btn">
                                <i class="fas fa-check-circle"></i>
                                Hoàn thành (+<?= $lesson['diem_thuong'] ?? 20 ?> điểm)
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="complete-btn" disabled>
                            <i class="fas fa-check-circle"></i>
                            Đã hoàn thành
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="shareLesson()" class="share-btn">
                            <i class="fas fa-share-alt"></i>
                            Chia sẻ bài học
                        </button>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-user-circle"></i>
                            <p>Vui lòng <a href="<?= BASE_URL ?>/login.php">đăng nhập</a> để hoàn thành bài học và nhận điểm thưởng</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Navigation Card -->
                <div class="lesson-nav-card">
                    <div class="nav-title">
                        <i class="fas fa-compass"></i>
                        Điều hướng
                    </div>
                    <div class="nav-links">
                        <?php if ($prevLesson): ?>
                        <a href="<?= BASE_URL ?>/bai-hoc-chi-tiet.php?id=<?= $prevLesson['ma_bai_hoc'] ?>" class="nav-link">
                            <i class="fas fa-arrow-left"></i>
                            <span>Bài trước</span>
                        </a>
                        <?php else: ?>
                        <div class="nav-link disabled">
                            <i class="fas fa-arrow-left"></i>
                            <span>Không có bài trước</span>
                        </div>
                        <?php endif; ?>
                        
                        <a href="<?= BASE_URL ?>/danh-sach-bai-hoc.php" class="nav-link">
                            <i class="fas fa-list"></i>
                            <span>Danh sách bài học</span>
                        </a>
                        
                        <?php if ($nextLesson): ?>
                        <a href="<?= BASE_URL ?>/bai-hoc-chi-tiet.php?id=<?= $nextLesson['ma_bai_hoc'] ?>" class="nav-link">
                            <span>Bài tiếp theo</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php else: ?>
                        <div class="nav-link disabled">
                            <span>Không có bài tiếp</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
function shareLesson() {
    if (navigator.share) {
        navigator.share({
            title: '<?= htmlspecialchars($lesson['tieu_de']) ?>',
            text: 'Học tiếng Khmer cùng tôi!',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Đã sao chép link bài học!');
        });
    }
}
</script>
