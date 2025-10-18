-- =====================================================
-- SU MENTORING PLATFORM - CORE DATABASE SCHEMA
-- Streamlined schema containing only the structures that the
-- current PHP application reads or writes today.
-- Generated: October 15, 2025
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS mentoring_website;
CREATE DATABASE mentoring_website
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mentoring_website;

-- =====================================================
-- 1. USERS
-- Stores every account (admin, mentor, mentee)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    gender ENUM('male', 'female') NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email_verification_token VARCHAR(64) DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    role ENUM('admin', 'mentor', 'mentee') NOT NULL DEFAULT 'mentee',
    bio TEXT DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    experience_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_role (role),
    INDEX idx_gender (gender),
    INDEX idx_is_active (is_active),
    INDEX idx_email_verified (email_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. MENTORSHIPS
-- Links mentors and mentees together
-- =====================================================
CREATE TABLE mentorships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentor_id INT NOT NULL,
    mentee_id INT NOT NULL,
    status ENUM('pending', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    subject_area VARCHAR(100) DEFAULT NULL,
    goals TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mentor (mentor_id),
    INDEX idx_mentee (mentee_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. SESSIONS
-- Scheduled mentoring sessions and requests
-- =====================================================
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentorship_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    scheduled_date DATETIME NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 60,
    meeting_link VARCHAR(500) DEFAULT NULL,
    status ENUM('pending', 'scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_by INT NOT NULL,
    cancelled_by INT DEFAULT NULL,
    cancellation_reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (mentorship_id) REFERENCES mentorships(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mentorship (mentorship_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_date (scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. MESSAGES
-- Direct messages exchanged within a mentorship
-- =====================================================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentorship_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (mentorship_id) REFERENCES mentorships(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mentorship (mentorship_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ACTIVITY LOGS
-- Audit log for important user actions
-- =====================================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. RATE LIMITS
-- Tracks recent actions for throttling
-- =====================================================
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_identifier (identifier),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. USER SESSIONS
-- Remember-me tokens
-- =====================================================
CREATE TABLE user_sessions (
    session_token CHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. PASSWORD RESETS
-- Stores password reset tokens
-- =====================================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    UNIQUE INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT SEED DATA
-- Minimal accounts to help with testing
-- Password hashes equal to the string "password"
-- =====================================================
INSERT INTO users (full_name, email, gender, password_hash, role, bio, skills, experience_level, is_active, email_verification_token, email_verified)
VALUES
    ('System Administrator', 'admin@mentoring.local', 'female', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Site administrator account.', NULL, NULL, 1, NULL, 1),
    ('Dr. Jane Smith', 'jane.smith@mentoring.local', 'female', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 'Senior software engineer and mentor.', 'JavaScript, PHP, Career Coaching', 'advanced', 1, NULL, 1),
    ('Mary Doe', 'mary.doe@mentoring.local', 'female', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee', 'Aspiring developer looking for guidance.', 'Web Development, JavaScript', 'beginner', 1, NULL, 1);

INSERT INTO mentorships (mentor_id, mentee_id, status, subject_area, goals)
VALUES (2, 3, 'active', 'Web Development', 'Create a learning roadmap for full-stack development');

INSERT INTO sessions (mentorship_id, title, description, scheduled_date, duration_minutes, meeting_link, status, created_by)
VALUES (1, 'Introductory Session', 'Discuss goals and expectations.', DATE_ADD(NOW(), INTERVAL 3 DAY), 60, 'https://meet.example.com/session/intro', 'scheduled', 2);

INSERT INTO messages (mentorship_id, sender_id, receiver_id, message)
VALUES (1, 2, 3, 'Welcome to the mentorship! Looking forward to working with you.');

SET FOREIGN_KEY_CHECKS = 1;
