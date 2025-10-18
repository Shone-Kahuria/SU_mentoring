<?php
/**
 * Database Setup Script
 * Run this script to create the database schema and initial data
 */

require_once 'includes/config.php';

echo "<h2>SU Mentoring - Database Setup</h2>\n";

try {
    // Read the SQL schema file
    $sqlFile = __DIR__ . '/database_schema.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Database schema file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read database schema file");
    }
    
    // Use hardcoded database config for setup (fallback if config not loaded)
    $db_host = isset($config['db_host']) ? $config['db_host'] : 'localhost';
    $db_username = isset($config['db_username']) ? $config['db_username'] : 'root';
    $db_password = isset($config['db_password']) ? $config['db_password'] : 'skahush254';
    $db_name = isset($config['db_name']) ? $config['db_name'] : 'mentoring_website';
    
    // Connect to MySQL without specifying database (to create it)
    $dsn = "mysql:host={$db_host};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p>✅ Connected to MySQL server successfully</p>\n";
    
    // Split SQL into individual statements and execute
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt) &&
                   !preg_match('/^DELIMITER/', $stmt);
        }
    );
    
    $executedCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                $pdo->exec($statement);
                $executedCount++;
            } catch (PDOException $e) {
                // Some statements may fail (like DROP DATABASE IF EXISTS when it doesn't exist)
                // We'll continue with others
                if (strpos($e->getMessage(), 'DROP DATABASE') === false) {
                    echo "<p>⚠️ Warning executing statement: " . htmlspecialchars($e->getMessage()) . "</p>\n";
                    $errorCount++;
                }
            }
        }
    }
    
    echo "<p>✅ Database schema setup completed!</p>\n";
    echo "<p>📊 Executed {$executedCount} SQL statements</p>\n";
    
    if ($errorCount > 0) {
        echo "<p>⚠️ {$errorCount} warnings/errors occurred (see above)</p>\n";
    }
    
    // Test the connection to the new database
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $testPdo = new PDO($dsn, $db_username, $db_password);
    
    // Test with a simple query
    $stmt = $testPdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    
    echo "<p>✅ Database connection test successful</p>\n";
    echo "<p>👥 Total users in database: {$result['user_count']}</p>\n";
    
    // Show default login credentials
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>🔑 Default Login Credentials</h3>\n";
    echo "<p><strong>Admin Account:</strong></p>\n";
    echo "<p>Email: <code>admin@mentoring.local</code></p>\n";
    echo "<p>Password: <code>password</code></p>\n";
    echo "<br>\n";
    echo "<p><strong>Sample Mentor:</strong></p>\n";
    echo "<p>Email: <code>jane.smith@mentoring.local</code></p>\n";
    echo "<p>Password: <code>password</code></p>\n";
    echo "<br>\n";
    echo "<p><strong>Sample Mentee:</strong></p>\n";
    echo "<p>Email: <code>mary.doe@mentoring.local</code></p>\n";
    echo "<p>Password: <code>password</code></p>\n";
    echo "<p><em>⚠️ Remember to change these passwords after first login!</em></p>\n";
    echo "</div>\n";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>📋 Next Steps</h3>\n";
    echo "<ol>\n";
    echo "<li>Change default passwords for all accounts</li>\n";
    echo "<li>Update email settings in <code>includes/.env.php</code></li>\n";
    echo "<li>Configure your web server to point to this directory</li>\n";
    echo "<li>Test the application by visiting <code>pages/login.php</code></li>\n";
    echo "<li>Review security settings and SSL certificate</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>\n";
    echo "<h3>❌ Database Setup Failed</h3>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Please check:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Database server is running</li>\n";
    echo "<li>Database credentials in <code>includes/.env.php</code> are correct</li>\n";
    echo "<li>Database user has sufficient privileges</li>\n";
    echo "<li>MySQL version is 5.7+ or MariaDB 10.2+</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - SU Mentoring</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 40px auto; 
            padding: 20px; 
            line-height: 1.6;
        }
        code { 
            background: #f4f4f4; 
            padding: 2px 5px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin: 30px 0;">
        <h3>🚀 Ready to Start?</h3>
        <a href="pages/login.php" class="btn">Go to Login Page</a>
        <a href="pages/home.php" class="btn">View Home Page</a>
        <a href="pages/signup.php" class="btn">Create New Account</a>
    </div>
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">
        <p><strong>🗄️ Database Schema Information</strong></p>
        <p>Total Tables: <strong>8</strong> | Views: <strong>0</strong> | Stored Procedures: <strong>0</strong></p>
        <p>Features: User Management, Mentorships, Sessions, Messages, Activity Logs, Password Resets</p>
    </div>
</body>
</html>