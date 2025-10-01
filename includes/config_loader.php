<?php
/**
 * Secure Configuration Loader
 * This file safely loads environment variables from .env.php
 * and provides fallback to default values
 */

class ConfigLoader {
    private static $loaded = false;
    private static $config = [];
    
    /**
     * Load environment configuration
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        // Look for environment file
        $envFile = __DIR__ . '/.env.php';
        $exampleFile = __DIR__ . '/.env.example.php';
        
        if (file_exists($envFile)) {
            // Load actual environment file
            require_once $envFile;
            self::$loaded = true;
        } elseif (file_exists($exampleFile)) {
            // Load example file for development (with warnings)
            if (self::isDevelopment()) {
                error_log("WARNING: Using example environment file. Copy .env.example.php to .env.php and update with real values.");
                require_once $exampleFile;
            } else {
                throw new Exception("Environment configuration file not found. Please create .env.php from .env.example.php");
            }
            self::$loaded = true;
        } else {
            // Fallback to default values (very basic)
            self::setDefaults();
            self::$loaded = true;
        }
        
        // Validate required configuration
        self::validateConfig();
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        self::load();
        
        if (defined($key)) {
            return constant($key);
        }
        
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }
    
    /**
     * Check if we're in development mode
     */
    private static function isDevelopment() {
        return isset($_SERVER['SERVER_NAME']) && 
               (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || 
                strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
    }
    
    /**
     * Set default configuration values
     */
    private static function setDefaults() {
        // Only very basic defaults - force user to create proper config
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'mentoring_website');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_CHARSET', 'utf8mb4');
        define('DB_PORT', 3306);
        
        define('APP_ENV', 'development');
        define('APP_DEBUG', true);
        define('APP_SECRET_KEY', 'CHANGE_THIS_SECRET_KEY_IMMEDIATELY');
        
        // Log warning about using defaults
        error_log("WARNING: Using default configuration values. This is not secure for production!");
    }
    
    /**
     * Validate that required configuration is present
     */
    private static function validateConfig() {
        $required = [
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'APP_SECRET_KEY'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            if (!defined($key) || empty(constant($key)) || constant($key) === 'your_' . strtolower($key) . '_here') {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            $message = "Missing or invalid configuration for: " . implode(', ', $missing);
            if (self::get('APP_ENV') === 'production') {
                throw new Exception($message);
            } else {
                error_log("CONFIG WARNING: " . $message);
            }
        }
        
        // Validate secret key strength
        if (self::get('APP_SECRET_KEY') === 'CHANGE_THIS_SECRET_KEY_IMMEDIATELY' || 
            strlen(self::get('APP_SECRET_KEY')) < 32) {
            $message = "APP_SECRET_KEY must be changed and be at least 32 characters long";
            if (self::get('APP_ENV') === 'production') {
                throw new Exception($message);
            } else {
                error_log("SECURITY WARNING: " . $message);
            }
        }
    }
    
    /**
     * Check if configuration is properly set up
     */
    public static function isConfigured() {
        return file_exists(__DIR__ . '/.env.php');
    }
    
    /**
     * Generate a secure random key
     */
    public static function generateSecretKey($length = 32) {
        return bin2hex(random_bytes($length));
    }
}