<?php
/**
 * Database Configuration
 * Văn hóa Khmer Nam Bộ - Frontend User
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO Connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
        }
    }
    
    return $pdo;
}

// Base URL - Cập nhật theo đường dẫn thực tế
define('BASE_URL', '/DoAn_ChuyenNganh/FrontendUser');
define('UPLOAD_PATH', '/DoAn_ChuyenNganh/uploads/');
?>
