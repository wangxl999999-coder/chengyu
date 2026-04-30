<?php
/**
 * 管理后台配置文件
 */

// 数据库配置（与后端共用）
define('DB_HOST', 'localhost');
define('DB_NAME', 'chengyu');
define('DB_USER', 'root');
define('DB_PASS', '123123');
define('DB_CHARSET', 'utf8mb4');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 会话设置
session_start();

/**
 * 检查登录状态
 */
function check_login() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * 数据库连接类
 */
class AdminDatabase {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $keys) . "`) VALUES ({$placeholders})";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "`{$key}` = ?";
        }
        $sql = "UPDATE `{$table}` SET " . implode(',', $sets) . " WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * 获取总数
     */
    public function count($table, $where = '1=1', $params = []) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as `count` FROM `{$table}` WHERE {$where}");
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}

/**
 * 获取数据库实例
 */
function admin_db() {
    return AdminDatabase::getInstance();
}

/**
 * JSON响应
 */
function json_response($success, $message = '', $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
