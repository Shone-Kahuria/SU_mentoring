<?php
/**
 * Reset Password Page
 * Allows users to reset their password using a token
 */

require_once '../includes/functions.php';
startSession();

$pageTitle = 'Reset Password - MentorConnect';
$errors = [];
$successMessage = '';
$validToken = false;
$user = null;

// Get and validate token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $errors[] = 'Invalid or missing reset token.';
} else {
    // Check if token is valid and not expired
    $sql = "SELECT pr.*, u.id as user_id, u.full_name, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.email = u.email 
            WHERE pr.token = :token AND pr.expires_at > NOW() AND pr.used = 0";
    
    $resetRecord = selectRecord($sql, ['token' => $token]);
    
    if ($resetRecord) {
        $validToken = true;
        $user = $resetRecord;
    } else {
        $errors[] = 'Invalid or expired reset token. Please request a new password reset.';
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['success']) {
                $errors[] = $passwordValidation['message'];
            }
        }
        
        if (empty($confirmPassword)) {
            $errors[] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Reset password if no errors
        if (empty($errors)) {
            try {
                $hashedPassword = hashPassword($password);
                
                // Update user password
                $updateSuccess = updateRecord('users', 
                    ['password_hash' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = :id', 
                    ['id' => $user['user_id']]
                );
                
                if ($updateSuccess) {
                    // Mark token as used
                    updateRecord('password_resets', 
                        ['used' => 1], 
                        'token = :token', 
                        ['token' => $token]
                    );
                    
                    // Log activity
                    logActivity('password_reset_completed', [
                        'user_id' => $user['user_id'],
                        'email' => $user['email']
                    ]);
                    
                    $successMessage = 'Your password has been successfully reset. You can now log in with your new password.';
                    $validToken = false; // Hide the form
                } else {
                    $errors[] = 'Failed to reset password. Please try again.';
                }
            } catch (Exception $e) {
                error_log('Password reset error: ' . $e->getMessage());
                $errors[] = 'An error occurred while resetting your password.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Reset Password</h1>
        
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
                <div class="text-center mt-2">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        <?php elseif ($validToken && $user): ?>
            <p class="text-center mb-2">
                Hello <?php echo htmlspecialchars($user['full_name']); ?>, 
                enter your new password below.
            </p>
            
            <form method="POST" id="resetPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            required
                            minlength="8"
                            placeholder="Enter your new password"
                        >
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                    <small style="color: #666; font-size: 0.875rem;">
                        Password must be at least 8 characters with uppercase, lowercase, and number.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            required
                            placeholder="Confirm your new password"
                        >
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-error">
                <p>Invalid or expired reset link.</p>
                <div class="text-center mt-2">
                    <a href="forgot-password.php" class="btn btn-primary">Request New Reset Link</a>
                    <a href="login.php" class="btn btn-secondary">Back to Login</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    if (form) {
        // Real-time password confirmation validation
        confirmPasswordField.addEventListener('input', function() {
            if (this.value && this.value !== passwordField.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        passwordField.addEventListener('input', function() {
            if (confirmPasswordField.value && confirmPasswordField.value !== this.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        });
        
        // Form validation
        form.addEventListener('submit', function(e) {
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;
            
            let errors = [];
            
            if (password.length < 8) {
                errors.push('Password must be at least 8 characters long.');
            }
            
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                errors.push('Password must contain at least one uppercase letter, one lowercase letter, and one number.');
            }
            
            if (password !== confirmPassword) {
                errors.push('Passwords do not match.');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                showAlert(errors.join(' '), 'error');
            }
        });
        
        passwordField.focus();
    }
});
</script>

<?php include '../includes/footer.php'; ?>