<?php
// Include necessary files (db, user, validation, email)
// require 'includes/db.php';
// require 'includes/user_functions.php'; 
// require 'includes/validation_functions.php'; // Contains validateSameGenderMentorship
// require 'includes/email_functions.php';

// Assume these functions are defined elsewhere:
// getCurrentUserId(), verifyCSRFToken($token), redirect($url), insertRecord($sql, $params)
// validateSameGenderMentorship($mentorId, $menteeId) (returns ['valid' => bool, 'message' => string])
// sendMentorshipRequestEmail($mentorEmail, $mentorName, $menteeName)
// selectRecord($sql, $params)

// A. Validate Input
$mentorId = (int)($_POST['mentor_id'] ?? 0);
$menteeId = getCurrentUserId();
$csrfToken = $_POST['csrf_token'] ?? '';

// 1. Check user is logged in
requireLogin();

// 2. Check user is a mentee
if (getCurrentUserRole() !== 'mentee') {
    $_SESSION['error'] = 'Only mentees can request mentors.';
    redirect('../find-mentor.php');
    exit;
}

// CSRF check
if (!verifyCSRFToken($csrfToken)) {
    $_SESSION['error'] = 'Invalid request (CSRF check failed).';
    redirect('../find-mentor.php');
    exit;
}

// 3. Check mentor exists and is active
$mentor = selectRecord("SELECT * FROM users WHERE id = :id AND role = 'mentor' AND is_active = 1", ['id' => $mentorId]);
if (!$mentor) {
    $_SESSION['error'] = 'Invalid mentor.';
    redirect('../find-mentor.php');
    exit;
}


// B. Gender Validation (Critical!)
// Layer 2: Request Validation - Blocks different-gender requests
$genderValidation = validateSameGenderMentorship($mentorId, $menteeId);

if (!$genderValidation['valid']) {
    // Test Case 3: Gender Mismatch Attempt
    $_SESSION['error'] = $genderValidation['message']; // "Mentorship requests must be between users of the same gender"
    redirect('../find-mentor.php');
    exit;
}

// C. Check for Existing Requests
$existingRequest = selectRecord(
    "SELECT * FROM mentorships 
    WHERE mentor_id = :mentor_id 
      AND mentee_id = :mentee_id
      AND status IN ('pending', 'active')", 
    [
        'mentor_id' => $mentorId, 
        'mentee_id' => $menteeId
    ]
);

if ($existingRequest) {
    $_SESSION['error'] = 'You already have an active or pending request with this mentor.';
    redirect('../find-mentor.php');
    exit;
}

// D. Create Mentorship Request
$newMentorshipId = insertRecord(
    "INSERT INTO mentorships (mentor_id, mentee_id, status, requested_at) 
     VALUES (:mentor_id, :mentee_id, 'pending', NOW())",
    [
        'mentor_id' => $mentorId, 
        'mentee_id' => $menteeId
    ]
);

if ($newMentorshipId) {
    // E. Send Notification Email
    $menteeName = getUserName($menteeId); // Assume this function exists
    
    sendMentorshipRequestEmail(
        $mentor['email'], 
        $mentor['full_name'], 
        $menteeName
    );

    $_SESSION['success'] = 'Mentorship request sent successfully!';
} else {
    $_SESSION['error'] = 'Failed to create mentorship request.';
}

redirect('../dashboard.php'); // Redirect to dashboard or confirmation page
?>