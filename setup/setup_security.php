#!/usr/bin/env php
<?php
/**
 * Security Setup Script for SU Mentoring
 * This script helps secure your repository and environment
 */

echo "🔒 SU MENTORING SECURITY SETUP\n";
echo "===============================\n\n";

$projectRoot = __DIR__;
$includesDir = $projectRoot . '/includes';

// Check if .env.php already exists
$envFile = $includesDir . '/.env.php';
$gitignoreFile = $projectRoot . '/.gitignore';
$configFile = $includesDir . '/config.php';

echo "1. Checking current security status...\n";

// Check .gitignore
if (file_exists($gitignoreFile)) {
    echo "   ✅ .gitignore file exists\n";
    $gitignoreContent = file_get_contents($gitignoreFile);
    if (strpos($gitignoreContent, '.env') !== false) {
        echo "   ✅ .gitignore protects environment files\n";
    } else {
        echo "   ⚠️  .gitignore may not protect environment files\n";
    }
} else {
    echo "   ❌ .gitignore file missing\n";
}

// Check if credentials are in git history
echo "\n2. Checking for credentials in current files...\n";
$credentialPatterns = [
    '/skahush254/',
    '/password\s*=\s*[\'"][^\'"]+[\'"]/',
    '/secret.*=.*[\'"][^\'"]+[\'"]/',
    '/api.*key.*=.*[\'"][^\'"]+[\'"]/'
];

$foundCredentials = [];
$phpFiles = glob($projectRoot . '/*.php');
$phpFiles = array_merge($phpFiles, glob($projectRoot . '/includes/*.php'));
$phpFiles = array_merge($phpFiles, glob($projectRoot . '/pages/*.php'));

foreach ($phpFiles as $file) {
    if (strpos($file, '.env.') !== false) continue; // Skip env files
    
    $content = file_get_contents($file);
    foreach ($credentialPatterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $foundCredentials[] = [
                'file' => basename($file),
                'match' => $matches[0]
            ];
        }
    }
}

if (!empty($foundCredentials)) {
    echo "   ⚠️  Found potential credentials in files:\n";
    foreach ($foundCredentials as $cred) {
        echo "      - {$cred['file']}: {$cred['match']}\n";
    }
} else {
    echo "   ✅ No obvious credentials found in code files\n";
}

echo "\n3. Setting up secure environment...\n";

// Create .env.php if it doesn't exist
if (!file_exists($envFile)) {
    echo "   📝 Creating secure environment file...\n";
    
    // Generate secure keys
    $secretKey = bin2hex(random_bytes(32));
    $jwtSecret = bin2hex(random_bytes(32));
    $encryptionKey = bin2hex(random_bytes(32));
    
    $envContent = "<?php\n";
    $envContent .= "/**\n * Secure Environment Configuration\n * This file is ignored by git and contains your actual credentials\n */\n\n";
    $envContent .= "// Database Configuration\n";
    $envContent .= "define('DB_HOST', 'localhost');\n";
    $envContent .= "define('DB_NAME', 'mentoring_website');\n";
    $envContent .= "define('DB_USER', 'root');\n";
    $envContent .= "define('DB_PASS', 'skahush254'); // TODO: Change this!\n";
    $envContent .= "define('DB_CHARSET', 'utf8mb4');\n";
    $envContent .= "define('DB_PORT', 3306);\n\n";
    $envContent .= "// Security Keys (auto-generated)\n";
    $envContent .= "define('APP_SECRET_KEY', '$secretKey');\n";
    $envContent .= "define('JWT_SECRET', '$jwtSecret');\n";
    $envContent .= "define('ENCRYPTION_KEY', '$encryptionKey');\n\n";
    $envContent .= "// Application Settings\n";
    $envContent .= "define('APP_ENV', 'development');\n";
    $envContent .= "define('APP_DEBUG', true);\n";
    $envContent .= "define('APP_URL', 'http://localhost/SU_mentoring');\n";
    
    file_put_contents($envFile, $envContent);
    echo "   ✅ Environment file created with secure keys\n";
} else {
    echo "   ✅ Environment file already exists\n";
}

echo "\n4. Git security recommendations...\n";

// Check git status
exec('git status --porcelain 2>&1', $gitOutput, $gitReturn);
if ($gitReturn === 0) {
    echo "   📊 Git repository detected\n";
    
    // Check if sensitive files are staged
    $stagedFiles = [];
    foreach ($gitOutput as $line) {
        if (preg_match('/^[AM]\s+(.+)$/', $line, $matches)) {
            $stagedFiles[] = $matches[1];
        }
    }
    
    $sensitiveStaged = [];
    foreach ($stagedFiles as $file) {
        if (strpos($file, '.env') !== false || 
            strpos($file, 'config.php') !== false ||
            strpos($file, 'password') !== false) {
            $sensitiveStaged[] = $file;
        }
    }
    
    if (!empty($sensitiveStaged)) {
        echo "   ⚠️  WARNING: Potentially sensitive files are staged:\n";
        foreach ($sensitiveStaged as $file) {
            echo "      - $file\n";
        }
        echo "   💡 Consider unstaging these files: git reset HEAD <file>\n";
    }
} else {
    echo "   ℹ️  Not a git repository or git not available\n";
}

echo "\n5. Security recommendations:\n";
echo "   📝 TODO LIST:\n";
echo "   ┌─────────────────────────────────────────────────────────────┐\n";
echo "   │ 1. Update your database password in includes/.env.php       │\n";
echo "   │ 2. Set up HTTPS and update APP_URL                         │\n";
echo "   │ 3. Configure email settings for production                 │\n";
echo "   │ 4. Review and remove any hardcoded credentials             │\n";
echo "   │ 5. Set up proper backups for your .env.php file           │\n";
echo "   │ 6. Consider using git-secrets or similar tools            │\n";
echo "   └─────────────────────────────────────────────────────────────┘\n";

echo "\n6. Git hooks setup (optional):\n";
echo "   💡 You can set up git hooks to prevent accidental commits of credentials.\n";
echo "   Would you like to create a pre-commit hook? (y/n): ";

$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
    $hooksDir = $projectRoot . '/.git/hooks';
    $preCommitHook = $hooksDir . '/pre-commit';
    
    if (!is_dir($hooksDir)) {
        mkdir($hooksDir, 0755, true);
    }
    
    $hookContent = "#!/bin/sh\n";
    $hookContent .= "# Pre-commit hook to prevent committing sensitive data\n\n";
    $hookContent .= "# Check for potential credentials\n";
    $hookContent .= "if git diff --cached --name-only | grep -E '\\.(php|js|py|rb|java)$' | xargs grep -l 'password\\|secret\\|api.*key\\|skahush254' 2>/dev/null; then\n";
    $hookContent .= "    echo '❌ ERROR: Potential credentials found in staged files!'\n";
    $hookContent .= "    echo '   Please review and remove sensitive data before committing.'\n";
    $hookContent .= "    exit 1\n";
    $hookContent .= "fi\n\n";
    $hookContent .= "# Check for .env files\n";
    $hookContent .= "if git diff --cached --name-only | grep -E '\\.env' 2>/dev/null; then\n";
    $hookContent .= "    echo '❌ ERROR: .env files should not be committed!'\n";
    $hookContent .= "    echo '   These files contain sensitive configuration.'\n";
    $hookContent .= "    exit 1\n";
    $hookContent .= "fi\n\n";
    $hookContent .= "echo '✅ Pre-commit security check passed'\n";
    
    file_put_contents($preCommitHook, $hookContent);
    chmod($preCommitHook, 0755);
    
    echo "   ✅ Pre-commit hook installed successfully!\n";
    echo "   📝 This hook will prevent committing credentials\n";
}

echo "\n🎉 Security setup complete!\n";
echo "\nNext steps:\n";
echo "1. Review and update includes/.env.php with your actual settings\n";
echo "2. Test your application to ensure it still works\n";
echo "3. Commit your changes (credentials will be protected)\n";
echo "4. Share .env.example.php with your team (not .env.php!)\n\n";

echo "Remember: NEVER commit your .env.php file to git!\n";
?>