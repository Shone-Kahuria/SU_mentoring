<?php
/**
 * Database Connection (db.php)
 * Simplified database connection file for modular structure
 */

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'mentoring_website',
    'username' => 'root',
    'password' => 'skahush254',
    'charset' => 'utf8mb4'
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
    die("Database connection failed. Please check your configuration.");
}

/**
 * Execute a prepared statement
 */
function db_query($sql, $params = []) {
    global $pdo;
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
 * Insert and return last insert ID
 */
function db_insert($table, $data) {
    global $pdo;
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = db_query($sql, $data);
    
    return $stmt ? $pdo->lastInsertId() : false;
}

/**
 * Update records
 */
function db_update($table, $data, $where, $where_params = []) {
    $set_parts = [];
    foreach (array_keys($data) as $column) {
        $set_parts[] = "{$column} = :{$column}";
    }
    $set_clause = implode(', ', $set_parts);
    
    $sql = "UPDATE {$table} SET {$set_clause} WHERE {$where}";
    $params = array_merge($data, $where_params);
    
    return db_query($sql, $params) !== false;
}

/**
 * Select multiple records
 */
function db_select($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Select single record
 */
function db_select_one($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}
?>