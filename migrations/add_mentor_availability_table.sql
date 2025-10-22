-- =====================================================
-- MENTOR AVAILABILITY TABLE
-- Migration: Add mentor_availability table
-- Purpose: Store mentor available time slots for booking
-- =====================================================

CREATE TABLE IF NOT EXISTS mentor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_recurring TINYINT(1) DEFAULT 1 COMMENT '1 = weekly recurring, 0 = one-time',
    is_available TINYINT(1) DEFAULT 1 COMMENT '1 = available, 0 = blocked/unavailable',
    notes VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mentor (mentor_id),
    INDEX idx_day (day_of_week),
    INDEX idx_available (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing (optional)
-- Uncomment if you want test data
/*
INSERT INTO mentor_availability (mentor_id, day_of_week, start_time, end_time, is_recurring) 
SELECT id, 'Monday', '09:00:00', '12:00:00', 1 FROM users WHERE role = 'mentor' LIMIT 1;

INSERT INTO mentor_availability (mentor_id, day_of_week, start_time, end_time, is_recurring) 
SELECT id, 'Wednesday', '14:00:00', '17:00:00', 1 FROM users WHERE role = 'mentor' LIMIT 1;
*/
