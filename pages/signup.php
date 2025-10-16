<?php
/**
 * Signup Page for Mentoring Website
 * Developer: Alpha
 * 
 * Features:
 * - User registration as mentor or mentee
 * - Form validation (client + server-side)
 * - Password hashing and secure storage
 * - Redirect to login on success
 */

require_once '../includes/functions.php';
require_once '../plugins/PHPMailer/mail.php'; // Add PHPMailer mail functions
startSession();

$pageTitle = 'Sign Up - MentorConnect';
$errors = [];
$formData = [];

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input data
        $formData = [
            'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'gender' => strtolower(sanitizeInput($_POST['gender'] ?? '')),
            'role' => sanitizeInput($_POST['role'] ?? '')
        ];
        
        // Server-side validation
        if (empty($formData['full_name'])) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($formData['full_name']) < 2) {
            $errors[] = 'Full name must be at least 2 characters long.';
        } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $formData['full_name'])) {
            $errors[] = 'Full name can only contain letters, spaces, hyphens, and apostrophes.';
        }
        
        if (empty($formData['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($formData['email'])) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (emailExists($formData['email'])) {
            $errors[] = 'An account with this email already exists.';
        }
        
        if (empty($formData['password'])) {
            $errors[] = 'Password is required.';
        } else {
            $passwordValidation = validatePassword($formData['password']);
            if (!$passwordValidation['success']) {
                $errors[] = $passwordValidation['message'];
            }
        }
        
        if (empty($formData['confirm_password'])) {
            $errors[] = 'Please confirm your password.';
        } elseif ($formData['password'] !== $formData['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($formData['gender']) || !isValidGender($formData['gender'])) {
            $errors[] = 'Please select a valid gender.';
        }

        if (empty($formData['role']) || !in_array($formData['role'], ['mentor', 'mentee'])) {
            $errors[] = 'Please select a valid role.';
        }
        
        // Rate limiting check
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!checkRateLimit('signup_' . $clientIP, 5, 300)) {
            $errors[] = 'Too many signup attempts. Please try again in a few minutes.';
        }
        
        // If no errors, create the user
        if (empty($errors)) {
            try {
                $userData = [
                    'full_name' => $formData['full_name'],
                    'email' => $formData['email'],
                    'gender' => $formData['gender'],
                    'password_hash' => hashPassword($formData['password']),
                    'role' => $formData['role'],
                    'email_verification_token' => generateRandomString(64),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $userId = insertRecord('users', $userData);
                
                if ($userId) {
                    // Send OTP email for verification
                    $result = sendOTP($formData['email']);
                    
                        if (is_array($result) && isset($result['otp'])) {
                            // Store OTP and user ID in session for verification
                            $_SESSION['temp_user_id'] = $userId;
                            $_SESSION['otp'] = $result['otp'];
                            $_SESSION['otp_time'] = time();
                        
                            // Log the signup activity
                            logActivity('user_signup', [
                                'user_id' => $userId,
                                'role' => $formData['role'],
                                'gender' => $formData['gender'],
                                'ip_address' => $clientIP
                            ]);
                        
                            // Set success message and redirect
                            setFlashMessage('Account created! Please enter the verification code sent to your email.', 'success');
                            ob_end_clean(); // Clear any output buffers
                            redirect('verify-otp.php');
                        } else {
                            $errors[] = 'Failed to send verification code. Please try again.';
                        }
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }
            } catch (Exception $e) {
                error_log('Signup error: ' . $e->getMessage());
                $errors[] = 'An error occurred while creating your account. Please try again.';
            }
        }
    }
}

// Generate CSRF token for the form
$csrfToken = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Join MentorConnect</h1>
        <p class="text-center mb-2">Create your account to start your mentoring journey</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="signup.php" id="signupForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Full Name Field -->
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                    required
                    maxlength="255"
                    placeholder="Enter your full name"
                >
            </div>
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                    required
                    maxlength="255"
                    placeholder="Enter your email address"
                >
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required
                        minlength="8"
                        placeholder="Enter a strong password"
                    >
                    <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                </div>
                <small style="color: #666; font-size: 0.875rem;">
                    Password must be at least 8 characters with uppercase, lowercase, and number.
                </small>
            </div>
            
            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        required
                        placeholder="Confirm your password"
                    >
                    <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                </div>
            </div>

            <!-- Gender Selection -->
            <div class="form-group">
                <label>Gender *</label>
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="radio" name="gender" value="male" 
                               <?php echo (($formData['gender'] ?? '') === 'male') ? 'checked' : ''; ?> required>
                        <span>Male</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="radio" name="gender" value="female" 
                               <?php echo (($formData['gender'] ?? '') === 'female') ? 'checked' : ''; ?> required>
                        <span>Female</span>
                    </label>
                </div>
            </div>
            
            <!-- Role Selection -->
            <div class="form-group">
                <label>Choose Your Role *</label>
                <div class="role-selection">
                    <div class="role-option <?php echo (($formData['role'] ?? '') === 'mentor') ? 'selected' : ''; ?>">
                        <input type="radio" name="role" value="mentor" id="role_mentor" 
                               <?php echo (($formData['role'] ?? '') === 'mentor') ? 'checked' : ''; ?> required>
                        <div class="role-title">Mentor</div>
                        <div class="role-description">Share your expertise and guide others in their professional journey</div>
                    </div>
                    <div class="role-option <?php echo (($formData['role'] ?? '') === 'mentee') ? 'selected' : ''; ?>">
                        <input type="radio" name="role" value="mentee" id="role_mentee" 
                               <?php echo (($formData['role'] ?? '') === 'mentee') ? 'checked' : ''; ?> required>
                        <div class="role-title">Mentee</div>
                        <div class="role-description">Learn from experienced professionals and accelerate your growth</div>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" required>
                    <span>I agree to the <a href="terms.php" class="link" target="_blank">Terms of Service</a> and <a href="privacy.php" class="link" target="_blank">Privacy Policy</a></span>
                </label>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        
        <div class="text-center mt-2">
            <p>Already have an account? <a href="login.php" class="link">Sign in here</a></p>
        </div>
    </div>
</div>

<script>
// Additional client-side validation for signup form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
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
    
    // Enhanced form submission
    form.addEventListener('submit', function(e) {
        // Additional client-side checks
        const fullName = document.getElementById('full_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
    const gender = document.querySelector('input[name="gender"]:checked');
        const role = document.querySelector('input[name="role"]:checked');
        
        let errors = [];
        
        if (fullName.length < 2) {
            errors.push('Full name must be at least 2 characters long.');
        }
        
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Please enter a valid email address.');
        }
        
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long.');
        }
        
        if (password !== confirmPassword) {
            errors.push('Passwords do not match.');
        }
        
        if (!gender) {
            errors.push('Please select your gender.');
        }

        if (!role) {
            errors.push('Please select your role.');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            showAlert(errors.join(' '), 'error');
        }
    });
    
    // Auto-focus first field
    document.getElementById('full_name').focus();
});
</script>

<?php include '../includes/footer.php'; ?>