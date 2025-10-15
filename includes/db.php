<?php
/**
 * Database Connection (db.php)
 * Simplified database connection file for modular structure
 */

// Database configuration - try to load from config first
$db_config = [
    'host' => 'localhost',
    'dbname' => 'mentoring_website', 
    'username' => 'root',
    'password' => 'skahush254',
    'charset' => 'utf8mb4'
];

// Try to load from global config if available
if (isset($config)) {
    $db_config = [
        'host' => $config['db_host'] ?? $db_config['host'],
        'dbname' => $config['db_name'] ?? $db_config['dbname'],
        'username' => $config['db_username'] ?? $db_config['username'],
        'password' => $config['db_password'] ?? $db_config['password'],
        'charset' => $config['db_charset'] ?? $db_config['charset']
    ];
}

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