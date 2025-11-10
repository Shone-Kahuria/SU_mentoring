<?php
/**
 * Authentication Helper (auth.php)
 * Handles user authentication and session managing
 */

require_once 'db.php';

/**
 * Start session if not already started.
 */
function auth_start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function auth_is_logged_in() {
    auth_start_session();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function auth_get_user_id() {
    auth_start_session();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function auth_get_user_role() {
    auth_start_session();
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user data
 */
function auth_get_user() {
    $user_id = auth_get_user_id();
    if (!$user_id) return null;
    
    $sql = "SELECT id, full_name, email, gender, role, created_at, last_login FROM users WHERE id = :id";
    return db_select_one($sql, ['id' => $user_id]);
}

/**
 * Login user
 */
function auth_login($email, $password) {
    $sql = "SELECT id, full_name, email, gender, password_hash, role, is_active FROM users WHERE email = :email";
    $user = db_select_one($sql, ['email' => $email]);
    
    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        auth_start_session();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_gender'] = strtolower($user['gender'] ?? '');
        
        // Update last login
        db_update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
        
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function auth_logout() {
    auth_start_session();
    session_destroy();
    
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

/**
 * Require login (redirect if not logged in)
 */
function auth_require_login() {
    if (!auth_is_logged_in()) {
        header('Location: ../pages/login.php');
        exit();
    }
}

/**
 * Require specific role
 */
function auth_require_role($required_role) {
    auth_require_login();
    $user_role = auth_get_user_role();
    if ($user_role !== $required_role) {
        header('Location: ../pages/dashboard.php');
        exit();
    }
}

/**
 * Hash password
 */
function auth_hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function auth_verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function auth_generate_csrf_token() {
    auth_start_session();
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Verify CSRF token
 */
function auth_verify_csrf_token($token) {
    auth_start_session();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input
 */
function auth_sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function auth_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if email exists
 */
function auth_email_exists($email, $exclude_user_id = null) {
    $sql = "SELECT id FROM users WHERE email = :email";
    $params = ['email' => $email];
    
    if ($exclude_user_id) {
        $sql .= " AND id != :exclude_id";
        $params['exclude_id'] = $exclude_user_id;
    }
    
    return db_select_one($sql, $params) !== false;
}

/**
 * Create new user
 */
function auth_create_user($full_name, $email, $password, $role, $gender = 'male') {
    // Check if email already exists
    if (auth_email_exists($email)) {
        return false;
    }
    $allowedGenders = ['male', 'female'];
    $gender = strtolower($gender);
    if (!in_array($gender, $allowedGenders, true)) {
        $gender = 'male';
    }
    
    $user_data = [
        'full_name' => $full_name,
        'email' => $email,
        'gender' => $gender,
        'password_hash' => auth_hash_password($password),
        'role' => $role,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    return db_insert('users', $user_data);
}

/**
 * Set flash message
 */
function auth_set_flash($message, $type = 'success') {
    auth_start_session();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 */
function auth_get_flash() {
    auth_start_session();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function auth_display_flash() {
    $flash = auth_get_flash();
    if ($flash) {
        $alert_class = 'alert-' . $flash['type'];
        return "<div class='alert {$alert_class}'>" . htmlspecialchars($flash['message']) . "</div>";
    }
    return '';
}
?>