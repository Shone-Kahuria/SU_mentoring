<?php
/**
 * Environment Configuration Template
 * Copy this file to .env.php and update with your actual values
 * NEVER commit the actual .env.php file!
 */

// ========================================
// DATABASE CONFIGURATION
// ========================================
// Database credentials for local development
define('DB_HOST', 'localhost');
define('DB_NAME', 'mentoring_website');
define('DB_USER', 'mentoring_user');
define('DB_PASS', 'mentoring_pass_123');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// ========================================
// EMAIL CONFIGURATION
// ========================================
// SMTP Settings for sending emails
define('SMTP_HOST', 'your_smtp_host_here');          // e.g., 'smtp.gmail.com'
define('SMTP_PORT', 587);                            // or 465 for SSL
define('SMTP_USERNAME', 'your_email@domain.com');
define('SMTP_PASSWORD', 'your_email_password_here');
define('SMTP_ENCRYPTION', 'tls');                    // 'tls' or 'ssl'

// Email settings
define('FROM_EMAIL', 'noreply@yourdomain.com');
define('FROM_NAME', 'SU Mentoring Platform');
define('REPLY_TO_EMAIL', 'support@yourdomain.com');

// ========================================
// SECURITY SETTINGS
// ========================================
// Generate these with: openssl rand -hex 32
define('APP_SECRET_KEY', 'generate_a_32_character_secret_key_here');
define('JWT_SECRET', 'generate_another_32_character_secret_here');
define('ENCRYPTION_KEY', 'yet_another_32_character_encryption_key');

// Session security
define('SESSION_COOKIE_SECURE', false);              // Set to true for HTTPS
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

// ========================================
// THIRD PARTY API KEYS
// ========================================
// Add your API keys here (never commit actual values)
define('GOOGLE_API_KEY', 'your_google_api_key_here');
define('RECAPTCHA_SITE_KEY', 'your_recaptcha_site_key_here');
define('RECAPTCHA_SECRET_KEY', 'your_recaptcha_secret_key_here');

// Social login (if needed)
define('GOOGLE_CLIENT_ID', 'your_google_client_id_here');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here');

// ========================================
// APPLICATION SETTINGS
// ========================================
define('APP_ENV', 'development');                    // 'development', 'staging', 'production'
define('APP_DEBUG', true);                           // Set to false in production
define('APP_URL', 'http://localhost/SU_mentoring');  // Your application URL

// File upload settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);        // 10MB in bytes
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx');

// Timezone
define('APP_TIMEZONE', 'UTC');                       // Set your timezone

// ========================================
// LOGGING SETTINGS
// ========================================
define('LOG_LEVEL', 'INFO');                         // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', __DIR__ . '/../logs/app.log');
define('ERROR_LOG_FILE', __DIR__ . '/../logs/error.log');

// ========================================
// BACKUP SETTINGS
// ========================================
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('BACKUP_RETENTION_DAYS', 30);

// ========================================
// DEVELOPMENT TOOLS
// ========================================
// Only for development environment
if (APP_ENV === 'development') {
    define('SHOW_ERRORS', true);
    define('ENABLE_DEBUG_TOOLBAR', true);
} else {
    define('SHOW_ERRORS', false);
    define('ENABLE_DEBUG_TOOLBAR', false);
}