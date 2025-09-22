<?php
/**
 * MariaDB Database Setup Script
 * Creates database and tables for the mentoring website
 */

echo "<h1>MariaDB Database Setup</h1>";

// Database configuration - update these values for your MariaDB setup
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',      // Change to your MariaDB username
    'password' => 'skahus254', // Updated with your MariaDB password
    'database' => 'mentoring_website',
    'charset' => 'utf8mb4'
];

echo "<h2>1. Database Configuration</h2>";
echo "<p>Host: " . htmlspecialchars($config['host']) . ":" . $config['port'] . "</p>";
echo "<p>Username: " . htmlspecialchars($config['username']) . "</p>";
echo "<p>Database: " . htmlspecialchars($config['database']) . "</p>";

try {
    // First, connect without specifying database to create it
    echo "<h2>2. Connecting to MariaDB Server</h2>";
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p>✅ Connected to MariaDB server successfully</p>";
    
    // Create database if it doesn't exist
    echo "<h2>3. Creating Database</h2>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET {$config['charset']} COLLATE {$config['charset']}_unicode_ci");
    echo "<p>✅ Database '{$config['database']}' created or already exists</p>";
    
    // Use the database
    $pdo->exec("USE `{$config['database']}`");
    echo "<p>✅ Using database '{$config['database']}'</p>";
    
    // Read SQL schema file
    echo "<h2>4. Loading Database Schema</h2>";
    $schemaFile = __DIR__ . '/database_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Remove the database creation commands since we've already done that
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE .*?;/i', '', $sql);
    
    // Split the SQL into individual statements
    $statements = [];
    $currentStatement = '';
    $inDelimiter = false;
    
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        // Handle DELIMITER changes
        if (strpos($line, 'DELIMITER') === 0) {
            $inDelimiter = !$inDelimiter;
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Check for statement end
        if (!$inDelimiter && (substr($line, -1) === ';' || substr($line, -2) === '//')) {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    echo "<p>Found " . count($statements) . " SQL statements to execute</p>";
    
    // Execute statements
    echo "<h2>5. Creating Tables and Data</h2>";
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $executed++;
            
            // Show progress for major operations
            if (stripos($statement, 'CREATE TABLE') === 0) {
                preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "<p>✅ Created table: $tableName</p>";
            } elseif (stripos($statement, 'INSERT INTO') === 0) {
                preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "<p>✅ Inserted data into: $tableName</p>";
            } elseif (stripos($statement, 'CREATE VIEW') === 0) {
                preg_match('/CREATE VIEW\s+(\w+)/i', $statement, $matches);
                $viewName = $matches[1] ?? 'unknown';
                echo "<p>✅ Created view: $viewName</p>";
            } elseif (stripos($statement, 'CREATE PROCEDURE') === 0) {
                preg_match('/CREATE PROCEDURE\s+(\w+)/i', $statement, $matches);
                $procName = $matches[1] ?? 'unknown';
                echo "<p>✅ Created procedure: $procName</p>";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "<p>⚠️ Warning executing statement: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Statement: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
        }
    }
    
    echo "<h2>6. Database Setup Summary</h2>";
    echo "<p>✅ Executed $executed statements successfully</p>";
    if ($errors > 0) {
        echo "<p>⚠️ $errors statements had warnings (this may be normal)</p>";
    }
    
    // Update the config.php file with the database settings
    echo "<h2>7. Updating Configuration</h2>";
    $configFile = __DIR__ . '/includes/config.php';
    $configContent = file_get_contents($configFile);
    
    // Update database credentials if they're different
    if ($config['username'] !== 'root' || !empty($config['password'])) {
        $configContent = preg_replace(
            "/define\('DB_USER', '[^']*'\);/", 
            "define('DB_USER', '{$config['username']}');", 
            $configContent
        );
        $configContent = preg_replace(
            "/define\('DB_PASS', '[^']*'\);/", 
            "define('DB_PASS', '{$config['password']}');", 
            $configContent
        );
        file_put_contents($configFile, $configContent);
        echo "<p>✅ Updated configuration file with database credentials</p>";
    } else {
        echo "<p>✅ Configuration file is already correct</p>";
    }
    
    // Test the application database connection
    echo "<h2>8. Testing Application Connection</h2>";
    require_once 'includes/functions.php';
    $testPdo = getDBConnection();
    $result = $testPdo->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "<p>✅ Application can connect to database</p>";
    echo "<p>✅ Users table has {$result['count']} records</p>";
    
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Ready to Use!</h3>";
    echo "<p>Your MariaDB database is now set up and ready for the mentoring website.</p>";
    echo "<p><strong>Default Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li>Email: admin@mentoring.com</li>";
    echo "<li>Password: Admin123!</li>";
    echo "</ul>";
    echo "<p><a href='index.php' class='btn btn-primary'>Go to Website</a> ";
    echo "<a href='test_database.php' class='btn btn-secondary'>Test Database</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Common Solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure MariaDB/MySQL is running</li>";
    echo "<li>Check your database credentials above</li>";
    echo "<li>Ensure the database user has CREATE privileges</li>";
    echo "<li>Verify the host and port are correct</li>";
    echo "</ul>";
    echo "<p>Update the configuration at the top of this file and try again.</p>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
</style>