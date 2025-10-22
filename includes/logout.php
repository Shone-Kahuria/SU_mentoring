<?php
/**
 * Logout functionality
 */

require_once 'functions.php';
startSession();

// Get user ID before destroying session
$userId = getCurrentUserId();

if ($userId) {
    // Log the logout activity
    logActivity('user_logout', ['user_id' => $userId]);
    
    // Clear remember me token if exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        executeQuery("DELETE FROM user_sessions WHERE session_token = :token", ['token' => $token]);
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

// Destroy session
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Handle AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit();
}

// Regular request - redirect to login page
header('Location: ../pages/login.php?message=logged_out');
exit();
?>