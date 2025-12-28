<?php
/**
 * Trang cá nhân - Modern Redesign 2024
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('profile');

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php', __('please_login_profile'), 'warning');
}

$user = getCurrentUser();
if (!$user) {
    session_destroy();
    redirect(BASE_URL . '/login.php', __('invalid_session'), 'warning');
}

try {
    $pdo = getDBConnection();
    
    // Refresh user data from database to get latest points
    $userStmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?");
    $userStmt->execute([$user['ma_nguoi_dung']]);
    $freshUser = $userStmt->fetch();
    
    if ($freshUser) {
        // Update session with fresh data
        $_SESSION['user'] = [
            'ma_nguoi_dung' => $freshUser['ma_nguoi_dung'],
            'ten_dang_nhap' => $freshUser['ten_dang_nhap'],
            'ho_ten' => $freshUser['ho_ten'],
            'email' => $freshUser['email'],
            'anh_dai_dien' => $freshUser['anh_dai_dien'],
            'tong_diem' => $freshUser['tong_diem'],
            'cap_do' => $freshUser['cap_do']
        ];
        $user = $freshUser; // Use fresh data
    }

    // Get user stats
    $lessonsStmt = $pdo->prepare("SELECT COUNT(*) FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND trang_thai = 'hoan_thanh'");
    $lessonsStmt->execute([$user['ma_nguoi_dung']]);
    $lessonsCompleted = $lessonsStmt->fetchColumn();
    
    // Get quiz stats
    $quizCount = 0;
    $totalQuizScore = 0;
    try {
        $quizTables = ['ket_qua_quiz', 'ket_qua_quiz_chua', 'ket_qua_quiz_le_hoi', 'ket_qua_quiz_truyen', 'ket_qua_quiz_van_hoa'];
        foreach ($quizTables as $table) {
            $checkTable = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($checkTable->rowCount() > 0) {
                $qStmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(diem), 0) FROM $table WHERE ma_nguoi_dung = ?");
                $qStmt->execute([$user['ma_nguoi_dung']]);
                $result = $qStmt->fetch(PDO::FETCH_NUM);
                $quizCount += $result[0];
                $totalQuizScore += $result[1];
                
                // Debug log
                error_log("Quiz table $table: count={$result[0]}, score={$result[1]}");
            }
        }
        error_log("Total quiz count: $quizCount, Total score: $totalQuizScore for user {$user['ma_nguoi_dung']}");
    } catch (Exception $e) { 
        error_log("Quiz query error: " . $e->getMessage());
        $quizCount = 0; 
    }

    // Get user rank
    $rankStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM nguoi_dung WHERE tong_diem > ?");
    $rankStmt->execute([$user['tong_diem'] ?? 0]);
    $userRank = $rankStmt->fetchColumn();
    
    $totalUsersStmt = $pdo->query("SELECT COUNT(*) FROM nguoi_dung");
    $totalUsers = $totalUsersStmt->fetchColumn();

    // Get badges - with error handling
    $badges = [];
    $totalBadges = 0;
    try {
        // Check if tables exist
        $checkBadgeTable = $pdo->query("SHOW TABLES LIKE 'huy_hieu'");
        $checkUserBadgeTable = $pdo->query("SHOW TABLES LIKE 'huy_hieu_nguoi_dung'");
        
        if ($checkBadgeTable->rowCount() > 0 && $checkUserBadgeTable->rowCount() > 0) {
            $badgesStmt = $pdo->prepare("SELECT h.*, hn.ngay_dat_duoc FROM huy_hieu h JOIN huy_hieu_nguoi_dung hn ON h.ma_huy_hieu = hn.ma_huy_hieu WHERE hn.ma_nguoi_dung = ? ORDER BY hn.ngay_dat_duoc DESC LIMIT 8");
            $badgesStmt->execute([$user['ma_nguoi_dung']]);
            $badges = $badgesStmt->fetchAll();
            
            $totalBadgesStmt = $pdo->prepare("SELECT COUNT(*) FROM huy_hieu_nguoi_dung WHERE ma_nguoi_dung = ?");
            $totalBadgesStmt->execute([$user['ma_nguoi_dung']]);
            $totalBadges = $totalBadgesStmt->fetchColumn();
        }
    } catch (Exception $e) {
        error_log("Badge query error: " . $e->getMessage());
    }

    // Get saved items - with error handling
    $savedItems = [];
    $favoritesCount = 0;
    try {
        $checkFavTable = $pdo->query("SHOW TABLES LIKE 'yeu_thich'");
        $checkCultureTable = $pdo->query("SHOW TABLES LIKE 'van_hoa'");
        
        if ($checkFavTable->rowCount() > 0 && $checkCultureTable->rowCount() > 0) {
            $savedStmt = $pdo->prepare("
                SELECT y.ma_yeu_thich, y.ma_doi_tuong, y.loai_doi_tuong, y.ngay_tao, 
                       v.tieu_de, v.hinh_anh_chinh 
                FROM yeu_thich y 
                LEFT JOIN van_hoa v ON y.ma_doi_tuong = v.ma_van_hoa 
                WHERE y.ma_nguoi_dung = ? AND y.loai_doi_tuong = 'van_hoa'
                ORDER BY y.ngay_tao DESC LIMIT 6
            ");
            $savedStmt->execute([$user['ma_nguoi_dung']]);
            $savedItems = $savedStmt->fetchAll();
            
            $totalFavStmt = $pdo->prepare("SELECT COUNT(*) FROM yeu_thich WHERE ma_nguoi_dung = ? AND loai_doi_tuong = 'van_hoa'");
            $totalFavStmt->execute([$user['ma_nguoi_dung']]);
            $favoritesCount = $totalFavStmt->fetchColumn();
        }
    } catch (Exception $e) {
        error_log("Saved items query error: " . $e->getMessage());
    }

    // Get activities - with error handling
    $activities = [];
    try {
        $checkActivityTable = $pdo->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
        if ($checkActivityTable->rowCount() > 0) {
            $activitiesStmt = $pdo->prepare("SELECT * FROM nhat_ky_hoat_dong WHERE ma_nguoi_dung = ? AND loai_nguoi_dung = 'nguoi_dung' ORDER BY ngay_tao DESC LIMIT 8");
            $activitiesStmt->execute([$user['ma_nguoi_dung']]);
            $activities = $activitiesStmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log("Activities query error: " . $e->getMessage());
    }
    
    // Get learning progress - with error handling
    $learningProgress = [];
    try {
        $checkProgressTable = $pdo->query("SHOW TABLES LIKE 'tien_trinh_hoc_tap'");
        $checkLessonTable = $pdo->query("SHOW TABLES LIKE 'bai_hoc'");
        
        if ($checkProgressTable->rowCount() > 0 && $checkLessonTable->rowCount() > 0) {
            // Kiểm tra cột nào tồn tại để ORDER BY
            $columns = $pdo->query("DESCRIBE tien_trinh_hoc_tap")->fetchAll(PDO::FETCH_COLUMN);
            
            $orderBy = 'tt.ma_tien_trinh DESC'; // Default
            if (in_array('ngay_cap_nhat', $columns)) {
                $orderBy = 'tt.ngay_cap_nhat DESC';
            } elseif (in_array('ngay_hoan_thanh', $columns) && in_array('ngay_bat_dau', $columns)) {
                $orderBy = 'COALESCE(tt.ngay_hoan_thanh, tt.ngay_bat_dau) DESC';
            } elseif (in_array('ngay_bat_dau', $columns)) {
                $orderBy = 'tt.ngay_bat_dau DESC';
            } elseif (in_array('ngay_tao', $columns)) {
                $orderBy = 'tt.ngay_tao DESC';
            }
            
            $progressStmt = $pdo->prepare("SELECT tt.*, bh.tieu_de as ten_bai_hoc FROM tien_trinh_hoc_tap tt LEFT JOIN bai_hoc bh ON tt.ma_bai_hoc = bh.ma_bai_hoc WHERE tt.ma_nguoi_dung = ? ORDER BY $orderBy LIMIT 5");
            $progressStmt->execute([$user['ma_nguoi_dung']]);
            $learningProgress = $progressStmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log("Learning progress query error: " . $e->getMessage());
    }

    // Get learning groups - with error handling
    $learningGroups = [];
    try {
        $checkGroupTable = $pdo->query("SHOW TABLES LIKE 'nhom_hoc_tap'");
        if ($checkGroupTable->rowCount() > 0) {
            $groupsStmt = $pdo->prepare("SELECT nh.*, 
                (SELECT COUNT(*) FROM thanh_vien_nhom WHERE ma_nhom = nh.ma_nhom AND trang_thai = 'hoat_dong') as so_thanh_vien
                FROM nhom_hoc_tap nh 
                WHERE nh.trang_thai = 'hoat_dong' 
                ORDER BY nh.thu_tu ASC 
                LIMIT 6");
            $groupsStmt->execute();
            $learningGroups = $groupsStmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log("Learning groups query error: " . $e->getMessage());
    }

} catch (Exception $e) {
    $lessonsCompleted = $quizCount = $totalQuizScore = $userRank = $totalUsers = $totalBadges = $favoritesCount = 0;
    $badges = $savedItems = $activities = $learningProgress = $learningGroups = [];
}

$userPoints = $user['tong_diem'] ?? 0;
$userLevel = floor($userPoints / 100) + 1;
$pointsToNextLevel = ($userLevel * 100) - $userPoints;
$levelProgress = ($userPoints % 100);
$joinDate = isset($user['ngay_tao']) ? date('d/m/Y', strtotime($user['ngay_tao'])) : 'N/A';
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Profile Page ===== */
.profile-page { 
    min-height: 100vh; 
    background: #ffffff; 
    padding-top: 70px; 
}

/* ===== Hero Section ===== */
.profile-hero { 
    min-height: 30vh;
    background: #ffffff; 
    padding: 4rem 0 3rem; 
    position: relative; 
    border-bottom: 2px solid #e2e8f0;
    margin-top: 70px;
}

.profile-hero-content { 
    position: relative; 
    z-index: 2; 
    text-align: center; 
    color: #000000; 
}

.profile-hero-title { 
    font-size: clamp(2rem, 5vw, 2.5rem); 
    font-weight: 900; 
    margin-bottom: 0.75rem; 
    color: #000000 !important; 
}

.profile-hero-subtitle { 
    font-size: 1.125rem; 
    color: #000000; 
    font-weight: 600; 
}

/* Container */
.profile-container { max-width: 1140px; margin: 0 auto; padding: 0 1rem; }

/* ===== Main Card ===== */
.profile-main-card { 
    background: #ffffff; 
    border-radius: 20px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
    margin-top: 2rem; 
    position: relative; 
    z-index: 10; 
    overflow: hidden; 
    border: 2px solid #000000;
}

/* ===== Header ===== */
.profile-header { 
    display: flex; 
    align-items: flex-start; 
    gap: 1.5rem; 
    padding: 2rem; 
    background: #ffffff; 
    border-bottom: 2px solid #000000; 
}
.profile-avatar-wrapper { position: relative; flex-shrink: 0; }
.profile-avatar { 
    width: 130px; 
    height: 130px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 3px solid #000000; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.15); 
}

.profile-avatar-badge { 
    position: absolute; 
    bottom: -4px; 
    right: -4px; 
    width: 44px; 
    height: 44px; 
    background: #ffffff; 
    border-radius: 50%; 
    border: 3px solid #000000; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1.125rem; 
}

.profile-avatar-edit { 
    position: absolute; 
    top: 6px; 
    right: 6px; 
    width: 36px; 
    height: 36px; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #000000; 
    text-decoration: none; 
    opacity: 0; 
    transition: all 0.3s; 
}

.profile-avatar-wrapper:hover .profile-avatar-edit { 
    opacity: 1; 
}

.profile-avatar-edit:hover { 
    background: #000000; 
    color: #ffffff; 
}

/* Info */
.profile-info { flex: 1; min-width: 0; }
.profile-name-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
.profile-name { 
    font-size: 1.75rem; 
    font-weight: 900; 
    color: #000000; 
    margin: 0; 
}

.profile-level-badge { 
    display: inline-flex; 
    align-items: center; 
    gap: 0.375rem; 
    padding: 0.375rem 0.875rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 50px; 
    color: #000000; 
    font-size: 0.75rem; 
    font-weight: 700; 
}

.profile-email { 
    font-size: 0.9375rem; 
    color: #000000; 
    font-weight: 600;
    margin-bottom: 0.375rem; 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
}

.profile-meta { 
    display: flex; 
    align-items: center; 
    gap: 1.25rem; 
    flex-wrap: wrap; 
    margin-bottom: 1rem; 
}

.profile-meta-item { 
    display: flex; 
    align-items: center; 
    gap: 0.375rem; 
    font-size: 0.8125rem; 
    color: #000000; 
    font-weight: 700;
}

.profile-meta-item i { 
    color: #f59e0b; 
    font-size: 0.875rem; 
}

/* ===== Level Progress ===== */
.profile-level-progress { 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 12px; 
    padding: 0.875rem 1rem; 
    margin-bottom: 1rem; 
}

.level-progress-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 0.5rem; 
}

.level-progress-title { 
    font-size: 0.8125rem; 
    font-weight: 700; 
    color: #000000; 
    display: flex; 
    align-items: center; 
    gap: 0.375rem; 
}

.level-progress-points { 
    font-size: 0.75rem; 
    color: #000000; 
    font-weight: 700; 
}

.level-progress-bar { 
    height: 8px; 
    background: #e2e8f0; 
    border-radius: 8px; 
    overflow: hidden; 
    border: 1px solid #000000;
}

.level-progress-fill { 
    height: 100%; 
    background: #06b6d4; 
    border-radius: 8px; 
}

/* ===== Actions ===== */
.profile-actions { 
    display: flex; 
    gap: 0.625rem; 
    flex-wrap: wrap; 
}

.profile-btn { 
    padding: 0.75rem 1.25rem; 
    border-radius: 12px; 
    font-size: 0.875rem; 
    font-weight: 700; 
    text-decoration: none; 
    display: inline-flex; 
    align-items: center; 
    gap: 0.5rem; 
    transition: all 0.3s; 
    cursor: pointer; 
}

.profile-btn.outline { 
    background: #ffffff; 
    color: #000000; 
    border: 2px solid #000000; 
}

.profile-btn.outline:hover { 
    background: #000000; 
    color: #ffffff; 
    transform: translateY(-2px);
}

/* ===== Stats ===== */
.profile-stats-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    border-top: 2px solid #000000; 
}

.profile-stat-item { 
    text-align: center; 
    padding: 1.5rem 0.75rem; 
    border-right: 2px solid #000000; 
    transition: all 0.3s; 
}

.profile-stat-item:last-child { 
    border-right: none; 
}

.profile-stat-item:hover { 
    background: #f8fafc; 
}

.stat-icon-wrapper { 
    width: 50px; 
    height: 50px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    margin: 0 auto 0.75rem; 
    font-size: 1.25rem; 
    background: #ffffff;
    border: 2px solid #000000;
}

.stat-icon-wrapper.purple { color: #8b5cf6; }
.stat-icon-wrapper.green { color: #10b981; }
.stat-icon-wrapper.blue { color: #3b82f6; }
.stat-icon-wrapper.yellow { color: #f59e0b; }

.stat-number { 
    font-size: 1.75rem; 
    font-weight: 900; 
    color: #000000; 
    line-height: 1; 
    margin-bottom: 0.25rem; 
}

.stat-label { 
    font-size: 0.75rem; 
    color: #64748b; 
    font-weight: 700; 
}

/* Content Grid */
.profile-content-grid { 
    display: flex;
    flex-direction: column;
    gap: 1.5rem; 
    padding: 1.5rem 0 2rem; 
}

/* Top Row - 2 columns */
.profile-top-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* Bottom Row - 2 columns */
.profile-bottom-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* ===== Section Card ===== */
.section-card { 
    background: #ffffff; 
    border-radius: 20px; 
    padding: 1.5rem; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
    margin-bottom: 1.25rem; 
    border: 2px solid #000000;
}

.section-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.25rem; 
    padding-bottom: 0.875rem; 
    border-bottom: 2px solid #000000; 
}

.section-title { 
    font-size: 1rem; 
    font-weight: 900; 
    color: #000000; 
    display: flex; 
    align-items: center; 
    gap: 0.625rem; 
    margin: 0; 
}

.section-title-icon { 
    width: 36px; 
    height: 36px; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #f59e0b; 
    font-size: 0.9375rem; 
}

.section-count { 
    font-size: 0.75rem; 
    color: #000000; 
    font-weight: 700; 
    padding: 0.25rem 0.75rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 50px; 
}

.section-link { 
    font-size: 0.8125rem; 
    color: #000000; 
    font-weight: 700; 
    text-decoration: none; 
    display: flex; 
    align-items: center; 
    gap: 0.375rem; 
}

.section-link:hover { 
    color: #f59e0b; 
}

/* ===== Badges ===== */
.badges-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); 
    gap: 0.875rem; 
}

.badge-card { 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    padding: 1rem 0.75rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 16px; 
    text-align: center; 
    transition: all 0.3s; 
    min-width: 0; 
}

.badge-card:hover { 
    transform: translateY(-4px); 
    box-shadow: 0 6px 20px rgba(0,0,0,0.15); 
}

.badge-icon-wrapper { 
    width: 56px; 
    height: 56px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    margin-bottom: 0.625rem; 
    font-size: 1.625rem; 
    flex-shrink: 0; 
    background: #ffffff;
    border: 2px solid #000000;
}

.badge-icon-wrapper.gold { color: #fbbf24; }
.badge-icon-wrapper.silver { color: #94a3b8; }
.badge-icon-wrapper.bronze { color: #f97316; }
.badge-icon-wrapper.purple { color: #8b5cf6; }
.badge-icon-wrapper.green { color: #10b981; }

.badge-name { 
    font-size: 0.8125rem; 
    font-weight: 900; 
    color: #000000; 
    margin-bottom: 0.25rem; 
    line-height: 1.3; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    max-width: 100%; 
    padding: 0 0.25rem; 
}

.badge-date { 
    font-size: 0.625rem; 
    color: #64748b; 
    font-weight: 600;
}

/* ===== Saved ===== */
.saved-grid { 
    display: flex; 
    flex-direction: column; 
    gap: 0.75rem; 
}

.saved-item { 
    display: flex; 
    align-items: center; 
    gap: 0.875rem; 
    padding: 0.875rem 1rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 14px; 
    text-decoration: none; 
    transition: all 0.3s; 
    min-height: 80px; 
}

.saved-item:hover { 
    background: #f8fafc; 
    transform: translateX(3px); 
}

.saved-image-wrapper { 
    width: 64px; 
    height: 64px; 
    border-radius: 12px; 
    overflow: hidden; 
    flex-shrink: 0; 
    border: 2px solid #000000;
}

.saved-image { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
}

.saved-placeholder { 
    width: 100%; 
    height: 100%; 
    background: #f8fafc; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #667eea; 
    font-size: 1.25rem; 
    border-radius: 12px; 
}

.saved-info { 
    flex: 1; 
    min-width: 0; 
    display: flex; 
    flex-direction: column; 
    justify-content: center; 
}

.saved-title { 
    font-size: 0.875rem; 
    font-weight: 900; 
    color: #000000; 
    margin-bottom: 0.375rem; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    line-height: 1.4; 
}

.saved-meta { 
    font-size: 0.6875rem; 
    color: #64748b; 
    font-weight: 700;
    display: flex; 
    align-items: center; 
    gap: 0.25rem; 
}

.saved-meta i {
    color: #f59e0b;
}

/* ===== Progress ===== */
.progress-list { 
    display: flex; 
    flex-direction: column; 
    gap: 0.75rem; 
}

.progress-item { 
    display: flex; 
    align-items: center; 
    gap: 0.875rem; 
    padding: 0.875rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 14px; 
}

.progress-icon { 
    width: 40px; 
    height: 40px; 
    border-radius: 50%; 
    background: #ffffff; 
    border: 2px solid #000000; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #10b981; 
    font-size: 0.9375rem; 
    flex-shrink: 0; 
}

.progress-info { 
    flex: 1; 
    min-width: 0; 
}

.progress-title { 
    font-size: 0.875rem; 
    font-weight: 900; 
    color: #000000; 
    margin-bottom: 0.375rem; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

.progress-bar-wrapper { 
    height: 6px; 
    background: #e2e8f0; 
    border-radius: 6px; 
    overflow: hidden; 
    border: 1px solid #000000;
}

.progress-bar-fill { 
    height: 100%; 
    background: #06b6d4; 
    border-radius: 6px; 
}

.progress-status { 
    font-size: 0.6875rem; 
    font-weight: 700; 
    padding: 0.25rem 0.625rem; 
    border-radius: 50px; 
    flex-shrink: 0; 
    border: 2px solid #000000;
}

.progress-status.completed { 
    background: #ffffff; 
    color: #000000; 
}

.progress-status.in-progress { 
    background: #ffffff; 
    color: #000000; 
}

/* ===== Quick Stats ===== */
.quick-stats-list { 
    display: flex; 
    flex-direction: column; 
    gap: 0.75rem; 
}

.quick-stat-item { 
    display: flex; 
    align-items: center; 
    gap: 0.875rem; 
    padding: 0.875rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 12px; 
}

.quick-stat-icon { 
    width: 40px; 
    height: 40px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 0.9375rem; 
    flex-shrink: 0; 
    background: #ffffff;
    border: 2px solid #000000;
}

.quick-stat-icon.purple { color: #8b5cf6; }
.quick-stat-icon.green { color: #10b981; }
.quick-stat-icon.blue { color: #3b82f6; }

.quick-stat-info { 
    flex: 1; 
}

.quick-stat-label { 
    font-size: 0.75rem; 
    color: #64748b; 
    font-weight: 700;
    margin-bottom: 0.125rem; 
}

.quick-stat-value { 
    font-size: 1.125rem; 
    font-weight: 900; 
    color: #000000; 
}

/* ===== Learning Groups ===== */
.groups-grid { 
    display: flex; 
    flex-direction: column; 
    gap: 0.75rem; 
}

.group-item { 
    display: flex; 
    align-items: center; 
    gap: 0.875rem; 
    padding: 0.875rem 1rem; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 14px; 
    text-decoration: none; 
    transition: all 0.3s; 
}

.group-item:hover { 
    background: #f8fafc; 
    transform: translateX(3px); 
}

.group-icon-wrapper { 
    width: 48px; 
    height: 48px; 
    border-radius: 12px; 
    background: linear-gradient(135deg, #667eea, #764ba2); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1.125rem; 
    color: white; 
    flex-shrink: 0; 
    border: 2px solid #000000;
}

.group-info { 
    flex: 1; 
    min-width: 0; 
}

.group-name { 
    font-size: 0.875rem; 
    font-weight: 900; 
    color: #000000; 
    margin-bottom: 0.25rem; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

.group-members { 
    font-size: 0.6875rem; 
    color: #64748b; 
    font-weight: 700;
    display: flex; 
    align-items: center; 
    gap: 0.25rem; 
}

.group-members i {
    color: #667eea;
}

/* ===== Empty State ===== */
.empty-state { 
    text-align: center; 
    padding: 2rem 1rem; 
}

.empty-state-icon { 
    width: 70px; 
    height: 70px; 
    background: #ffffff; 
    border: 2px solid #000000; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    margin: 0 auto 1rem; 
    font-size: 1.75rem; 
    color: #f59e0b; 
}

.empty-state-title { 
    font-size: 0.9375rem; 
    font-weight: 900; 
    color: #000000; 
    margin-bottom: 0.375rem; 
}

.empty-state-text { 
    font-size: 0.8125rem; 
    color: #64748b; 
    font-weight: 600;
    margin-bottom: 1rem; 
}

.empty-state-btn { 
    display: inline-flex; 
    align-items: center; 
    gap: 0.375rem; 
    padding: 0.625rem 1rem; 
    background: #ffffff; 
    color: #000000; 
    border: 2px solid #000000; 
    border-radius: 10px; 
    text-decoration: none; 
    font-size: 0.8125rem; 
    font-weight: 700; 
}

.empty-state-btn:hover { 
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px); 
}

/* Responsive */
@media (max-width: 1024px) { 
    .profile-top-row,
    .profile-bottom-row { 
        grid-template-columns: 1fr; 
    } 
}
@media (max-width: 768px) {
    .profile-header { flex-direction: column; align-items: center; text-align: center; padding: 1.5rem; }
    .profile-avatar { width: 110px; height: 110px; }
    .profile-name-row { justify-content: center; }
    .profile-name { font-size: 1.5rem; }
    .profile-meta { justify-content: center; }
    .profile-actions { justify-content: center; }
    .profile-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .profile-stat-item { padding: 1.25rem 0.5rem; }
    .stat-number { font-size: 1.5rem; }
    .badges-grid { grid-template-columns: repeat(2, 1fr); }
    .section-card { padding: 1.25rem; }
}
@media (max-width: 480px) {
    .profile-hero { padding: 2rem 0 5rem; }
    .profile-main-card { margin-top: -3.5rem; border-radius: 20px; }
    .profile-avatar { width: 90px; height: 90px; border-radius: 20px; }
    .profile-avatar-badge { width: 36px; height: 36px; font-size: 0.9375rem; }
}
</style>


<!-- Profile Page -->
<div class="profile-page">
    <!-- Hero Section -->
    <section class="profile-hero">
        <div class="profile-container">
            <div class="profile-hero-content">
                <h1 class="profile-hero-title"><?= __('profile') ?></h1>
                <p class="profile-hero-subtitle"><?= __('manage_your_account') ?></p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="profile-container">
        <!-- Main Profile Card -->
        <div class="profile-main-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar-wrapper">
                    <img src="<?= !empty($user['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $user['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                         alt="<?= sanitize($user['ho_ten']) ?>" class="profile-avatar">
                    <div class="profile-avatar-badge"><span>🏆</span></div>
                    <a href="<?= BASE_URL ?>/settings.php" class="profile-avatar-edit" title="Đổi ảnh"><i class="fas fa-camera"></i></a>
                </div>
                
                <div class="profile-info">
                    <div class="profile-name-row">
                        <h1 class="profile-name"><?= sanitize($user['ho_ten']) ?></h1>
                        <span class="profile-level-badge"><i class="fas fa-star"></i> Level <?= $userLevel ?></span>
                    </div>
                    
                    <p class="profile-email"><i class="fas fa-envelope"></i> <?= sanitize($user['email']) ?></p>
                    
                    <div class="profile-meta">
                        <span class="profile-meta-item"><i class="fas fa-calendar-alt"></i> <?= __('joined_on') ?>: <?= $joinDate ?></span>
                        <span class="profile-meta-item"><i class="fas fa-trophy"></i> <?= __('rank') ?> #<?= number_format($userRank) ?>/<?= number_format($totalUsers) ?></span>
                        <span class="profile-meta-item"><i class="fas fa-coins"></i> <?= number_format($userPoints) ?> <?= __('points') ?></span>
                    </div>
                    
                    <div class="profile-level-progress">
                        <div class="level-progress-header">
                            <span class="level-progress-title"><i class="fas fa-chart-line"></i> <?= __('learning_progress') ?></span>
                            <span class="level-progress-points"><?= $levelProgress ?>/100 XP</span>
                        </div>
                        <div class="level-progress-bar">
                            <div class="level-progress-fill" style="width: <?= $levelProgress ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="<?= BASE_URL ?>/settings.php" class="profile-btn outline"><i class="fas fa-cog"></i> <?= __('settings') ?></a>
                        <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="profile-btn outline"><i class="fas fa-graduation-cap"></i> Tiếp tục học</a>
                        <a href="<?= BASE_URL ?>/leaderboard.php" class="profile-btn outline"><i class="fas fa-medal"></i> <?= __('leaderboard') ?></a>
                    </div>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="profile-stats-grid">
                <div class="profile-stat-item">
                    <div class="stat-icon-wrapper purple"><i class="fas fa-star"></i></div>
                    <div class="stat-number"><?= number_format($userPoints) ?></div>
                    <div class="stat-label"><?= __('points') ?></div>
                </div>
                <div class="profile-stat-item">
                    <div class="stat-icon-wrapper green"><i class="fas fa-book-reader"></i></div>
                    <div class="stat-number"><?= number_format($lessonsCompleted) ?></div>
                    <div class="stat-label"><?= __('lessons') ?></div>
                </div>
                <div class="profile-stat-item">
                    <div class="stat-icon-wrapper blue"><i class="fas fa-question-circle"></i></div>
                    <div class="stat-number"><?= number_format($quizCount) ?></div>
                    <div class="stat-label">Quiz</div>
                </div>
                <div class="profile-stat-item">
                    <div class="stat-icon-wrapper yellow"><i class="fas fa-award"></i></div>
                    <div class="stat-number"><?= number_format($totalBadges) ?></div>
                    <div class="stat-label"><?= __('badges') ?></div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="profile-content-grid">
            <!-- Top Row - Badges and Saved Items -->
            <div class="profile-top-row">
                <!-- Badges Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="section-title-icon"><i class="fas fa-medal"></i></span>
                            <?= __('badges') ?>
                        </h2>
                        <?php if ($totalBadges > 0): ?>
                        <span class="section-count"><?= $totalBadges ?> <?= __('badges') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($badges)): ?>
                    <div class="badges-grid">
                        <?php foreach ($badges as $badge): 
                            $colors = ['gold', 'silver', 'bronze', 'purple', 'green'];
                            $color = $colors[$badge['ma_huy_hieu'] % count($colors)];
                        ?>
                        <div class="badge-card">
                            <div class="badge-icon-wrapper <?= $color ?>">
                                <?php 
                                $iconValue = $badge['icon'] ?? $badge['bieu_tuong'] ?? '🏅';
                                // Kiểm tra nếu là FontAwesome class
                                if (strpos($iconValue, 'fa-') === 0 || strpos($iconValue, 'fas ') === 0) {
                                    echo '<i class="fas ' . $iconValue . '"></i>';
                                } else {
                                    // Là emoji
                                    echo $iconValue;
                                }
                                ?>
                            </div>
                            <div class="badge-name"><?= sanitize(getCurrentLang() === 'km' && !empty($badge['ten_huy_hieu_khmer']) ? $badge['ten_huy_hieu_khmer'] : $badge['ten_huy_hieu']) ?></div>
                            <div class="badge-date"><?= date('d/m/Y', strtotime($badge['ngay_dat_duoc'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-medal"></i></div>
                        <h3 class="empty-state-title"><?= __('no_activity') ?></h3>
                        <p class="empty-state-text"><?= __('complete_lesson') ?></p>
                        <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="empty-state-btn"><i class="fas fa-graduation-cap"></i> <?= __('start') ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Saved Items -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="section-title-icon"><i class="fas fa-heart"></i></span>
                            <?= __('saved') ?>
                        </h2>
                        <?php if ($favoritesCount > 0): ?>
                        <span class="section-count"><?= $favoritesCount ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($savedItems)): ?>
                    <div class="saved-grid">
                        <?php foreach ($savedItems as $item): 
                            // Xử lý đường dẫn ảnh giống van-hoa-chi-tiet.php
                            $imagePath = $item['hinh_anh_chinh'] ?? '';
                            $imageUrl = '';
                            if (!empty($imagePath)) {
                                if (strpos($imagePath, 'http') === 0) {
                                    $imageUrl = $imagePath;
                                } elseif (strpos($imagePath, 'uploads/') === 0) {
                                    $imageUrl = '/DoAn_ChuyenNganh/' . $imagePath;
                                } else {
                                    $imageUrl = UPLOAD_PATH . 'vanhoa/' . $imagePath;
                                }
                            }
                        ?>
                        <a href="<?= BASE_URL ?>/van-hoa-chi-tiet.php?id=<?= $item['ma_doi_tuong'] ?>" class="saved-item">
                            <div class="saved-image-wrapper">
                                <?php if (!empty($imageUrl)): ?>
                                <img src="<?= $imageUrl ?>" alt="" class="saved-image" onerror="this.parentElement.innerHTML='<div class=\'saved-placeholder\'><i class=\'fas fa-image\'></i></div>';">
                                <?php else: ?>
                                <div class="saved-placeholder"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="saved-info">
                                <div class="saved-title"><?= sanitize($item['tieu_de']) ?></div>
                                <div class="saved-meta"><i class="fas fa-clock"></i> <?= timeAgo($item['ngay_tao']) ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-bookmark"></i></div>
                        <h3 class="empty-state-title"><?= __('no_saved_articles') ?></h3>
                        <a href="<?= BASE_URL ?>/van-hoa.php" class="empty-state-btn"><i class="fas fa-compass"></i> <?= __('explore_now') ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom Row - Learning Progress and Groups -->
            <div class="profile-bottom-row">
                <!-- Learning Progress -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="section-title-icon"><i class="fas fa-tasks"></i></span>
                            <?= __('learning_progress') ?>
                        </h2>
                        <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="section-link"><?= __('view_all') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (!empty($learningProgress)): ?>
                    <div class="progress-list">
                        <?php foreach ($learningProgress as $progress): 
                            $isCompleted = $progress['trang_thai'] === 'hoan_thanh';
                            $percent = $isCompleted ? 100 : ($progress['tien_do'] ?? 50);
                        ?>
                        <div class="progress-item">
                            <div class="progress-icon"><i class="fas fa-book"></i></div>
                            <div class="progress-info">
                                <div class="progress-title"><?= sanitize($progress['ten_bai_hoc'] ?? __('lesson') . ' #' . $progress['ma_bai_hoc']) ?></div>
                                <div class="progress-bar-wrapper">
                                    <div class="progress-bar-fill" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                            <span class="progress-status <?= $isCompleted ? 'completed' : 'in-progress' ?>">
                                <?= $isCompleted ? __('completed') : $percent . '%' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-book-open"></i></div>
                        <h3 class="empty-state-title"><?= __('not_started') ?></h3>
                        <p class="empty-state-text"><?= __('learn_page_desc') ?></p>
                        <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="empty-state-btn"><i class="fas fa-play"></i> <?= __('start') ?></a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Learning Groups -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="section-title-icon"><i class="fas fa-users"></i></span>
                            Nhóm học tập
                        </h2>
                    </div>
                    
                    <?php if (!empty($learningGroups)): ?>
                    <div class="groups-grid">
                        <?php 
                        $currentLang = getCurrentLang();
                        $isKhmer = ($currentLang === 'km');
                        foreach ($learningGroups as $group): 
                        ?>
                        <a href="<?= BASE_URL ?>/group_detail.php?id=<?= $group['ma_nhom'] ?>" class="group-item">
                            <div class="group-icon-wrapper">
                                <i class="<?= $group['icon'] ?? 'fas fa-users' ?>"></i>
                            </div>
                            <div class="group-info">
                                <div class="group-name"><?= sanitize($isKhmer && !empty($group['ten_nhom_km']) ? $group['ten_nhom_km'] : $group['ten_nhom']) ?></div>
                                <div class="group-members"><i class="fas fa-user-friends"></i> <?= number_format($group['so_thanh_vien']) ?> thành viên</div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                        <h3 class="empty-state-title">Chưa có nhóm học tập</h3>
                        <p class="empty-state-text">Tham gia nhóm để học cùng cộng đồng</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
