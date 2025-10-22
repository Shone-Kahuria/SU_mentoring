<?php
require_once 'includes/db.php';

echo "=== Checking Mentors in Database ===\n\n";

// Check all users
$sql = "SELECT id, full_name, email, role, gender, is_active FROM users";
$users = selectRecords($sql, []);

echo "Total users: " . count($users) . "\n\n";

// Check mentors
$mentors = array_filter($users, function($user) {
    return $user['role'] === 'mentor';
});

echo "Total mentors: " . count($mentors) . "\n\n";

if (!empty($mentors)) {
    echo "Mentors found:\n";
    foreach ($mentors as $mentor) {
        echo sprintf(
            "- ID: %d | Name: %s | Gender: %s | Active: %s\n",
            $mentor['id'],
            $mentor['full_name'],
            $mentor['gender'],
            $mentor['is_active'] ? 'Yes' : 'No'
        );
    }
} else {
    echo "No mentors found in database.\n";
    echo "\nTo add test mentors, you need to:\n";
    echo "1. Register users via signup page\n";
    echo "2. Update their role to 'mentor' in the database\n";
    echo "3. Set their gender appropriately\n";
}

echo "\n\n=== Checking Your Current User ===\n";
session_start();
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $currentUser = selectRecord("SELECT * FROM users WHERE id = ?", [$userId]);
    
    if ($currentUser) {
        echo sprintf(
            "Your ID: %d\nYour Name: %s\nYour Role: %s\nYour Gender: %s\n",
            $currentUser['id'],
            $currentUser['full_name'],
            $currentUser['role'],
            $currentUser['gender']
        );
        
        // Check for same-gender mentors
        $sameGenderMentors = array_filter($mentors, function($mentor) use ($currentUser) {
            return $mentor['gender'] === $currentUser['gender'] 
                   && $mentor['is_active'] == 1
                   && $mentor['id'] != $currentUser['id'];
        });
        
        echo "\nSame-gender mentors available: " . count($sameGenderMentors) . "\n";
    }
} else {
    echo "You are not logged in.\n";
}
?>
