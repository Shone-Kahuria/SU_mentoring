<?php
// includes/validation_functions.php

/**
 * Checks if a mentorship request is between users of the same gender.
 * Assumes selectRecord and getUserGender exist.
 * * @param int $mentorId
 * @param int $menteeId
 * @return array ['valid' => bool, 'message' => string]
 */
function validateSameGenderMentorship($mentorId, $menteeId) {
    // Assuming getUserGender fetches 'male' or 'female' from the database
    $mentorGender = getUserGender($mentorId); 
    $menteeGender = getUserGender($menteeId);

    if ($mentorGender !== $menteeGender) {
        return [
            'valid' => false,
            'message' => "Mentorship requests must be between users of the same gender."
        ];
    }

    return [
        'valid' => true,
        'message' => "Gender match confirmed."
    ];
}

// ... other validation functions