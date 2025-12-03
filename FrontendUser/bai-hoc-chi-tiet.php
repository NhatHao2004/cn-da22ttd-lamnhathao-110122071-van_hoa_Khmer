<?php
/**
 * Chi tiết Bài học tiếng Khmer - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id) {
    header('Location: hoc-tieng-khmer.php');
    exit;
}

// Lấy thông tin bài học
$baiHoc = $db->querySingle(
    "SELECT * FROM bai_hoc WHERE id = ? AND trang_thai = 'xuat_ban'",
    [$id]
);

if(!$baiHoc) {
    header('Location: hoc-tieng-khmer.php');
    exit;
}

// Tăng lượt học
$db->execute("UPDATE bai_hoc SET luot_hoc = luot_hoc + 1 WHERE id = ?", [$id]);

// Lấy từ vựng
$tuVung = $db->query(
    "SELECT * FROM tu_vung WHERE bai_hoc_id = ? ORDER BY thu_tu ASC",
    [$id]
);

// Lấy bài học tiếp theo
$nextLesson = $db->querySingle(
    "SELECT id, tieu_de FROM bai_hoc 
     WHERE trang_thai = 'xuat_ban' AND cap_do = ? AND thu_tu > ? 
     ORDER BY thu_tu ASC 
     LIMIT 1",
    [$baiHoc['cap_do'], $baiHoc['thu_tu']]
);

// Lấy bài học trước
$prevLesson = $db->querySingle(
    "SELECT id, tieu_de FROM bai_hoc 
     WHERE trang_thai = 'xuat_ban' AND cap_do = ? AND thu_tu < ? 
     ORDER BY thu_tu DESC 
     LIMIT 1",
    [$baiHoc['cap_do'], $baiHoc['thu_tu']]
);

$pageTitle = $baiHoc['tieu_de'];
include 'includes/header.php';

$capDoColors = [
    'co_ban' => 'var(--success)',
    'trung_cap' => 'var(--warning)',
    'nang_cao' => 'var(--danger)'
];
$capDoLabels = [
    'co_ban' => 'Cơ bản',
    'trung_cap' => 'Trung cấp',
    'nang_cao' => 'Nâng cao'
];
?>

<article class="lesson-detail">
    <!-- Lesson Header -->
    <section class="lesson-header">
        <div class="container">
            <div class="lesson-breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="hoc-tieng-khmer.php">Học tiếng Khmer</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($baiHoc['tieu_de']); ?></span>
            </div>
            
            <div class="lesson-header-content">
                <span class="lesson-level" style="background: <?php echo $capDoColors[$baiHoc['cap_do']]; ?>;">
                    <?php echo $capDoLabels[$baiHoc['cap_do']]; ?>
                </span>
                
                <?php if($baiHoc['thu_tu']): ?>
                <div class="lesson-number-big">Bài <?php echo $baiHoc['thu_tu']; ?></div>
                <?php endif; ?>
                
                <h1 class="lesson-title"><?php echo htmlspecialchars($baiHoc['tieu_de']); ?></h1>
                
                <div class="lesson-quick-info">
                    <div class="quick-info-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $baiHoc['thoi_luong']; ?> phút</span>
                    </div>
                    <div class="quick-info-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo number_format($baiHoc['luot_hoc']); ?> học viên</span>
                    </div>
                    <?php if($baiHoc['so_tu_vung']): ?>
                    <div class="quick-info-item">
                        <i class="fas fa-book"></i>
                        <span><?php echo $baiHoc['so_tu_vung']; ?> từ vựng</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Lesson Content -->
    <section class="lesson-content-section">
        <div class="container">
            <div class="lesson-layout">
                <!-- Main Content -->
                <div class="lesson-main">
                    <!-- Description -->
                    <?php if($baiHoc['mo_ta']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-info-circle"></i> Giới thiệu</h2>
                        <p class="lesson-intro">
                            <?php echo nl2br(htmlspecialchars($baiHoc['mo_ta'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Video/Audio -->
                    <?php if($baiHoc['video']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-video"></i> Video bài học</h2>
                        <div class="video-wrapper">
                            <iframe 
                                src="<?php echo htmlspecialchars($baiHoc['video']); ?>" 
                                frameborder="0" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($baiHoc['audio']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-volume-up"></i> Phát âm</h2>
                        <div class="audio-player">
                            <audio controls>
                                <source src="<?php echo htmlspecialchars($baiHoc['audio']); ?>" type="audio/mpeg">
                                Trình duyệt của bạn không hỗ trợ audio.
                            </audio>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Content -->
                    <?php if($baiHoc['noi_dung']): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-book-open"></i> Nội dung bài học</h2>
                        <div class="lesson-content">
                            <?php echo $baiHoc['noi_dung']; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Vocabulary -->
                    <?php if($tuVung && count($tuVung) > 0): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-language"></i> Từ vựng (<?php echo count($tuVung); ?> từ)</h2>
                        <div class="vocabulary-grid">
                            <?php foreach($tuVung as $tu): ?>
                            <div class="vocabulary-card">
                                <div class="vocab-main">
                                    <div class="vocab-khmer"><?php echo htmlspecialchars($tu['tu_khmer']); ?></div>
                                    <div class="vocab-viet"><?php echo htmlspecialchars($tu['nghia_tieng_viet']); ?></div>
                                </div>
                                <?php if($tu['phien_am']): ?>
                                <div class="vocab-pronunciation">
                                    <i class="fas fa-volume-up"></i>
                                    <?php echo htmlspecialchars($tu['phien_am']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if($tu['vi_du']): ?>
                                <div class="vocab-example">
                                    <strong>Ví dụ:</strong> <?php echo htmlspecialchars($tu['vi_du']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if($tu['audio']): ?>
                                <button class="btn-play-audio" onclick="playAudio('<?php echo htmlspecialchars($tu['audio']); ?>')">
                                    <i class="fas fa-play"></i> Nghe phát âm
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Practice Exercise -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="content-card">
                        <h2><i class="fas fa-pen"></i> Bài tập thực hành</h2>
                        <div class="practice-section">
                            <p style="color: var(--gray-600); margin-bottom: 20px;">
                                Hoàn thành bài tập để củng cố kiến thức
                            </p>
                            <button class="btn btn-primary" onclick="startPractice()">
                                <i class="fas fa-play"></i> Bắt đầu làm bài
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Navigation -->
                    <div class="lesson-navigation">
                        <?php if($prevLesson): ?>
                        <a href="bai-hoc-chi-tiet.php?id=<?php echo $prevLesson['id']; ?>" class="nav-btn prev-btn">
                            <i class="fas fa-arrow-left"></i>
                            <div class="nav-btn-content">
                                <span class="nav-label">Bài trước</span>
                                <span class="nav-title"><?php echo htmlspecialchars($prevLesson['tieu_de']); ?></span>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <?php if($nextLesson): ?>
                        <a href="bai-hoc-chi-tiet.php?id=<?php echo $nextLesson['id']; ?>" class="nav-btn next-btn">
                            <div class="nav-btn-content">
                                <span class="nav-label">Bài tiếp theo</span>
                                <span class="nav-title"><?php echo htmlspecialchars($nextLesson['tieu_de']); ?></span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <aside class="lesson-sidebar">
                    <!-- Progress -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="sidebar-card">
                        <h3><i class="fas fa-chart-line"></i> Tiến trình</h3>
                        <div class="progress-circle">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="var(--gray-200)" stroke-width="8"/>
                                <circle cx="50" cy="50" r="45" fill="none" stroke="var(--primary)" stroke-width="8" 
                                        stroke-dasharray="282.7" stroke-dashoffset="282.7" class="progress-circle-bar"/>
                            </svg>
                            <div class="progress-circle-text">
                                <span class="progress-percent">0%</span>
                                <span class="progress-label">Hoàn thành</span>
                            </div>
                        </div>
                        <button class="btn btn-success btn-block" onclick="markAsCompleted()">
                            <i class="fas fa-check"></i> Đánh dấu hoàn thành
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-tools"></i> Công cụ</h3>
                        <div class="action-buttons">
                            <button class="action-btn" onclick="bookmarkLesson()">
                                <i class="far fa-bookmark"></i>
                                Lưu bài học
                            </button>
                            <button class="action-btn" onclick="downloadLesson()">
                                <i class="fas fa-download"></i>
                                Tải về
                            </button>
                            <button class="action-btn" onclick="shareLesson()">
                                <i class="fas fa-share-alt"></i>
                                Chia sẻ
                            </button>
                            <button class="action-btn" onclick="printLesson()">
                                <i class="fas fa-print"></i>
                                In bài học
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lesson Info -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-info-circle"></i> Thông tin</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Cấp độ</span>
                                <span class="info-value" style="color: <?php echo $capDoColors[$baiHoc['cap_do']]; ?>;">
                                    <?php echo $capDoLabels[$baiHoc['cap_do']]; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Thời lượng</span>
                                <span class="info-value"><?php echo $baiHoc['thoi_luong']; ?> phút</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số từ vựng</span>
                                <span class="info-value"><?php echo $baiHoc['so_tu_vung'] ?: 0; ?> từ</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Lượt học</span>
                                <span class="info-value"><?php echo number_format($baiHoc['luot_hoc']); ?></span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</article>

<style>
.lesson-header {
    padding: 120px 0 40px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.lesson-number-big {
    font-size: 14px;
    font-weight: 600;
    opacity: 0.9;
    margin: 16px 0 8px;
}

.lesson-intro {
    font-size: 17px;
    line-height: 1.8;
    color: var(--gray-700);
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 12px;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.audio-player {
    padding: 20px;
    background: var(--gray-50);
    border-radius: 12px;
}

.audio-player audio {
    width: 100%;
}

.vocabulary-grid {
    display: grid;
    gap: 16px;
}

.vocabulary-card {
    padding: 20px;
    background: var(--gray-50);
    border-left: 4px solid var(--primary);
    border-radius: 8px;
    transition: var(--transition-base);
}

.vocabulary-card:hover {
    background: white;
    box-shadow: var(--shadow-md);
}

.vocab-main {
    margin-bottom: 12px;
}

.vocab-khmer {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 8px;
}

.vocab-viet {
    font-size: 18px;
    color: var(--gray-800);
}

.vocab-pronunciation {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
    font-style: italic;
    margin-bottom: 8px;
}

.vocab-example {
    font-size: 14px;
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 12px;
}

.btn-play-audio {
    padding: 8px 16px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition-base);
}

.btn-play-audio:hover {
    background: var(--primary-dark);
}

.practice-section {
    text-align: center;
    padding: 40px 20px;
}

.lesson-navigation {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 40px;
}

.nav-btn {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    transition: var(--transition-base);
}

.nav-btn:hover {
    border-color: var(--primary);
    background: var(--gray-50);
}

.nav-btn i {
    font-size: 20px;
    color: var(--primary);
}

.nav-btn-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-label {
    font-size: 12px;
    color: var(--gray-500);
    font-weight: 500;
}

.nav-title {
    font-size: 14px;
    color: var(--gray-900);
    font-weight: 600;
}

.next-btn {
    justify-content: flex-end;
    text-align: right;
}

.progress-circle {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 20px auto;
}

.progress-circle svg {
    transform: rotate(-90deg);
}

.progress-circle-bar {
    transition: stroke-dashoffset 1s ease;
}

.progress-circle-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.progress-percent {
    display: block;
    font-size: 32px;
    font-weight: 700;
    color: var(--primary);
}

.progress-label {
    display: block;
    font-size: 12px;
    color: var(--gray-600);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    color: var(--gray-700);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition-base);
}

.action-btn:hover {
    background: var(--gray-100);
    border-color: var(--primary);
    color: var(--primary);
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--gray-200);
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-size: 13px;
    color: var(--gray-600);
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-900);
}

@media (max-width: 768px) {
    .lesson-navigation {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function playAudio(audioUrl) {
    const audio = new Audio(audioUrl);
    audio.play();
}

function startPractice() {
    alert('Tính năng bài tập đang được phát triển');
}

function markAsCompleted() {
    <?php if(!isset($_SESSION['user_id'])): ?>
    alert('Vui lòng đăng nhập để lưu tiến trình');
    window.location.href = 'login.php';
    <?php else: ?>
    // TODO: Implement mark as completed
    alert('Tính năng đang được phát triển');
    <?php endif; ?>
}

function bookmarkLesson() {
    alert('Tính năng đang được phát triển');
}

function downloadLesson() {
    alert('Tính năng đang được phát triển');
}

function shareLesson() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    } else {
        alert('Trình duyệt không hỗ trợ chia sẻ');
    }
}

function printLesson() {
    window.print();
}
</script>

<?php include 'includes/footer.php'; ?>
