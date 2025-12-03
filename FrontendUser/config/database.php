<?php
/**
 * Database Configuration - Frontend User
 * Kết nối đến cùng database với Admin
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Class Database - Singleton Pattern
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
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
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
    
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log("Database Execute Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->querySingle($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    public function findById($table, $id) {
        $sql = "SELECT * FROM `$table` WHERE id = ?";
        return $this->querySingle($sql, [$id]);
    }
    
    public function update($table, $data, $id) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->execute($sql, $values);
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO `$table` (" . implode(', ', array_map(function($f) { return "`$f`"; }, $fields)) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        if ($this->execute($sql, $values)) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function delete($table, $id) {
        $sql = "DELETE FROM `$table` WHERE id = ?";
        return $this->execute($sql, [$id]);
    }
}
