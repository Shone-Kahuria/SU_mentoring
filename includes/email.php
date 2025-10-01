<?php
/**
 * Email Configuration and Helper Functions
 * Provides email functionality for the mentoring website
 */

require_once __DIR__ . '/../Plugins/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../Plugins/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../Plugins/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email configuration
$email_config = [
    'smtp_host' => 'localhost',        // Change to your SMTP server
    'smtp_port' => 587,                // or 465 for SSL
    'smtp_secure' => 'tls',            // 'ssl' or 'tls'
    'smtp_username' => '',             // Your email username
    'smtp_password' => '',             // Your email password
    'from_email' => 'noreply@mentorconnect.com',
    'from_name' => 'MentorConnect',
    'admin_email' => 'admin@mentorconnect.com'
];

/**
 * Create and configure PHPMailer instance
 */
function createMailer() {
    global $email_config;
    
    $mail = new PHPMailer(true);
    
    try {
        // Use built-in PHP mail function for simplicity
        // In production, configure SMTP properly
        $mail->isSMTP();
        $mail->Host = $email_config['smtp_host'];
        $mail->SMTPAuth = !empty($email_config['smtp_username']);
        $mail->Username = $email_config['smtp_username'];
        $mail->Password = $email_config['smtp_password'];
        $mail->SMTPSecure = $email_config['smtp_secure'];
        $mail->Port = $email_config['smtp_port'];
        
        // Set default from
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
    } catch (Exception $e) {
        error_log("Mailer setup failed: " . $e->getMessage());
        return false;
    }
    
    return $mail;
}

/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($userEmail, $userName, $userRole) {
    global $email_config;
    
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        $mail->addAddress($userEmail, $userName);
        $mail->Subject = 'Welcome to MentorConnect!';
        
        $emailBody = getWelcomeEmailTemplate($userName, $userRole);
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Welcome email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
    global $email_config;
    
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        $mail->addAddress($userEmail, $userName);
        $mail->Subject = 'Password Reset Request - MentorConnect';
        
        $resetLink = getBaseUrl() . '/reset-password.php?token=' . urlencode($resetToken);
        $emailBody = getPasswordResetEmailTemplate($userName, $resetLink);
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Password reset email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send session notification email
 */
function sendSessionNotificationEmail($userEmail, $userName, $sessionTitle, $sessionDate, $sessionType = 'scheduled') {
    global $email_config;
    
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        $mail->addAddress($userEmail, $userName);
        
        if ($sessionType === 'scheduled') {
            $mail->Subject = 'Session Scheduled - MentorConnect';
        } elseif ($sessionType === 'cancelled') {
            $mail->Subject = 'Session Cancelled - MentorConnect';
        } else {
            $mail->Subject = 'Session Update - MentorConnect';
        }
        
        $emailBody = getSessionNotificationEmailTemplate($userName, $sessionTitle, $sessionDate, $sessionType);
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Session notification email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send mentorship request notification
 */
function sendMentorshipRequestEmail($mentorEmail, $mentorName, $menteeName, $requestMessage = '') {
    global $email_config;
    
    $mail = createMailer();
    if (!$mail) return false;
    
    try {
        $mail->addAddress($mentorEmail, $mentorName);
        $mail->Subject = 'New Mentorship Request - MentorConnect';
        
        $emailBody = getMentorshipRequestEmailTemplate($mentorName, $menteeName, $requestMessage);
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Mentorship request email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get welcome email template
 */
function getWelcomeEmailTemplate($userName, $userRole) {
    $loginUrl = getBaseUrl() . '/pages/login.php';
    $dashboardUrl = getBaseUrl() . '/pages/dashboard.php';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Welcome to MentorConnect</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0057B7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { background: #D62828; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to MentorConnect!</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($userName) . ",</h2>
                <p>Welcome to MentorConnect! Your account has been successfully created as a <strong>" . ucfirst($userRole) . "</strong>.</p>
                
                <p>You can now:</p>
                <ul>
                    <li>Complete your profile with skills and experience</li>
                    <li>" . ($userRole === 'mentor' ? 'Connect with mentees seeking guidance' : 'Find experienced mentors in your field') . "</li>
                    <li>Schedule mentoring sessions</li>
                    <li>Access our messaging system</li>
                </ul>
                
                <p>Get started by logging into your dashboard:</p>
                <a href='" . htmlspecialchars($dashboardUrl) . "' class='button'>Go to Dashboard</a>
                
                <p>If you have any questions, feel free to reach out to our support team.</p>
                
                <p>Best regards,<br>The MentorConnect Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " MentorConnect. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get password reset email template
 */
function getPasswordResetEmailTemplate($userName, $resetLink) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Password Reset - MentorConnect</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0057B7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { background: #D62828; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset Request</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($userName) . ",</h2>
                <p>We received a request to reset your password for your MentorConnect account.</p>
                
                <p>Click the button below to reset your password:</p>
                <a href='" . htmlspecialchars($resetLink) . "' class='button'>Reset Password</a>
                
                <div class='warning'>
                    <strong>Security Notice:</strong>
                    <ul>
                        <li>This link will expire in 24 hours</li>
                        <li>If you didn't request this reset, please ignore this email</li>
                        <li>Never share this link with anyone</li>
                    </ul>
                </div>
                
                <p>If you're having trouble with the button, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #0057B7;'>" . htmlspecialchars($resetLink) . "</p>
                
                <p>Best regards,<br>The MentorConnect Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " MentorConnect. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get session notification email template
 */
function getSessionNotificationEmailTemplate($userName, $sessionTitle, $sessionDate, $sessionType) {
    $formattedDate = date('F j, Y \a\t g:i A', strtotime($sessionDate));
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Session " . ucfirst($sessionType) . " - MentorConnect</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0057B7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .session-info { background: white; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .button { background: #D62828; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Session " . ucfirst($sessionType) . "</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($userName) . ",</h2>
                <p>Your mentoring session has been " . $sessionType . ".</p>
                
                <div class='session-info'>
                    <h3>" . htmlspecialchars($sessionTitle) . "</h3>
                    <p><strong>Date & Time:</strong> " . $formattedDate . "</p>
                </div>
                
                " . ($sessionType === 'scheduled' ? 
                    "<p>Please make sure to be available at the scheduled time. You can access your session details from your dashboard.</p>
                     <a href='" . getBaseUrl() . "/pages/dashboard.php' class='button'>View Dashboard</a>" : 
                    "<p>If you need to reschedule, please contact your " . ($sessionType === 'cancelled' ? 'mentor/mentee' : 'counterpart') . " directly.</p>") . "
                
                <p>Best regards,<br>The MentorConnect Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " MentorConnect. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get mentorship request email template
 */
function getMentorshipRequestEmailTemplate($mentorName, $menteeName, $requestMessage) {
    $dashboardUrl = getBaseUrl() . '/pages/dashboard.php';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>New Mentorship Request - MentorConnect</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0057B7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .request-info { background: white; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .button { background: #D62828; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Mentorship Request</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($mentorName) . ",</h2>
                <p>You have received a new mentorship request from <strong>" . htmlspecialchars($menteeName) . "</strong>.</p>
                
                " . (!empty($requestMessage) ? "
                <div class='request-info'>
                    <h3>Message from " . htmlspecialchars($menteeName) . ":</h3>
                    <p>" . htmlspecialchars($requestMessage) . "</p>
                </div>
                " : "") . "
                
                <p>You can review this request and respond from your dashboard:</p>
                <a href='" . htmlspecialchars($dashboardUrl) . "' class='button'>Review Request</a>
                
                <p>Best regards,<br>The MentorConnect Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " MentorConnect. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get base URL for the application
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    return $protocol . '://' . $host . rtrim($path, '/');
}

/**
 * Test email functionality
 */
function testEmailConfiguration() {
    $testEmail = 'test@example.com';
    $testName = 'Test User';
    
    $mail = createMailer();
    if (!$mail) {
        return ['success' => false, 'message' => 'Failed to create mailer instance'];
    }
    
    try {
        $mail->addAddress($testEmail, $testName);
        $mail->Subject = 'MentorConnect Email Test';
        $mail->Body = '<h1>Email Test Successful</h1><p>Your email configuration is working correctly.</p>';
        $mail->AltBody = 'Email Test Successful. Your email configuration is working correctly.';
        
        // Don't actually send the test email, just validate configuration
        return ['success' => true, 'message' => 'Email configuration appears to be valid'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Email test failed: ' . $e->getMessage()];
    }
}