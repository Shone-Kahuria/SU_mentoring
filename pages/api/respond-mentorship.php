<?php
/**
 * API Endpoint: Respond to Mentorship Request
 * 
 * Purpose: Allow mentors to accept or decline mentorship requests
 * Method: POST
 * 
 * Request Parameters:
 * - request_id: ID of the mentorship request (int)
 * - action: 'accept' or 'decline' (string)
 * - csrf_token: CSRF token for security (string)
 * 
 * Response: JSON object with success status and message
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

startSession();

// Set JSON response header
header('Content-Type: application/json');

try {
    // Validate HTTP method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        sendJsonResponse(false, 'Invalid request method. POST required.');
    }

    
    // Check if user is logged in
    if (!isLoggedIn()) {
        http_response_code(401);
        sendJsonResponse(false, 'Please log in to continue.');
    }

    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        sendJsonResponse(false, 'Invalid security token.');
    }

    // Validate and sanitize input
    $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $action = strtolower(sanitizeInput($_POST['action'] ?? ''));

    if ($requestId <= 0 || !in_array($action, ['accept', 'decline'], true)) {
        http_response_code(400);
        sendJsonResponse(false, 'Invalid request payload. Ensure request_id is a positive integer and action is accept or decline.');
    }

    // Get current user info
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();

    // Verify user is a mentor
    if ($userRole !== 'mentor') {
        http_response_code(403);
        sendJsonResponse(false, 'Only mentors can respond to mentorship requests.');
    }

    // Fetch mentorship request details
    $mentorship = selectRecord('SELECT * FROM mentorships WHERE id = :id', ['id' => $requestId]);

    if (!$mentorship) {
        http_response_code(404);
        sendJsonResponse(false, 'Mentorship request not found.');
    }

    // Verify mentor authorization
    if ((int)$mentorship['mentor_id'] !== $userId) {
        http_response_code(403);
        sendJsonResponse(false, 'You are not authorized to modify this mentorship request.');
    }

    // Check if request is still pending
    if ($mentorship['status'] !== 'pending') {
        http_response_code(409);
        sendJsonResponse(false, 'This mentorship request has already been processed. Current status: ' . htmlspecialchars($mentorship['status']));
    }

    // Prepare update data
    $updateData = [];
    $responseMessage = '';

    if ($action === 'accept') {
        // Validate gender matching before accepting
        $genderValidation = validateSameGenderMentorship($mentorship['mentor_id'], $mentorship['mentee_id']);
        
        if ($genderValidation !== 'ok') {
            http_response_code(422);
            sendJsonResponse(false, 'Cannot accept mentorship: ' . htmlspecialchars($genderValidation));
        }

        $updateData = [
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $responseMessage = 'Mentorship request accepted successfully. You can now start scheduling sessions.';
    } else {
        // Decline action
        $updateData = [
            'status' => 'declined',
            'notes' => 'Declined by mentor',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $responseMessage = 'Mentorship request declined.';
    }

    // Update mentorship record in database
    $updated = updateRecord('mentorships', $updateData, 'id = :id', ['id' => $mentorship['id']]);

    if (!$updated) {
        http_response_code(500);
        error_log("Failed to update mentorship request ID: {$requestId}");
        sendJsonResponse(false, 'Failed to update the mentorship request. Please try again.');
    }

    // Log the activity
    logActivity('mentorship_request_' . $action, [
        'mentorship_id' => $mentorship['id'],
        'mentor_id' => $userId,
        'mentee_id' => $mentorship['mentee_id'],
        'action' => $action,
        'new_status' => $updateData['status']
    ]);

    // Send success response
    http_response_code(200);
    sendJsonResponse(true, $responseMessage, [
        'mentorship_id' => $mentorship['id'],
        'new_status' => $updateData['status'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Throwable $e) {
    // Catch any unexpected errors
    http_response_code(500);
    error_log("Error in respond-mentorship.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendJsonResponse(false, 'An unexpected error occurred. Please try again later.');
}
