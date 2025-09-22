<?php
/**
 * Common utility functions for the Mentoring Website
 */

require_once 'config.php';
require_once 'email.php';

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Sanitize input data
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Validation result with success status and message
 */
function validatePassword($password) {
    $result = ['success' => true, 'message' => ''];
    
    if (strlen($password) < 8) {
        $result['success'] = false;
        $result['message'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $result['success'] = false;
        $result['message'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
    }
    
    return $result;
}

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    startSession();
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid token
 */
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    startSession();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user role
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    startSession();
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Get current user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }
    
    $sql = "SELECT id, full_name, email, role, created_at FROM users WHERE id = :id";
    return selectRecord($sql, ['id' => $userId]);
}

/**
 * Redirect to specified URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Require login (redirect to login page if not logged in)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Require specific role
 * @param string $requiredRole Required role
 */
function requireRole($requiredRole) {
    requireLogin();
    $userRole = getCurrentUserRole();
    if ($userRole !== $requiredRole) {
        redirect('dashboard.php');
    }
}

/**
 * Set flash message
 * @param string $message Message text
 * @param string $type Message type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'success') {
    startSession();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message data or null if none
 */
function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * @return string HTML for flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = 'alert-' . $flash['type'];
        return "<div class='alert {$alertClass}'>" . htmlspecialchars($flash['message']) . "</div>";
    }
    return '';
}

/**
 * Log user activity
 * @param string $action Action performed
 * @param array $details Additional details
 */
function logActivity($action, $details = []) {
    $userId = getCurrentUserId();
    if ($userId) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        insertRecord('activity_logs', $data);
    }
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y g:i A') {
    return date($format, strtotime($date));
}

/**
 * Get user by email
 * @param string $email User email
 * @return array|false User data or false if not found
 */
function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = :email";
    return selectRecord($sql, ['email' => $email]);
}

/**
 * Get user by ID
 * @param int $id User ID
 * @return array|false User data or false if not found
 */
function getUserById($id) {
    $sql = "SELECT * FROM users WHERE id = :id";
    return selectRecord($sql, ['id' => $id]);
}

/**
 * Check if email exists
 * @param string $email Email to check
 * @param int $excludeUserId User ID to exclude from check (for updates)
 * @return bool True if email exists
 */
function emailExists($email, $excludeUserId = null) {
    $sql = "SELECT id FROM users WHERE email = :email";
    $params = ['email' => $email];
    
    if ($excludeUserId) {
        $sql .= " AND id != :exclude_id";
        $params['exclude_id'] = $excludeUserId;
    }
    
    return selectRecord($sql, $params) !== false;
}

/**
 * Generate random string
 * @param int $length Length of string
 * @return string Random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Send JSON response
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $data Additional data
 */
function sendJsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Include header template
 * @param string $title Page title
 */
function includeHeader($title = 'Mentoring Website') {
    include 'includes/header.php';
}

/**
 * Include footer template
 */
function includeFooter() {
    include 'includes/footer.php';
}

/**
 * Clean old sessions (can be called via cron job)
 */
function cleanOldSessions() {
    $sql = "DELETE FROM user_sessions WHERE expires_at < NOW()";
    executeQuery($sql);
}

/**
 * Rate limiting check
 * @param string $identifier Unique identifier (IP, user ID, etc.)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if within rate limit
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $sql = "SELECT COUNT(*) as attempts FROM rate_limits 
            WHERE identifier = :identifier 
            AND created_at > DATE_SUB(NOW(), INTERVAL :timeWindow SECOND)";
    
    $result = selectRecord($sql, [
        'identifier' => $identifier,
        'timeWindow' => $timeWindow
    ]);
    
    $attempts = $result ? (int)$result['attempts'] : 0;
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    
    // Log this attempt
    insertRecord('rate_limits', [
        'identifier' => $identifier,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    return true;
}
?>