<?php
/**
 * API Endpoint: Request Mentorship
 * 
 * Allows a logged-in mentee to send a mentorship request to a mentor.
 * Enforces same-gender pairing policy before allowing the request.
 * 
 * Requirements:
 * - User must be logged in as a mentee
 * - Valid CSRF token must be provided
 * - Mentor must exist and be active
 * - Same-gender validation must pass
 * - No existing pending/active request between the pair
 * 
 * Success redirects to dashboard with flash message.
 * Errors redirect back to find-mentor.php with error message.
 */

// Load all required dependencies
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/config.php';

// Start session and verify authentication
startSession();
requireLogin();

try {
    // ============================================================
    // A. INPUT VALIDATION
    // ============================================================
    
    $mentorId = isset($_POST['mentor_id']) ? (int)$_POST['mentor_id'] : 0;
    $csrfToken = $_POST['csrf_token'] ?? '';
    $menteeId = getCurrentUserId();
    
    if (!$mentorId || $mentorId <= 0) {
        setFlashMessage('Invalid mentor ID.', 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // B. AUTHORIZATION CHECKS
    // ============================================================
    
    // Check user is a mentee (not a mentor)
    $userRole = getCurrentUserRole();
    if ($userRole !== 'mentee') {
        setFlashMessage('Only mentees can request mentors. Your current role is: ' . htmlspecialchars($userRole), 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // CSRF token verification
    if (!verifyCSRFToken($csrfToken)) {
        error_log("CSRF token verification failed for user $menteeId");
        setFlashMessage('Invalid security token. Please try again.', 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // C. MENTOR VALIDATION
    // ============================================================
    
    // Verify mentor exists and is active
    $mentor = selectRecord(
        "SELECT id, full_name, email, gender, role, is_active FROM users 
         WHERE id = :id AND role = 'mentor' AND is_active = 1",
        ['id' => $mentorId]
    );
    
    if (!$mentor) {
        setFlashMessage('The mentor you selected is no longer available. Please refresh and try again.', 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // D. GENDER VALIDATION (CRITICAL BUSINESS RULE)
    // ============================================================
    
    // Enforce same-gender mentorship policy
    $genderValidation = validateSameGenderMentorship($mentorId, $menteeId);
    
    if (!$genderValidation['ok']) {
        error_log("Gender validation failed: Mentor ID $mentorId and Mentee ID $menteeId - " . $genderValidation['message']);
        setFlashMessage($genderValidation['message'], 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // E. DUPLICATE REQUEST CHECK
    // ============================================================
    
    // Prevent duplicate requests or reactivating existing relationships
    $existingRequest = selectRecord(
        "SELECT id, status FROM mentorships 
         WHERE mentor_id = :mentor_id AND mentee_id = :mentee_id
         AND status IN ('pending', 'active')",
        ['mentor_id' => $mentorId, 'mentee_id' => $menteeId]
    );
    
    if ($existingRequest) {
        $statusMsg = ucfirst($existingRequest['status']);
        setFlashMessage("You already have a $statusMsg mentorship with this mentor.", 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // F. CREATE MENTORSHIP REQUEST
    // ============================================================
    
    // Prepare mentorship record
    $mentorshipData = [
        'mentor_id' => $mentorId,
        'mentee_id' => $menteeId,
        'status' => 'pending',
        'requested_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert into database
    $mentorshipId = insertRecord('mentorships', $mentorshipData);
    
    if (!$mentorshipId) {
        error_log("Failed to insert mentorship record for mentor $mentorId, mentee $menteeId");
        setFlashMessage('Failed to send mentorship request. Please try again.', 'error');
        redirect('../find-mentor.php');
        exit;
    }
    
    // ============================================================
    // G. SEND NOTIFICATION EMAIL
    // ============================================================
    
    // Get mentee name for email
    $mentee = selectRecord("SELECT full_name, email FROM users WHERE id = :id", ['id' => $menteeId]);
    $menteeName = $mentee ? $mentee['full_name'] : 'A mentee';
    
    // Send email notification to mentor
    $emailSent = sendMentorshipRequestEmail(
        $mentor['email'],
        $mentor['full_name'],
        $menteeName
    );
    
    if (!$emailSent) {
        error_log("Warning: Failed to send mentorship request email to mentor $mentorId (ID: {$mentor['email']})");
        // Don't fail the request; just log the warning
    }
    
    // ============================================================
    // H. LOG ACTIVITY AND REDIRECT
    // ============================================================
    
    // Log the mentorship request action
    logActivity('mentorship_request_sent', [
        'mentorship_id' => $mentorshipId,
        'mentor_id' => $mentorId,
        'mentee_id' => $menteeId,
        'status' => 'pending'
    ]);
    
    // Success message and redirect
    setFlashMessage('Mentorship request sent successfully! Your mentor will receive a notification.', 'success');
    redirect('../dashboard.php');
    exit;

} catch (\Throwable $e) {
    // Catch any unexpected errors
    error_log('Request mentorship API error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    setFlashMessage('An unexpected error occurred. Please try again or contact support.', 'error');
    redirect('../find-mentor.php');
    exit;
}