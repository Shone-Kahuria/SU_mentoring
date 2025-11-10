<?php
/**
 * Application Initialization.
 * Sets up base URL and loads core dependencies
 */

// Load configuration
require_once __DIR__ . '/config_loader.php';
ConfigLoader::load();

// Set up base URL if not already defined
if (!isset($base_url)) {
    $base_url = rtrim(ConfigLoader::get('APP_URL', ''), '/');
    if (empty($base_url)) {
        // Auto-detect base URL if not configured
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $base_url = $protocol . $host . rtrim($scriptDir, '/\\');
    }
}

// Set default timezone
date_default_timezone_set(ConfigLoader::get('APP_TIMEZONE', 'UTC'));

// Error reporting based on environment
if (ConfigLoader::get('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}