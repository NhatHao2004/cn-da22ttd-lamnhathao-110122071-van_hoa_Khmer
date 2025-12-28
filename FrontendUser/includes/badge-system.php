<?php
/**
 * Badge System - Hệ thống quản lý huy hiệu tự động
 * Tự động kiểm tra và cấp huy hiệu khi người dùng đạt thành tựu
 */

/**
 * Danh sách tất cả các huy hiệu trong hệ thống
 */
function getAllBadgeDefinitions() {
    return [
        // ===== HUY HIỆU QUIZ =====
        'quiz_master' => [
            'name' => 'Quiz Master',
            'name_vi' => 'Bậc Thầy Quiz',
            'description' => 'Đạt điểm tuyệt đối (100%) trong một bài quiz',
            'icon' => 'fa-star',
            'color' => '#fbbf24',
            'bonus_points' => 50,
            'category' => 'quiz'
        ],
        'quiz_enthusiast' => [
            'name' => 'Quiz Enthusiast',
            'name_vi' => 'Người Yêu Quiz',
            'description' => 'Hoàn thành 5 bài quiz',
            'icon' => 'fa-clipboard-check',
            'color' => '#10b981',
            'bonus_points' => 30,
            'category' => 'quiz'
        ],
        'quiz_expert' => [
            'name' => 'Quiz Expert',
            'name_vi' => 'Chuyên Gia Quiz',
            'description' => 'Hoàn thành 10 bài quiz',
            'icon' => 'fa-award',
            'color' => '#8b5cf6',
            'bonus_points' => 100,
            'category' => 'quiz'
        ],
        'quiz_legend' => [
            'name' => 'Quiz Legend',
            'name_vi' => 'Huyền Thoại Quiz',
            'description' => 'Hoàn thành 20 bài quiz',
            'icon' => 'fa-crown',
            'color' => '#f59e0b',
            'bonus_points' => 200,
            'category' => 'quiz'
        ],
        'perfect_streak' => [
            'name' => 'Perfect Streak',
            'name_vi' => 'Chuỗi Hoàn Hảo',
            'description' => 'Đạt 100% trong 3 bài quiz liên tiếp',
            'icon' => 'fa-fire',
            'color' => '#ef4444',
            'bonus_points' => 150,
            'category' => 'quiz'
        ],
        
        // ===== HUY HIỆU BÀI HỌC =====
        'first_lesson' => [
            'name' => 'First Steps',
            'name_vi' => 'Bước Đầu Tiên',
            'description' => 'Hoàn thành bài học đầu tiên',
            'icon' => 'fa-graduation-cap',
            'color' => '#3b82f6',
            'bonus_points' => 20,
            'category' => 'lesson'
        ],
        'lesson_enthusiast' => [
            'name' => 'Eager Learner',
            'name_vi' => 'Học Viên Nhiệt Tình',
            'description' => 'Hoàn thành 5 bài học',
            'icon' => 'fa-book-reader',
            'color' => '#10b981',
            'bonus_points' => 50,
            'category' => 'lesson'
        ],
        'lesson_master' => [
            'name' => 'Knowledge Seeker',
            'name_vi' => 'Người Tìm Kiếm Tri Thức',
            'description' => 'Hoàn thành 10 bài học',
            'icon' => 'fa-brain',
            'color' => '#8b5cf6',
            'bonus_points' => 100,
            'category' => 'lesson'
        ],
        'lesson_expert' => [
            'name' => 'Scholar',
            'name_vi' => 'Học Giả',
            'description' => 'Hoàn thành 20 bài học',
            'icon' => 'fa-user-graduate',
            'color' => '#f59e0b',
            'bonus_points' => 200,
            'category' => 'lesson'
        ],
        
        // ===== HUY HIỆU ĐIỂM SỐ =====
        'point_collector_100' => [
            'name' => 'Point Collector',
            'name_vi' => 'Người Thu Thập Điểm',
            'description' => 'Đạt 100 điểm tổng',
            'icon' => 'fa-coins',
            'color' => '#fbbf24',
            'bonus_points' => 25,
            'category' => 'points'
        ],
        'point_master_500' => [
            'name' => 'Point Master',
            'name_vi' => 'Bậc Thầy Điểm Số',
            'description' => 'Đạt 500 điểm tổng',
            'icon' => 'fa-gem',
            'color' => '#3b82f6',
            'bonus_points' => 100,
            'category' => 'points'
        ],
        'point_legend_1000' => [
            'name' => 'Point Legend',
            'name_vi' => 'Huyền Thoại Điểm Số',
            'description' => 'Đạt 1000 điểm tổng',
            'icon' => 'fa-trophy',
            'color' => '#f59e0b',
            'bonus_points' => 250,
            'category' => 'points'
        ],
        
        // ===== HUY HIỆU HOẠT ĐỘNG =====
        'early_bird' => [
            'name' => 'Early Bird',
            'name_vi' => 'Chim Sớm',
            'description' => 'Đăng nhập vào buổi sáng (6h-9h) 5 ngày liên tiếp',
            'icon' => 'fa-sun',
            'color' => '#fbbf24',
            'bonus_points' => 50,
            'category' => 'activity'
        ],
        'night_owl' => [
            'name' => 'Night Owl',
            'name_vi' => 'Cú Đêm',
            'description' => 'Học vào ban đêm (22h-2h) 5 lần',
            'icon' => 'fa-moon',
            'color' => '#6366f1',
            'bonus_points' => 50,
            'category' => 'activity'
        ],
        'daily_streak_7' => [
            'name' => 'Week Warrior',
            'name_vi' => 'Chiến Binh Tuần',
            'description' => 'Học liên tục 7 ngày',
            'icon' => 'fa-calendar-check',
            'color' => '#10b981',
            'bonus_points' => 75,
            'category' => 'activity'
        ],
        'daily_streak_30' => [
            'name' => 'Month Master',
            'name_vi' => 'Bậc Thầy Tháng',
            'description' => 'Học liên tục 30 ngày',
            'icon' => 'fa-calendar-alt',
            'color' => '#f59e0b',
            'bonus_points' => 300,
            'category' => 'activity'
        ],
        
        // ===== HUY HIỆU ĐẶC BIỆT =====
        'culture_explorer' => [
            'name' => 'Culture Explorer',
            'name_vi' => 'Nhà Thám Hiểm Văn Hóa',
            'description' => 'Đọc 10 bài viết văn hóa',
            'icon' => 'fa-compass',
            'color' => '#3b82f6',
            'bonus_points' => 50,
            'category' => 'special'
        ],
        'temple_visitor' => [
            'name' => 'Temple Visitor',
            'name_vi' => 'Người Viếng Chùa',
            'description' => 'Xem thông tin 5 ngôi chùa',
            'icon' => 'fa-place-of-worship',
            'color' => '#f59e0b',
            'bonus_points' => 40,
            'category' => 'special'
        ],
        'festival_fan' => [
            'name' => 'Festival Fan',
            'name_vi' => 'Người Yêu Lễ Hội',
            'description' => 'Tìm hiểu về 5 lễ hội Khmer',
            'icon' => 'fa-calendar-star',
            'color' => '#ec4899',
            'bonus_points' => 40,
            'category' => 'special'
        ],
        'story_reader' => [
            'name' => 'Story Reader',
            'name_vi' => 'Người Đọc Truyện',
            'description' => 'Đọc 5 truyện dân gian',
            'icon' => 'fa-book-open',
            'color' => '#8b5cf6',
            'bonus_points' => 40,
            'category' => 'special'
        ],
        'community_helper' => [
            'name' => 'Community Helper',
            'name_vi' => 'Người Giúp Đỡ Cộng Đồng',
            'description' => 'Viết 10 bình luận hữu ích',
            'icon' => 'fa-hands-helping',
            'color' => '#10b981',
            'bonus_points' => 60,
            'category' => 'special'
        ]
    ];
}

/**
 * Kiểm tra và cấp huy hiệu cho người dùng
 */
function checkAndAwardBadges($pdo, $userId, $context = 'all') {
    $badges = getAllBadgeDefinitions();
    $awardedBadges = [];
    
    try {
        // Lấy thống kê người dùng
        $stats = getUserStats($pdo, $userId);
        
        foreach ($badges as $badgeCode => $badgeInfo) {
            // Kiểm tra context (chỉ check badge liên quan)
            if ($context !== 'all' && $badgeInfo['category'] !== $context) {
                continue;
            }
            
            // Kiểm tra điều kiện đạt huy hiệu
            if (checkBadgeCondition($badgeCode, $stats)) {
                $awarded = awardBadgeIfNotExists(
                    $pdo, 
                    $userId, 
                    $badgeCode, 
                    $badgeInfo['name_vi'], 
                    $badgeInfo['description'], 
                    $badgeInfo['icon'], 
                    $badgeInfo['color'], 
                    $badgeInfo['bonus_points']
                );
                
                if ($awarded) {
                    $awardedBadges[] = $badgeInfo;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Badge check error: " . $e->getMessage());
    }
    
    return $awardedBadges;
}

/**
 * Lấy thống kê người dùng để kiểm tra huy hiệu
 */
function getUserStats($pdo, $userId) {
    $stats = [
        'total_points' => 0,
        'quiz_count' => 0,
        'quiz_perfect_count' => 0,
        'quiz_recent_perfect_streak' => 0,
        'lesson_count' => 0,
        'culture_read_count' => 0,
        'temple_view_count' => 0,
        'festival_view_count' => 0,
        'story_read_count' => 0,
        'comment_count' => 0,
        'daily_streak' => 0
    ];
    
    try {
        // Tổng điểm
        $pointsStmt = $pdo->prepare("SELECT tong_diem FROM nguoi_dung WHERE ma_nguoi_dung = ?");
        $pointsStmt->execute([$userId]);
        $stats['total_points'] = $pointsStmt->fetchColumn() ?: 0;
        
        // Số quiz đã hoàn thành (chỉ văn hóa và chùa)
        $quizTables = ['ket_qua_quiz', 'ket_qua_quiz_chua'];
        foreach ($quizTables as $table) {
            $checkTable = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($checkTable->rowCount() > 0) {
                $qStmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE ma_nguoi_dung = ?");
                $qStmt->execute([$userId]);
                $stats['quiz_count'] += $qStmt->fetchColumn();
                
                // Đếm số quiz đạt 100%
                $perfectStmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE ma_nguoi_dung = ? AND so_cau_dung = tong_so_cau");
                $perfectStmt->execute([$userId]);
                $stats['quiz_perfect_count'] += $perfectStmt->fetchColumn();
            }
        }
        
        // Số bài học đã hoàn thành
        $lessonStmt = $pdo->prepare("SELECT COUNT(*) FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND trang_thai = 'hoan_thanh'");
        $lessonStmt->execute([$userId]);
        $stats['lesson_count'] = $lessonStmt->fetchColumn() ?: 0;
        
        // Số bình luận
        try {
            $checkCommentTable = $pdo->query("SHOW TABLES LIKE 'binh_luan'");
            if ($checkCommentTable->rowCount() > 0) {
                $commentStmt = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE ma_nguoi_dung = ? AND trang_thai = 'hien_thi'");
                $commentStmt->execute([$userId]);
                $stats['comment_count'] = $commentStmt->fetchColumn() ?: 0;
            }
        } catch (Exception $e) {
            error_log("Comment count error: " . $e->getMessage());
        }
        
        // Chuỗi ngày học liên tục (daily streak)
        try {
            $checkActivityTable = $pdo->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
            if ($checkActivityTable->rowCount() > 0) {
                $stats['daily_streak'] = calculateDailyStreak($pdo, $userId);
            }
        } catch (Exception $e) {
            error_log("Daily streak error: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        error_log("Get user stats error: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Kiểm tra điều kiện đạt huy hiệu
 */
function checkBadgeCondition($badgeCode, $stats) {
    switch ($badgeCode) {
        // Quiz badges
        case 'quiz_master':
            return $stats['quiz_perfect_count'] >= 1;
        case 'quiz_enthusiast':
            return $stats['quiz_count'] >= 5;
        case 'quiz_expert':
            return $stats['quiz_count'] >= 10;
        case 'quiz_legend':
            return $stats['quiz_count'] >= 20;
        case 'perfect_streak':
            return $stats['quiz_recent_perfect_streak'] >= 3;
            
        // Lesson badges
        case 'first_lesson':
            return $stats['lesson_count'] >= 1;
        case 'lesson_enthusiast':
            return $stats['lesson_count'] >= 5;
        case 'lesson_master':
            return $stats['lesson_count'] >= 10;
        case 'lesson_expert':
            return $stats['lesson_count'] >= 20;
            
        // Points badges
        case 'point_collector_100':
            return $stats['total_points'] >= 100;
        case 'point_master_500':
            return $stats['total_points'] >= 500;
        case 'point_legend_1000':
            return $stats['total_points'] >= 1000;
            
        // Activity badges
        case 'daily_streak_7':
            return $stats['daily_streak'] >= 7;
        case 'daily_streak_30':
            return $stats['daily_streak'] >= 30;
            
        // Special badges
        case 'culture_explorer':
            return $stats['culture_read_count'] >= 10;
        case 'temple_visitor':
            return $stats['temple_view_count'] >= 5;
        case 'festival_fan':
            return $stats['festival_view_count'] >= 5;
        case 'story_reader':
            return $stats['story_read_count'] >= 5;
        case 'community_helper':
            return $stats['comment_count'] >= 10;
            
        default:
            return false;
    }
}

/**
 * Tính chuỗi ngày học liên tục
 */
function calculateDailyStreak($pdo, $userId) {
    try {
        // Lấy các ngày có hoạt động (quiz hoặc lesson)
        $activityStmt = $pdo->prepare("
            SELECT DISTINCT DATE(ngay_tao) as activity_date 
            FROM nhat_ky_hoat_dong 
            WHERE ma_nguoi_dung = ? 
            AND loai_nguoi_dung = 'nguoi_dung'
            AND (hanh_dong = 'quiz_complete' OR hanh_dong = 'lesson_complete')
            ORDER BY activity_date DESC
            LIMIT 100
        ");
        $activityStmt->execute([$userId]);
        $dates = $activityStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($dates)) {
            return 0;
        }
        
        $streak = 1;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Kiểm tra có hoạt động hôm nay hoặc hôm qua không
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0; // Chuỗi đã bị đứt
        }
        
        // Đếm chuỗi ngày liên tục
        for ($i = 1; $i < count($dates); $i++) {
            $currentDate = strtotime($dates[$i]);
            $previousDate = strtotime($dates[$i - 1]);
            $diff = ($previousDate - $currentDate) / 86400; // Chênh lệch ngày
            
            if ($diff == 1) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Cấp huy hiệu nếu chưa có
 */
function awardBadgeIfNotExists($pdo, $userId, $badgeCode, $badgeName, $description, $icon, $color, $bonusPoints) {
    try {
        // Kiểm tra huy hiệu đã tồn tại trong bảng huy_hieu chưa
        $checkStmt = $pdo->prepare("SELECT ma_huy_hieu FROM huy_hieu WHERE ten_huy_hieu = ?");
        $checkStmt->execute([$badgeName]);
        $badge = $checkStmt->fetch();
        
        if (!$badge) {
            // Tạo huy hiệu mới
            $createStmt = $pdo->prepare("
                INSERT INTO huy_hieu (ten_huy_hieu, mo_ta, icon, mau_sac, diem_thuong, trang_thai) 
                VALUES (?, ?, ?, ?, ?, 'hoat_dong')
            ");
            $createStmt->execute([$badgeName, $description, $icon, $color, $bonusPoints]);
            $badgeId = $pdo->lastInsertId();
        } else {
            $badgeId = $badge['ma_huy_hieu'];
        }
        
        // Kiểm tra user đã có huy hiệu chưa
        $userBadgeStmt = $pdo->prepare("SELECT 1 FROM huy_hieu_nguoi_dung WHERE ma_nguoi_dung = ? AND ma_huy_hieu = ?");
        $userBadgeStmt->execute([$userId, $badgeId]);
        
        if (!$userBadgeStmt->fetch()) {
            // Cấp huy hiệu
            $awardStmt = $pdo->prepare("INSERT INTO huy_hieu_nguoi_dung (ma_nguoi_dung, ma_huy_hieu) VALUES (?, ?)");
            $awardStmt->execute([$userId, $badgeId]);
            
            // Cộng điểm thưởng
            $pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?")->execute([$bonusPoints, $userId]);
            
            // Ghi log
            $logStmt = $pdo->prepare("
                INSERT INTO nhat_ky_hoat_dong (ma_nguoi_dung, loai_nguoi_dung, hanh_dong, mo_ta, ngay_tao) 
                VALUES (?, 'nguoi_dung', 'badge_earned', ?, NOW())
            ");
            $logStmt->execute([$userId, "Đạt huy hiệu: $badgeName (+$bonusPoints điểm)"]);
            
            return true; // Đã cấp huy hiệu mới
        }
        
        return false; // Đã có huy hiệu rồi
    } catch (Exception $e) {
        error_log("Award badge error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy danh sách huy hiệu của người dùng
 */
function getUserBadges($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT h.*, hn.ngay_dat_duoc 
            FROM huy_hieu h 
            JOIN huy_hieu_nguoi_dung hn ON h.ma_huy_hieu = hn.ma_huy_hieu 
            WHERE hn.ma_nguoi_dung = ? 
            ORDER BY hn.ngay_dat_duoc DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Kiểm tra huy hiệu sau khi hoàn thành quiz
 */
function checkBadgesAfterQuiz($pdo, $userId) {
    return checkAndAwardBadges($pdo, $userId, 'quiz');
}

/**
 * Kiểm tra huy hiệu sau khi hoàn thành bài học
 */
function checkBadgesAfterLesson($pdo, $userId) {
    return checkAndAwardBadges($pdo, $userId, 'lesson');
}

/**
 * Kiểm tra huy hiệu điểm số
 */
function checkBadgesAfterPointsUpdate($pdo, $userId) {
    return checkAndAwardBadges($pdo, $userId, 'points');
}
