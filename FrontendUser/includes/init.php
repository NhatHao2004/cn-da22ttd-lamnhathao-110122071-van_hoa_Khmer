<?php
/**
 * Initialization File - Auto-load for all pages
 * Includes: Session, Database, Language, Functions
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/DoAn_ChuyenNganh/FrontendUser');
define('UPLOAD_PATH', '/DoAn_ChuyenNganh/uploads/');

// Include core files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/language.php';
require_once BASE_PATH . '/includes/functions.php';

// Get current user if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getInstance();
        $currentUser = $db->querySingle(
            "SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?",
            [$_SESSION['user_id']]
        );
    } catch (Exception $e) {
        // Silent fail
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Sanitize output
 */
function sanitize($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Get excerpt
 */
function getExcerpt($text, $length = 150) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}
?>
