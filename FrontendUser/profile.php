<?php
/**
 * User Profile & Dashboard - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Lấy thông tin user
$user = $db->findById('nguoi_dung', $userId);

if(!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Xử lý cập nhật thông tin
$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $gioi_tinh = $_POST['gioi_tinh'];
    
    // Validate
    if(empty($ho_ten) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        // Check email trùng
        $existingUser = $db->query(
            "SELECT id FROM nguoi_dung WHERE email = ? AND id != ?",
            [$email, $userId]
        );
        
        if($existingUser && count($existingUser) > 0) {
            $error = 'Email đã được sử dụng';
        } else {
            $updateData = [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'ngay_sinh' => $ngay_sinh ?: null,
                'gioi_tinh' => $gioi_tinh
            ];
            
            // Xử lý avatar upload
            if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if(in_array($_FILES['avatar']['type'], $allowedTypes)) {
                    $uploadDir = 'uploads/avatar/';
                    if(!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if(move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                        // Xóa avatar cũ
                        if($user['avatar'] && file_exists($user['avatar'])) {
                            unlink($user['avatar']);
                        }
                        $updateData['avatar'] = $uploadPath;
                    }
                }
            }
            
            if($db->update('nguoi_dung', $updateData, $userId)) {
                $success = 'Cập nhật thông tin thành công!';
                $user = $db->findById('nguoi_dung', $userId); // Refresh data
                $_SESSION['user_name'] = $user['ho_ten'];
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại';
            }
        }
    }
}

// Thống kê học tập
$stats = [
    'bai_hoc_hoan_thanh' => 0,
    'tong_bai_hoc' => $db->count('bai_hoc', "trang_thai = 'hien_thi'"),
    'diem_tich_luy' => $user['diem'] ?? 0,
    'hang_hien_tai' => $user['hang'] ?? 'Người mới',
    'ngay_hoc_lien_tiep' => $user['ngay_hoc_lien_tiep'] ?? 0
];

// Lấy tiến trình học tập (giả lập - cần bảng tien_trinh_hoc_tap)
$progress = $db->query(
    "SELECT bh.*, ttht.trang_thai, ttht.diem, ttht.ngay_hoan_thanh
     FROM bai_hoc bh
     LEFT JOIN tien_trinh_hoc_tap ttht ON bh.id = ttht.bai_hoc_id AND ttht.nguoi_dung_id = ?
     WHERE ttht.nguoi_dung_id = ? AND ttht.trang_thai = 'hoan_thanh'
     ORDER BY ttht.ngay_hoan_thanh DESC
     LIMIT 5",
    [$userId, $userId]
);

if($progress === false) {
    $progress = []; // Nếu bảng chưa tồn tại
} else {
    $stats['bai_hoc_hoan_thanh'] = $db->query(
        "SELECT COUNT(*) as count FROM tien_trinh_hoc_tap WHERE nguoi_dung_id = ? AND trang_thai = 'hoan_thanh'",
        [$userId]
    )[0]['count'] ?? 0;
}

// Huy hiệu (giả lập)
$badges = [
    ['name' => 'Người mới bắt đầu', 'icon' => 'fa-star', 'color' => '#10b981', 'earned' => true, 'description' => 'Hoàn thành bài học đầu tiên'],
    ['name' => 'Siêng năng', 'icon' => 'fa-fire', 'color' => '#f59e0b', 'earned' => $stats['ngay_hoc_lien_tiep'] >= 7, 'description' => 'Học 7 ngày liên tiếp'],
    ['name' => 'Học giỏi', 'icon' => 'fa-trophy', 'color' => '#eab308', 'earned' => $stats['bai_hoc_hoan_thanh'] >= 10, 'description' => 'Hoàn thành 10 bài học'],
    ['name' => 'Chuyên gia', 'icon' => 'fa-crown', 'color' => '#8b5cf6', 'earned' => $stats['bai_hoc_hoan_thanh'] >= 20, 'description' => 'Hoàn thành 20 bài học'],
    ['name' => 'Bậc thầy', 'icon' => 'fa-gem', 'color' => '#3b82f6', 'earned' => $stats['bai_hoc_hoan_thanh'] >= 50, 'description' => 'Hoàn thành 50 bài học'],
    ['name' => 'Truyền nhân', 'icon' => 'fa-dragon', 'color' => '#ef4444', 'earned' => $stats['bai_hoc_hoan_thanh'] >= 100, 'description' => 'Hoàn thành 100 bài học']
];

// Bookmark (giả lập - cần bảng bookmark)
$bookmarks = $db->query(
    "SELECT td.*, b.ngay_tao as bookmark_date
     FROM bookmark b
     INNER JOIN truyen_dan_gian td ON b.truyen_id = td.id
     WHERE b.nguoi_dung_id = ?
     ORDER BY b.ngay_tao DESC
     LIMIT 6",
    [$userId]
);

if($bookmarks === false) {
    $bookmarks = [];
}

// Tính phần trăm hoàn thành
$completionPercent = $stats['tong_bai_hoc'] > 0 
    ? round(($stats['bai_hoc_hoan_thanh'] / $stats['tong_bai_hoc']) * 100) 
    : 0;

$pageTitle = 'Trang cá nhân - ' . $user['ho_ten'];
include 'includes/header.php';
?>

<div class="profile-page">
    <!-- Header -->
    <section class="profile-header">
        <div class="container">
            <div class="profile-header-content">
                <div class="profile-avatar-section">
                    <div class="profile-avatar-wrapper">
                        <img src="<?php echo $user['avatar'] ?: 'assets/images/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($user['ho_ten']); ?>"
                             class="profile-avatar"
                             id="avatarPreview">
                        <button class="avatar-upload-btn" onclick="document.getElementById('avatarInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="profile-header-info">
                        <h1><?php echo htmlspecialchars($user['ho_ten']); ?></h1>
                        <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="profile-rank">
                            <i class="fas fa-medal"></i>
                            Hạng: <strong><?php echo htmlspecialchars($stats['hang_hien_tai']); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="profile-stats-quick">
                    <div class="stat-quick-item">
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong><?php echo $stats['ngay_hoc_lien_tiep']; ?></strong>
                            <span>Ngày liên tiếp</span>
                        </div>
                    </div>
                    <div class="stat-quick-item">
                        <i class="fas fa-star"></i>
                        <div>
                            <strong><?php echo number_format($stats['diem_tich_luy']); ?></strong>
                            <span>Điểm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="profile-content">
        <div class="container">
            <div class="profile-tabs">
                <button class="profile-tab active" data-tab="dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </button>
                <button class="profile-tab" data-tab="info">
                    <i class="fas fa-user"></i> Thông tin cá nhân
                </button>
                <button class="profile-tab" data-tab="badges">
                    <i class="fas fa-trophy"></i> Huy hiệu
                </button>
                <button class="profile-tab" data-tab="bookmarks">
                    <i class="fas fa-bookmark"></i> Đã lưu
                </button>
            </div>
            
            <!-- Tab: Dashboard -->
            <div class="tab-content active" id="tab-dashboard">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['bai_hoc_hoan_thanh']; ?> / <?php echo $stats['tong_bai_hoc']; ?></h3>
                            <p>Bài học hoàn thành</p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: <?php echo $completionPercent; ?>%"></div>
                            </div>
                            <span class="stat-percent"><?php echo $completionPercent; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['diem_tich_luy']); ?></h3>
                            <p>Điểm tích lũy</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #14b8a6);">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['ngay_hoc_lien_tiep']; ?></h3>
                            <p>Ngày học liên tiếp</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo htmlspecialchars($stats['hang_hien_tai']); ?></h3>
                            <p>Hạng hiện tại</p>
                        </div>
                    </div>
                </div>
                
                <!-- Learning Progress Chart -->
                <div class="dashboard-section">
                    <h2 class="section-title"><i class="fas fa-chart-bar"></i> Tiến trình học tập</h2>
                    <div class="progress-chart-card">
                        <div class="progress-circle-wrapper">
                            <svg class="progress-circle" viewBox="0 0 200 200">
                                <circle cx="100" cy="100" r="90" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                                <circle cx="100" cy="100" r="90" fill="none" stroke="url(#gradient)" stroke-width="12"
                                        stroke-dasharray="<?php echo $completionPercent * 5.65; ?> 565"
                                        stroke-linecap="round" 
                                        transform="rotate(-90 100 100)"/>
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#6366f1"/>
                                        <stop offset="100%" style="stop-color:#8b5cf6"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="progress-circle-text">
                                <span class="progress-percent"><?php echo $completionPercent; ?>%</span>
                                <span class="progress-label">Hoàn thành</span>
                            </div>
                        </div>
                        
                        <div class="progress-details">
                            <h3>Thống kê chi tiết</h3>
                            <div class="progress-detail-item">
                                <span>Tổng số bài học</span>
                                <strong><?php echo $stats['tong_bai_hoc']; ?> bài</strong>
                            </div>
                            <div class="progress-detail-item">
                                <span>Đã hoàn thành</span>
                                <strong class="text-success"><?php echo $stats['bai_hoc_hoan_thanh']; ?> bài</strong>
                            </div>
                            <div class="progress-detail-item">
                                <span>Còn lại</span>
                                <strong class="text-primary"><?php echo $stats['tong_bai_hoc'] - $stats['bai_hoc_hoan_thanh']; ?> bài</strong>
                            </div>
                            <a href="hoc-tieng-khmer.php" class="btn btn-primary" style="margin-top: 20px;">
                                <i class="fas fa-book-reader"></i> Tiếp tục học
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <?php if($progress && count($progress) > 0): ?>
                <div class="dashboard-section">
                    <h2 class="section-title"><i class="fas fa-history"></i> Hoạt động gần đây</h2>
                    <div class="activity-list">
                        <?php foreach($progress as $item): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($item['tieu_de']); ?></h4>
                                <p>
                                    Hoàn thành bài học • 
                                    Đạt <?php echo $item['diem'] ?? 0; ?> điểm • 
                                    <?php echo date('d/m/Y H:i', strtotime($item['ngay_hoan_thanh'])); ?>
                                </p>
                            </div>
                            <a href="bai-hoc-chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                Xem lại
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab: Personal Info -->
            <div class="tab-content" id="tab-info">
                <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <div class="info-card">
                    <h2 class="section-title"><i class="fas fa-user-edit"></i> Chỉnh sửa thông tin</h2>
                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Họ và tên <span class="required">*</span></label>
                                <input type="text" name="ho_ten" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="tel" name="so_dien_thoai" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Ngày sinh</label>
                                <input type="date" name="ngay_sinh" class="form-control" 
                                       value="<?php echo $user['ngay_sinh'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Giới tính</label>
                                <select name="gioi_tinh" class="form-control">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" <?php echo ($user['gioi_tinh'] ?? '') === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo ($user['gioi_tinh'] ?? '') === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo ($user['gioi_tinh'] ?? '') === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="change-password.php" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tab: Badges -->
            <div class="tab-content" id="tab-badges">
                <div class="badges-section">
                    <h2 class="section-title"><i class="fas fa-trophy"></i> Huy hiệu của bạn</h2>
                    <div class="badges-grid">
                        <?php foreach($badges as $badge): ?>
                        <div class="badge-card <?php echo $badge['earned'] ? 'earned' : 'locked'; ?>">
                            <div class="badge-icon" style="background: <?php echo $badge['earned'] ? $badge['color'] : '#d1d5db'; ?>;">
                                <i class="fas <?php echo $badge['icon']; ?>"></i>
                            </div>
                            <h4><?php echo $badge['name']; ?></h4>
                            <p><?php echo $badge['description']; ?></p>
                            <?php if($badge['earned']): ?>
                            <span class="badge-status earned"><i class="fas fa-check"></i> Đã đạt được</span>
                            <?php else: ?>
                            <span class="badge-status locked"><i class="fas fa-lock"></i> Chưa mở khóa</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Bookmarks -->
            <div class="tab-content" id="tab-bookmarks">
                <div class="bookmarks-section">
                    <h2 class="section-title"><i class="fas fa-bookmark"></i> Truyện đã lưu</h2>
                    <?php if($bookmarks && count($bookmarks) > 0): ?>
                    <div class="bookmarks-grid">
                        <?php foreach($bookmarks as $story): ?>
                        <article class="bookmark-card">
                            <a href="truyen-chi-tiet.php?id=<?php echo $story['id']; ?>" class="bookmark-image">
                                <img src="<?php echo $story['hinh_anh'] ?: 'assets/images/placeholder-story.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($story['tieu_de']); ?>">
                            </a>
                            <div class="bookmark-body">
                                <h4>
                                    <a href="truyen-chi-tiet.php?id=<?php echo $story['id']; ?>">
                                        <?php echo htmlspecialchars($story['tieu_de']); ?>
                                    </a>
                                </h4>
                                <?php if($story['tac_gia']): ?>
                                <p class="bookmark-author"><?php echo htmlspecialchars($story['tac_gia']); ?></p>
                                <?php endif; ?>
                                <p class="bookmark-date">
                                    <i class="fas fa-bookmark"></i>
                                    Lưu ngày <?php echo date('d/m/Y', strtotime($story['bookmark_date'])); ?>
                                </p>
                                <div class="bookmark-actions">
                                    <a href="truyen-chi-tiet.php?id=<?php echo $story['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-book-reader"></i> Đọc
                                    </a>
                                    <button class="btn btn-outline btn-sm" onclick="removeBookmark(<?php echo $story['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bookmark"></i>
                        <h3>Chưa có truyện đã lưu</h3>
                        <p>Hãy khám phá và lưu những truyện dân gian yêu thích của bạn</p>
                        <a href="truyen-dan-gian.php" class="btn btn-primary">
                            <i class="fas fa-book"></i> Khám phá truyện
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.profile-page {
    min-height: 100vh;
    background: var(--gray-50);
}

.profile-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 40px 0;
}

.profile-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 24px;
}

.profile-avatar-wrapper {
    position: relative;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: var(--shadow-lg);
}

.avatar-upload-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    color: var(--primary);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.avatar-upload-btn:hover {
    transform: scale(1.1);
}

.profile-header-info h1 {
    font-size: 32px;
    margin-bottom: 8px;
}

.profile-email {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 12px;
}

.profile-rank {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 14px;
}

.profile-stats-quick {
    display: flex;
    gap: 32px;
}

.stat-quick-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-quick-item i {
    font-size: 32px;
}

.stat-quick-item strong {
    display: block;
    font-size: 28px;
    line-height: 1;
}

.stat-quick-item span {
    display: block;
    font-size: 13px;
    opacity: 0.9;
}

.profile-content {
    padding: 40px 0;
}

.profile-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 32px;
    background: white;
    padding: 8px;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.profile-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    color: var(--gray-600);
    cursor: pointer;
    transition: var(--transition-base);
}

.profile-tab:hover {
    background: var(--gray-100);
    color: var(--gray-900);
}

.profile-tab.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    display: flex;
    gap: 16px;
    padding: 24px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: white;
    font-size: 28px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-info h3 {
    font-size: 28px;
    margin-bottom: 4px;
    color: var(--gray-900);
}

.stat-info p {
    font-size: 14px;
    color: var(--gray-600);
    margin-bottom: 12px;
}

.stat-progress {
    height: 6px;
    background: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.stat-progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 3px;
    transition: width 1s ease;
}

.stat-percent {
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
}

.dashboard-section {
    margin-bottom: 40px;
}

.progress-chart-card {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 48px;
    padding: 40px;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
}

.progress-circle-wrapper {
    position: relative;
    width: 220px;
    height: 220px;
    margin: 0 auto;
}

.progress-circle {
    width: 100%;
    height: 100%;
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
    font-size: 48px;
    font-weight: 700;
    color: var(--primary);
    line-height: 1;
}

.progress-label {
    display: block;
    font-size: 14px;
    color: var(--gray-600);
    margin-top: 8px;
}

.progress-details h3 {
    font-size: 24px;
    margin-bottom: 24px;
}

.progress-detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid var(--gray-200);
}

.progress-detail-item span {
    color: var(--gray-600);
}

.progress-detail-item strong {
    font-size: 18px;
}

.text-success {
    color: #10b981;
}

.text-primary {
    color: var(--primary);
}

.activity-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    border-bottom: 1px solid var(--gray-200);
    transition: var(--transition-base);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: var(--gray-50);
}

.activity-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #10b981, #14b8a6);
    color: white;
    border-radius: 12px;
    font-size: 20px;
}

.activity-content {
    flex: 1;
}

.activity-content h4 {
    font-size: 16px;
    margin-bottom: 4px;
}

.activity-content p {
    font-size: 13px;
    color: var(--gray-600);
}

.info-card {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
}

.profile-form {
    margin-top: 32px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--gray-700);
}

.required {
    color: var(--danger);
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--gray-300);
    border-radius: 8px;
    font-size: 15px;
    transition: var(--transition-base);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.form-actions {
    display: flex;
    gap: 12px;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.badge-card {
    text-align: center;
    padding: 32px 24px;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.badge-card.earned {
    border: 2px solid var(--primary);
}

.badge-card.locked {
    opacity: 0.5;
}

.badge-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.badge-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    border-radius: 50%;
    color: white;
    font-size: 36px;
}

.badge-card h4 {
    font-size: 18px;
    margin-bottom: 8px;
}

.badge-card p {
    font-size: 14px;
    color: var(--gray-600);
    margin-bottom: 16px;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.badge-status.earned {
    background: #d1fae5;
    color: #065f46;
}

.badge-status.locked {
    background: var(--gray-200);
    color: var(--gray-600);
}

.bookmarks-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.bookmark-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
}

.bookmark-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.bookmark-image {
    display: block;
    height: 200px;
    overflow: hidden;
}

.bookmark-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bookmark-body {
    padding: 20px;
}

.bookmark-body h4 {
    font-size: 16px;
    margin-bottom: 8px;
}

.bookmark-body h4 a {
    color: var(--gray-900);
}

.bookmark-author {
    font-size: 13px;
    color: var(--gray-600);
    margin-bottom: 8px;
}

.bookmark-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--gray-500);
    margin-bottom: 16px;
}

.bookmark-actions {
    display: flex;
    gap: 8px;
}

.bookmark-actions .btn {
    flex: 1;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
}

@media (max-width: 768px) {
    .profile-header-content {
        flex-direction: column;
        gap: 24px;
    }
    
    .stats-grid,
    .badges-grid,
    .bookmarks-grid {
        grid-template-columns: 1fr;
    }
    
    .progress-chart-card {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-tabs {
        flex-direction: column;
    }
}
</style>

<script>
// Tab switching
document.querySelectorAll('.profile-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Add active class
        this.classList.add('active');
        const tabId = 'tab-' + this.dataset.tab;
        document.getElementById(tabId).classList.add('active');
    });
});

// Avatar preview
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove bookmark
function removeBookmark(storyId) {
    if(confirm('Bạn có chắc muốn xóa truyện này khỏi danh sách đã lưu?')) {
        // TODO: Ajax call to remove bookmark
        alert('Tính năng đang được phát triển');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
