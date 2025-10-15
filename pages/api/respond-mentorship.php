<?php
/**
 * API endpoint to accept or decline mentorship requests.
 * Enforces same-gender pairing before activating a mentorship.
 */

require_once '../../includes/functions.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method.');
}

if (!isLoggedIn()) {
    sendJsonResponse(false, 'Please log in to continue.');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    sendJsonResponse(false, 'Invalid security token.');
}

$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? '';

if ($requestId <= 0 || !in_array($action, ['accept', 'decline'], true)) {
    sendJsonResponse(false, 'Invalid request payload.');
}

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

if ($userRole !== 'mentor') {
    sendJsonResponse(false, 'Only mentors can respond to mentorship requests.');
}

$mentorship = selectRecord('SELECT * FROM mentorships WHERE id = :id', ['id' => $requestId]);

if (!$mentorship) {
    sendJsonResponse(false, 'Mentorship request not found.');
}

if ((int)$mentorship['mentor_id'] !== $userId) {
    sendJsonResponse(false, 'You are not authorized to modify this mentorship request.');
}

if ($mentorship['status'] !== 'pending') {
    sendJsonResponse(false, 'This mentorship request has already been processed.');
}

if ($action === 'accept') {
    $genderValidation = validateSameGenderMentorship($mentorship['mentor_id'], $mentorship['mentee_id']);
    if (!$genderValidation['ok']) {
        sendJsonResponse(false, $genderValidation['message']);
    }

    $updateData = [
        'status' => 'active',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $responseMessage = 'Mentorship accepted successfully. You can now start scheduling sessions.';
} else {
    $updateData = [
        'status' => 'cancelled',
        'notes' => 'Declined by mentor',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $responseMessage = 'Mentorship request declined.';
}

$updated = updateRecord('mentorships', $updateData, 'id = :id', ['id' => $mentorship['id']]);

if (!$updated) {
    sendJsonResponse(false, 'Failed to update the mentorship request. Please try again.');
}

logActivity('mentorship_request_' . $action, [
    'mentorship_id' => $mentorship['id'],
    'action' => $action,
    'new_status' => $updateData['status']
]);

sendJsonResponse(true, $responseMessage);
