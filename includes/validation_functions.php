<?php
/**
 * Validation Functions Module
 * 
 * This module contains validation functions for ensuring data integrity
 * and enforcing business rules in the mentoring platform.
 * 
 * Core validation rules:
 * - Gender matching for mentorship
 * - Input sanitization
 * - Data format verification
 * 
 * Dependencies:
 * - Database utility functions
 * - User profile functions
 */

/**
 * Checks if a mentorship request is between users of the same gender.
 * Implements core platform policy requiring same-gender mentorship pairs.
 * 
 * Dependencies:
 * - getUserGender() from user profile module
 * 
 * @param int $mentorId
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