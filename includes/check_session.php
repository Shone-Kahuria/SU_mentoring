<?php
/**
 * Session Check Endpoint
 * Provides AJAX session validation functionality
 * 
 * Purpose:
 * - Validate user session status
 * - Support client-side session checks
 * - Prevent session timeouts
 * 
 * Usage:
 * - Called via AJAX from client JavaScript
 * - Returns JSON response with session status
 * - Used by session keepalive functionality
 */

require_once '../includes/functions.php';
startSession();

header('Content-Type: application/json');

if (isLoggedIn()) {
    echo json_encode(['success' => true, 'logged_in' => true]);
} else {
    echo json_encode(['success' => false, 'logged_in' => false]);
}
?>