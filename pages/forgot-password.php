<?php
/**
 * Forgot Password Page
 * Allows users to request password reset emails
 */

require_once '../includes/functions.php';
startSession();

$pageTitle = 'Forgot Password - MentorConnect';
$errors = [];
$successMessage = '';

// Process password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            // Check if user exists
            $user = getUserByEmail($email);
            
            if ($user) {
                // Generate reset token
                $resetToken = generateRandomString(64);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Store reset token in database
                $tokenData = [
                    'email' => $email,
                    'token' => $resetToken,
                    'expires_at' => $expiresAt
                ];
                
                // Create password_resets table entry
                $success = insertRecord('password_resets', $tokenData);
                
                if ($success) {
                    // Send password reset email
                    $emailSent = sendPasswordResetEmail($email, $user['full_name'], $resetToken);
                    
                    if ($emailSent) {
                        $successMessage = 'Password reset instructions have been sent to your email address.';
                        
                        // Log activity
                        logActivity('password_reset_requested', [
                            'user_id' => $user['id'],
                            'email' => $email
                        ]);
                    } else {
                        $errors[] = 'Failed to send reset email. Please try again later.';
                    }
                } else {
                    $errors[] = 'Failed to process reset request. Please try again.';
                }
            } else {
                // Don't reveal if email exists or not for security
                $successMessage = 'If an account with that email exists, password reset instructions have been sent.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Forgot Password</h1>
        <p class="text-center mb-2">Enter your email address and we'll send you instructions to reset your password.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
                <p class="mt-1">
                    <a href="login.php" class="link">Return to login</a>
                </p>
            </div>
        <?php else: ?>
            <form method="POST" id="forgotPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        required
                        autocomplete="email"
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Send Reset Instructions</button>
            </form>
            
            <div class="text-center mt-2">
                <p>Remember your password? <a href="login.php" class="link">Sign in here</a></p>
                <p>Don't have an account? <a href="signup.php" class="link">Create one here</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const emailField = document.getElementById('email');
    
    if (emailField) {
        emailField.focus();
        
        form.addEventListener('submit', function(e) {
            const email = emailField.value.trim();
            
            if (!email) {
                e.preventDefault();
                showAlert('Please enter your email address.', 'error');
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'error');
                return;
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>