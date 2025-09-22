<?php
/**
 * Session check endpoint for AJAX requests
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