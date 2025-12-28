<?php
/**
 * Đăng xuất - Unified Design
 */
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Destroy session completely
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to home - use relative path
header('Location: /DoAn_ChuyenNganh/FrontendUser/index.php');
exit;
?>
