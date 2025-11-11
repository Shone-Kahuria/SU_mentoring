<?php
/**
 * API Endpoint: Check For New Sessions
 * 
 * Returns a list of recently scheduled sessions for the current user
 * Used by the dashboard to display notifications about new session bookings
 * 
 * Expected Response Format (JSON):
 * {
 *   "success": true,
 *   "message": "Session check completed",
 *   "newSessions": [
 *     {
 *       "id": 123,
 *       "title": "Career Planning Session",
 *       "scheduled_date": "2025-11-15 14:00:00",
 *       "duration_minutes": 60,
 *       "mentor_name": "John Doe",
 *       "mentee_name": "Jane Smith",
 *       "status": "scheduled"
 *     }
 *   ]
 * }
 * 
 * This endpoint is called periodically by the dashboard JavaScript
 * to check if any new sessions have been scheduled since the last check.
 */

// Set JSON response header
header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/config.php';

try {
    // Start session and verify authentication
    startSession();
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in',
            'newSessions' => []
        ]);
        exit;
    }
    
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    
    // Get sessions scheduled in the last hour that the user hasn't seen yet
    // (This is an estimate - a production system would track "last checked" timestamp)
    $sql = "SELECT 
                s.id,
                s.title,
                s.scheduled_date,
                s.duration_minutes,
                s.status,
                m.mentor_id,
                m.mentee_id,
                mentor.full_name as mentor_name,
                mentee.full_name as mentee_name,
                s.created_at
            FROM sessions s
            JOIN mentorships m ON s.mentorship_id = m.id
            JOIN users mentor ON m.mentor_id = mentor.id
            JOIN users mentee ON m.mentee_id = mentee.id
            WHERE (m.mentor_id = :user_id OR m.mentee_id = :user_id)
            AND s.status = 'scheduled'
            AND s.scheduled_date > NOW()
            AND s.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY s.created_at DESC
            LIMIT 10";
    
    $newSessions = selectRecords($sql, [
        'user_id' => $userId
    ]) ?: [];
    
    // Format the response
    $formattedSessions = [];
    foreach ($newSessions as $session) {
        $formattedSessions[] = [
            'id' => (int)$session['id'],
            'title' => htmlspecialchars($session['title']),
            'scheduled_date' => $session['scheduled_date'],
            'duration_minutes' => (int)$session['duration_minutes'],
            'mentor_name' => htmlspecialchars($session['mentor_name']),
            'mentee_name' => htmlspecialchars($session['mentee_name']),
            'status' => $session['status'],
            'created_at' => $session['created_at']
        ];
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Session check completed',
        'newSessions' => $formattedSessions,
        'count' => count($formattedSessions)
    ]);
    exit;

} catch (\Throwable $e) {
    // Log the error
    error_log('Check sessions API error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while checking sessions',
        'newSessions' => []
    ]);
    exit;
}
