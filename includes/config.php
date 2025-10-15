<?php
/**
 * Database Configuration for Mentoring Website
 * 
 * This file contains the database connection settings and utility functions
 * for the mentoring website project.
 */

// Load secure configuration
require_once __DIR__ . '/config_loader.php';
ConfigLoader::load();

// Legacy constants for backward compatibility
// These are now loaded from .env.php file
if (!defined('DB_HOST')) {
    // Fallback values (should not be used in production)
    define('DB_HOST', ConfigLoader::get('DB_HOST', 'localhost'));
    define('DB_NAME', ConfigLoader::get('DB_NAME', 'mentoring_website'));
    define('DB_USER', ConfigLoader::get('DB_USER', 'root'));
    define('DB_PASS', ConfigLoader::get('DB_PASS', 'skahush254'));
    define('DB_CHARSET', ConfigLoader::get('DB_CHARSET', 'utf8mb4'));
}

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDBConnection() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute a prepared statement with parameters
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement|false
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . json_encode($params));
        return false;
    }
}

/**
 * Insert a new record and return the ID
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|false Last insert ID or false on failure
 */
function insertRecord($table, $data) {
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = executeQuery($sql, $data);
        
        if ($stmt) {
            return getDBConnection()->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        error_log("Insert failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a record
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $where WHERE clause
 * @param array $whereParams Parameters for WHERE clause
 * @return bool Success status
 */
function updateRecord($table, $data, $where, $whereParams = []) {
    try {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = executeQuery($sql, $params);
        return $stmt !== false;
    } catch (Exception $e) {
        error_log("Update failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a record
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success status
 */
function deleteRecord($table, $where, $params = []) {
    try {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = executeQuery($sql, $params);
        return $stmt !== false;
    } catch (Exception $e) {
        error_log("Delete failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Select records from database
 * @param string $sql SQL SELECT query
 * @param array $params Parameters to bind
 * @return array|false Array of records or false on failure
 */
function selectRecords($sql, $params = []) {
    try {
        $stmt = executeQuery($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return false;
    } catch (Exception $e) {
        error_log("Select failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Select a single record
 * @param string $sql SQL SELECT query
 * @param array $params Parameters to bind
 * @return array|false Single record or false on failure
 */
function selectRecord($sql, $params = []) {
    try {
        $stmt = executeQuery($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    } catch (Exception $e) {
        error_log("Select failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a record exists
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool True if record exists
 */
function recordExists($table, $where, $params = []) {
    $sql = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
    $result = selectRecord($sql, $params);
    return $result !== false;
}

/**
 * Get count of records
 * @param string $table Table name
 * @param string $where WHERE clause (optional)
 * @param array $params Parameters for WHERE clause
 * @return int|false Count of records or false on failure
 */
function getRecordCount($table, $where = '', $params = []) {
    try {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        $result = selectRecord($sql, $params);
        return $result ? (int)$result['count'] : false;
    } catch (Exception $e) {
        error_log("Count failed: " . $e->getMessage());
        return false;
    }
}
?>