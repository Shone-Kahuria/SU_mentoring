<?php
/**
 * Database Connection (db.php)
 * Uses environment configuration - NEVER hardcode credentials here!
 */

// Load environment configuration
$envFile = __DIR__ . '/.env.php';
if (file_exists($envFile)) {
    require_once $envFile;
} else {
    // Fallback error if .env.php doesn't exist
    error_log("CRITICAL: .env.php file not found! Copy .env.example.php to includes/.env.php and configure it.");
    die("Configuration error. Please contact administrator. (Missing .env.php)");
}

// Database configuration from environment
$db_config = [
    'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
    'dbname' => defined('DB_NAME') ? DB_NAME : 'mentoring_website',
    'username' => defined('DB_USER') ? DB_USER : 'root',
    'password' => defined('DB_PASS') ? DB_PASS : '',
    'charset' => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // Don't die here, let the calling code handle the error gracefully
    $pdo = null;
}

/**
 * Execute a prepared statement and return statement object
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    
    if ($pdo === null) {
        error_log("Database connection not available");
        return false;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database query failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Select multiple records
 */
function selectRecords($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

/**
 * Select single record
 */
function selectRecord($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Insert record and return last insert ID
 */
function insertRecord($table, $data) {
    global $pdo;
    
    if ($pdo === null) {
        error_log("Database connection not available for insert");
        return false;
    }
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = executeQuery($sql, $data);
    
    return $stmt ? $pdo->lastInsertId() : false;
}

/**
 * Update records
 */
function updateRecord($table, $data, $where, $where_params = []) {
    $set_parts = [];
    foreach (array_keys($data) as $column) {
        $set_parts[] = "{$column} = :{$column}";
    }
    $set_clause = implode(', ', $set_parts);
    
    $sql = "UPDATE {$table} SET {$set_clause} WHERE {$where}";
    $params = array_merge($data, $where_params);
    
    return executeQuery($sql, $params) !== false;
}

/**
 * Delete records
 */
function deleteRecord($table, $where, $where_params = []) {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    return executeQuery($sql, $where_params) !== false;
}
?>