<?php
/**
 * Nhóm học tập cộng đồng - Learning Groups
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('learning_groups') ?? 'Nhóm học tập';

// Lấy ngôn ngữ hiện tại
$currentLang = getCurrentLang();
$isKhmer = ($currentLang === 'km');

$pdo = getDBConnection();

// Kiểm tra bảng tồn tại
$tableExists = false;
try {
    $tableExists = $pdo->query("SHOW TABLES LIKE 'nhom_hoc_tap'")->rowCount() > 0;
} catch (Exception $e) {
    $tableExists = false;
}

$learningGroups = [];
$totalGroups = 0;
$totalMembers = 0;
$totalPosts = 0;

if ($tableExists) {
    // Lấy user ID hiện tại
    $current_user_id = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;
    
    // Lấy danh sách nhóm học tập
    $stmt = $pdo->prepare("
        SELECT 
            nht.*,
            nd.ho_ten as ten_nguoi_tao,
            nd.anh_dai_dien as anh_nguoi_tao
        FROM nhom_hoc_tap nht
        LEFT JOIN nguoi_dung nd ON nht.ma_nguoi_tao = nd.ma_nguoi_dung
        WHERE nht.trang_thai IN ('hoat_dong', 'cong_khai')
        ORDER BY nht.thu_tu ASC, nht.ngay_tao DESC
    ");
    $stmt->execute();
    $learningGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thêm đường dẫn đầy đủ cho ảnh banner
    foreach ($learningGroups as &$group) {
        if (!empty($group['anh_banner'])) {
            // Nếu đã có đường dẫn đầy đủ thì giữ nguyên, nếu không thì thêm BASE_URL
            if (strpos($group['anh_banner'], 'http') !== 0) {
                $group['anh_banner'] = BASE_URL . '/' . ltrim($group['anh_banner'], '/');
            }
        }
    }
    unset($group);
    
    // Lấy thống kê tổng quan
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as tong_nhom,
            COALESCE(SUM(so_thanh_vien), 0) as tong_thanh_vien,
            COALESCE(SUM(so_bai_viet), 0) as tong_bai_viet
        FROM nhom_hoc_tap
        WHERE trang_thai IN ('hoat_dong', 'cong_khai')
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $totalGroups = $stats['tong_nhom'];
    $totalMembers = $stats['tong_thanh_vien'];
    $totalPosts = $stats['tong_bai_viet'];
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Learning Groups Page - Unified Design ===== */
.groups-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #FFF6E5 0%, #FFE4B5 50%, #FFCC80 100%);
}

/* Hero Section */
.groups-hero {
    min-height: 25vh;
    background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 100%);
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
}

.groups-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #1a1a1a;
    padding: 1rem 0;
}

.groups-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #1a1a1a !important;
    text-shadow: 2px 2px 4px rgba(255, 152, 0, 0.1);
}

.groups-hero-subtitle {
    font-size: 1.125rem;
    color: #2d2d2d;
    font-weight: 600;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Main Content */
.groups-main {
    padding: 2rem 0;
    background: linear-gradient(180deg, #FFE4B5 0%, #FFCC80 100%);
    min-height: 60vh;
}

/* Filter Bar */
.filter-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.filter-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-search {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.filter-search input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #1a1a1a;
}

.filter-search input:focus {
    outline: none;
    border-color: #FF9800;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
}

.filter-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FF9800;
    font-weight: 600;
}

.btn-create-group {
    padding: 0.875rem 1.5rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.btn-create-group:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.filter-reset {
    padding: 0.875rem 1.25rem;
    background: #ffffff;
    color: #1a1a1a;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 3px 3px 0px #FF9800;
}

.filter-reset:hover {
    background: #1a1a1a;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #FF9800;
}

/* Section Header */
.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.section-title i {
    color: #FF9800;
}

.results-count {
    font-size: 0.9375rem;
    color: #1a1a1a;
    font-weight: 700;
    background: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    border: 3px solid #1a1a1a;
}

.results-count strong {
    color: #FF9800;
    font-weight: 900;
}

/* Groups Table */
.groups-table {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 4px 4px 0px #1a1a1a;
    border: 3px solid #1a1a1a;
}

/* Table Header */
.groups-table-header {
    display: grid;
    grid-template-columns: 200px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-bottom: 3px solid #1a1a1a;
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a1a;
}

/* Table Body */
.groups-table-body {
    display: flex;
    flex-direction: column;
}

/* Group Card - Table Row */
.group-card {
    display: grid;
    grid-template-columns: 200px 1fr 200px;
    gap: 2rem;
    padding: 1.5rem 2rem;
    background: #ffffff;
    border-bottom: 2px solid #FFE4B5;
    transition: all 0.3s ease;
    text-decoration: none;
    align-items: center;
}

.group-card:last-child {
    border-bottom: none;
}

.group-card:hover {
    background: #ffffff;
    transform: translateX(5px);
}

/* Banner Column */
.group-card-banner-wrapper {
    width: 180px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    border: 3px solid #1a1a1a;
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 4px 4px 0px #1a1a1a;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    position: relative;
}

.group-card-banner {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.group-card-banner-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
    color: white;
    font-size: 2rem;
}

.group-card:hover .group-card-banner-wrapper {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px #1a1a1a;
}

/* Name Column */
.group-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.group-card-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.4;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Stats Column */
.group-card-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #2d2d2d;
    font-weight: 600;
}

.group-card-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.group-card-stat i {
    color: #FF9800;
    font-size: 1rem;
}

/* Actions Column */
.group-card-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-join-group,
.btn-view-group,
.btn-delete-group {
    padding: 0.75rem 1.25rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    white-space: nowrap;
    box-shadow: 3px 3px 0px #1a1a1a;
}

.btn-join-group:hover,
.btn-view-group:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.btn-delete-group {
    background: #ef4444;
    border-color: #1a1a1a;
    color: #ffffff;
}

.btn-delete-group:hover {
    background: #dc2626;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #1a1a1a;
}

.btn-join-group {
    flex: 1;
}

.btn-view-group {
    padding: 0.75rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: none;
    border: 3px solid #1a1a1a;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: linear-gradient(135deg, #FFE4B5, #FFCC80);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #FF9800;
    border: 3px solid #1a1a1a;
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.empty-state-desc {
    font-size: 1rem;
    color: #2d2d2d;
    margin-bottom: 2rem;
}

.empty-state-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: #FF9800;
    color: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 4px 4px 0px #1a1a1a;
}

.empty-state-btn:hover {
    background: #F57C00;
    color: #ffffff;
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px #1a1a1a;
}

/* Responsive */
@media (max-width: 768px) {
    .groups-table-header {
        display: none;
    }
    
    .group-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.25rem;
    }
    
    .group-card-banner-wrapper {
        width: 100%;
        height: 80px;
    }
    
    .group-card-banner-placeholder {
        font-size: 1.5rem;
    }
    
    .group-card-stats {
        flex-direction: row;
        gap: 1rem;
    }
    
    .group-card-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-join-group,
    .btn-view-group {
        width: 100%;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-search {
        width: 100%;
    }
    
    .hero-stats {
        gap: 1.5rem;
    }
    
    .groups-hero-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .group-card {
        padding: 1rem;
    }
    
    .group-card-banner-wrapper {
        width: 100%;
        height: 70px;
    }
    
    .group-card-banner-placeholder {
        font-size: 1.25rem;
    }
    
    .group-card-name {
        font-size: 1rem;
    }
    
    .group-card-stats {
        font-size: 0.8125rem;
    }
}
</style>

<main class="groups-page">
    <!-- Hero Section -->
    <section class="groups-hero">
        <div class="container">
            <div class="groups-hero-content">
                <h1 class="groups-hero-title">👥 Nhóm học tập</h1>
                <p class="groups-hero-subtitle">Tham gia cộng đồng, học hỏi và chia sẻ kiến thức về văn hóa Khmer</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="groups-main">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-form">
                    <div class="filter-search">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm nhóm học tập..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" onkeyup="searchGroups(this.value)">
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                    <button type="button" class="btn-create-group" onclick="showCreateGroupModal()">
                        <i class="fas fa-plus-circle"></i>
                        Tạo nhóm mới
                    </button>
                    <?php endif; ?>
                    
                    <?php if (!empty($_GET['search'])): ?>
                    <a href="<?= BASE_URL ?>/learning_groups.php" class="filter-reset">
                        <i class="fas fa-times"></i> Đặt lại
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Groups Grid -->
            <?php if (empty($learningGroups)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="empty-state-title">Chưa có nhóm học tập nào</h3>
                <p class="empty-state-desc">Hãy là người đầu tiên tạo nhóm học tập</p>
                <?php if (isLoggedIn()): ?>
                <a href="#" class="empty-state-btn" onclick="showCreateGroupModal(); return false;">
                    <i class="fas fa-plus-circle"></i>
                    Tạo nhóm mới
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="section-header-row">
                <h2 class="section-title">
                    Tất cả nhóm học tập
                </h2>
                <span class="results-count">
                    Hiển thị <strong><?= count($learningGroups) ?></strong> nhóm
                </span>
            </div>
            
            <div class="groups-table">
                <!-- Table Header -->
                <div class="groups-table-header">
                    <div>Ảnh banner</div>
                    <div>Tên nhóm</div>
                    <div>Nút thao tác</div>
                </div>
                
                <!-- Table Body -->
                <div class="groups-table-body">
                <?php foreach ($learningGroups as $group): ?>
                <div class="group-card">
                    <!-- Banner Column -->
                    <div class="group-card-banner-wrapper">
                        <?php if (!empty($group['anh_banner'])): ?>
                            <img src="<?= htmlspecialchars($group['anh_banner']) ?>" 
                                 alt="<?= sanitize($isKhmer && !empty($group['ten_nhom_km']) ? $group['ten_nhom_km'] : $group['ten_nhom']) ?>" 
                                 class="group-card-banner"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="group-card-banner-placeholder" style="display: none;">
                                <i class="<?= htmlspecialchars($group['icon'] ?? 'fas fa-users') ?>"></i>
                            </div>
                        <?php else: ?>
                            <div class="group-card-banner-placeholder">
                                <i class="<?= htmlspecialchars($group['icon'] ?? 'fas fa-users') ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Name Column -->
                    <div class="group-card-content">
                        <h3 class="group-card-name">
                            <?= sanitize($isKhmer && !empty($group['ten_nhom_km']) ? $group['ten_nhom_km'] : $group['ten_nhom']) ?>
                        </h3>
                    </div>
                    
                    <!-- Actions Column -->
                    <div class="group-card-actions">
                        <?php if (isLoggedIn()): ?>
                            <?php 
                            $current_user_id = $_SESSION['ma_nguoi_dung'] ?? $_SESSION['user_id'] ?? null;
                            $is_creator = ($current_user_id == $group['ma_nguoi_tao']);
                            ?>
                            
                            <?php if ($is_creator): ?>
                                <button class="btn-delete-group" onclick="deleteGroup(<?= $group['ma_nhom'] ?>)" title="Xóa nhóm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-join-group" onclick="joinGroup(<?= $group['ma_nhom'] ?>)">
                                    <i class="fas fa-user-plus"></i>
                                    Tham gia
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/login.php" class="btn-join-group">
                                <i class="fas fa-sign-in-alt"></i>
                                Đăng nhập
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/group_detail.php?id=<?= $group['ma_nhom'] ?>" class="btn-view-group" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/create-group-modal.php'; ?>


<script>
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    };
    
    const colors = {
        success: '#48bb78',
        error: '#f56565',
        info: '#667eea'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        border-left: 4px solid ${colors[type]};
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 1.25rem;"></i>
        <span style="color: #2d3748; font-weight: 600;">${message}</span>
        <button onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
            margin-left: auto;
        ">×</button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Hàm xóa nhóm
function deleteGroup(groupId) {
    if (!confirm('⚠️ Bạn có chắc chắn muốn xóa nhóm này?\n\nLưu ý: Tất cả bài viết, bình luận và thành viên trong nhóm sẽ bị xóa!')) {
        return;
    }
    
    const btn = event.target.closest('.btn-delete-group');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('<?= BASE_URL ?>/api/delete_group.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_nhom: groupId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Xóa nhóm thành công!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Không thể xóa nhóm!', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('Đã xảy ra lỗi: ' + error.message, 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Hàm tham gia nhóm
function joinGroup(groupId) {
    if (!confirm('Bạn có muốn tham gia nhóm này?')) {
        return;
    }
    
    const btn = event.target.closest('.btn-join-group');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;
    
    fetch('<?= BASE_URL ?>/api/join_group.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_nhom: groupId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Tham gia nhóm thành công!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Không thể tham gia nhóm!', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('Đã xảy ra lỗi: ' + error.message, 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Tìm kiếm nhóm
function searchGroups(searchTerm) {
    searchTerm = searchTerm.toLowerCase().trim();
    const groupCards = document.querySelectorAll('.group-card');
    let visibleCount = 0;
    
    groupCards.forEach(card => {
        const groupName = card.querySelector('.group-card-name').textContent.toLowerCase();
        
        if (searchTerm === '' || groupName.includes(searchTerm)) {
            card.style.display = 'grid';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    const resultsCount = document.querySelector('.results-count');
    if (resultsCount) {
        resultsCount.innerHTML = `Hiển thị <strong>${visibleCount}</strong> nhóm`;
    }
}
</script>

<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
