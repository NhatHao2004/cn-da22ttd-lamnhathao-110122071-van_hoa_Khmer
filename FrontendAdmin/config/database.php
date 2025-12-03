<?php
/**
 * CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU
 * File: FrontendAdmin/config/database.php
 */

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Class Database - Singleton Pattern
 * Quản lý kết nối PDO đến MySQL
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Lỗi kết nối database: " . $e->getMessage());
        }
    }
    
    /**
     * Lấy instance của Database (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Lấy connection PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Thực hiện query SELECT và trả về nhiều dòng
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Thực hiện query SELECT và trả về 1 dòng
     */
    public function querySingle($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Thực hiện INSERT, UPDATE, DELETE
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log("Database Execute Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy ID của bản ghi vừa insert
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Bắt đầu transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollback();
    }
    
    /**
     * Đếm số lượng bản ghi
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->querySingle($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
}
