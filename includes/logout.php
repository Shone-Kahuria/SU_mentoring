<?php
/**
 * Logout functionality
 */

require_once '../includes/functions.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    session_destroy();
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // AJAX request
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } else {
        // Regular request
        setFlashMessage('You have been logged out successfully.', 'success');
        redirect('../pages/login.php');
    }
} else {
    redirect('../pages/dashboard.php');
}
?>