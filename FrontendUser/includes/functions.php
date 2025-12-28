<?php
/**
 * Helper Functions
 * Cập nhật theo cấu trúc database van_hoa_khmer
 */

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 * Hỗ trợ cả 2 cách lưu session: user_id và user['ma_nguoi_dung']
 */
function isLoggedIn() {
    // Cách 1: user_id trực tiếp
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Cách 2: user array với ma_nguoi_dung
    if (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        // Đồng bộ sang user_id
        $_SESSION['user_id'] = $_SESSION['user']['ma_nguoi_dung'];
        return true;
    }
    
    // Cách 3: logged_in flag
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    
    return false;
}

/**
 * Get current user
 * Hỗ trợ cả 2 cách lưu session
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    // Lấy user_id từ session
    $userId = null;
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } elseif (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        $userId = $_SESSION['user']['ma_nguoi_dung'];
    }
    
    if (!$userId) return null;
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}


/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        
        return "<div class='alert alert-{$type}'>{$message}</div>";
    }
    return '';
}

/**
 * Format date Vietnamese
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Format number
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get time ago
 */
function timeAgo($datetime) {
    if (!$datetime) return '';
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    if ($diff < 2592000) return floor($diff / 604800) . ' tuần trước';
    
    return formatDate($datetime);
}

/**
 * Generate slug
 */
function generateSlug($string) {
    $slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/\s+/', '-', $slug);
    return $slug;
}

/**
 * Upload image
 */
function uploadImage($file, $folder = 'uploads') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Không có file được upload'];
    }
    
    // Check file type using finfo for better security
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF, WEBP'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Build correct file system path
    // Go up from includes folder to FrontendUser, then up to DoAn_ChuyenNganh, then to uploads
    $baseDir = dirname(dirname(__DIR__)); // Go to DoAn_ChuyenNganh folder
    $uploadDir = $baseDir . '/uploads/' . $folder . '/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'message' => 'Không thể tạo thư mục upload'];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'message' => 'Thư mục upload không có quyền ghi'];
    }
    
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Không thể upload file. Vui lòng thử lại'];
}

/**
 * Add user points
 */
function addUserPoints($userId, $points, $reason = '') {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?");
    $stmt->execute([$points, $userId]);
    
    // Log activity (only if table exists)
    try {
        logActivity($userId, 'earn_points', "Nhận $points điểm: $reason");
    } catch (Exception $e) {
        // Ignore if activity log table doesn't exist
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Log user activity
 */
function logActivity($userId, $type, $description) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO nhat_ky_hoat_dong (ma_nguoi_dung, loai_nguoi_dung, hanh_dong, mo_ta, ip_address, ngay_tao) VALUES (?, 'nguoi_dung', ?, ?, ?, NOW())");
    $stmt->execute([$userId, $type, $description, $_SERVER['REMOTE_ADDR'] ?? '']);
}

/**
 * Check and award badges automatically
 */
function checkBadges($userId) {
    $pdo = getDBConnection();
    
    try {
        // Get user stats
        $stmt = $pdo->prepare("
            SELECT 
                nd.tong_diem,
                (SELECT COUNT(*) FROM tien_trinh_hoc_tap WHERE ma_nguoi_dung = ? AND trang_thai = 'hoan_thanh') as lessons_completed,
                (SELECT COUNT(*) FROM yeu_thich WHERE ma_nguoi_dung = ?) as saved_items,
                (SELECT COUNT(*) FROM ket_qua_quiz WHERE ma_nguoi_dung = ?) as quiz_completed,
                (SELECT COALESCE(SUM(diem), 0) FROM ket_qua_quiz WHERE ma_nguoi_dung = ?) as quiz_total_score
            FROM nguoi_dung nd 
            WHERE nd.ma_nguoi_dung = ?
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $stats = $stmt->fetch();
        
        if (!$stats) return [];
        
        $newBadges = [];
        
        // Define badge conditions
        $badgeConditions = [
            1 => ['name' => 'Người mới bắt đầu', 'check' => $stats['lessons_completed'] >= 1],
            2 => ['name' => 'Siêng năng', 'check' => false], // Cần logic streak - tạm thời false
            3 => ['name' => 'Học giỏi', 'check' => $stats['lessons_completed'] >= 10],
            4 => ['name' => 'Chuyên gia', 'check' => $stats['lessons_completed'] >= 20],
            5 => ['name' => 'Bậc thầy', 'check' => $stats['lessons_completed'] >= 50],
            6 => ['name' => 'Người khám phá', 'check' => $stats['saved_items'] >= 10],
            7 => ['name' => 'Người đam mê', 'check' => $stats['saved_items'] >= 5],
            8 => ['name' => 'Cao thủ Quiz', 'check' => $stats['quiz_completed'] >= 10 && $stats['quiz_total_score'] >= 100],
        ];
        
        foreach ($badgeConditions as $badgeId => $condition) {
            if ($condition['check']) {
                // Check if already has badge
                $checkStmt = $pdo->prepare("SELECT ma_hh_nguoi_dung FROM huy_hieu_nguoi_dung WHERE ma_nguoi_dung = ? AND ma_huy_hieu = ?");
                $checkStmt->execute([$userId, $badgeId]);
                
                if (!$checkStmt->fetch()) {
                    // Award badge
                    $insertStmt = $pdo->prepare("INSERT INTO huy_hieu_nguoi_dung (ma_nguoi_dung, ma_huy_hieu, ngay_dat_duoc) VALUES (?, ?, NOW())");
                    $insertStmt->execute([$userId, $badgeId]);
                    
                    // Get badge info
                    $badgeInfo = $pdo->prepare("SELECT ten_huy_hieu, diem_thuong FROM huy_hieu WHERE ma_huy_hieu = ?");
                    $badgeInfo->execute([$badgeId]);
                    $badge = $badgeInfo->fetch();
                    
                    if ($badge) {
                        // Add bonus points
                        if ($badge['diem_thuong'] > 0) {
                            addUserPoints($userId, $badge['diem_thuong'], "Đạt huy hiệu: " . $badge['ten_huy_hieu']);
                        }
                        
                        // Log activity
                        logActivity($userId, 'earn_badge', "Đạt huy hiệu: " . $badge['ten_huy_hieu']);
                        
                        $newBadges[] = $badge['ten_huy_hieu'];
                    }
                }
            }
        }
        
        return $newBadges;
        
    } catch (Exception $e) {
        error_log("checkBadges error: " . $e->getMessage());
        return [];
    }
}
?>
