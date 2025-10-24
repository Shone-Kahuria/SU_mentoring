<?php
/**
 * Login Page for Mentoring Website
 * Developer: Gabriel
 * 
 * Features:
 * - Standard email/password login form
 * - PHP + MySQL authentication
 * - Role-based dashboard redirects
 * - JavaScript password toggle
 * - Remember me functionality
 */

require_once '../includes/functions.php';
startSession();

$pageTitle = 'Login - MentorConnect';
$errors = [];
$email = '';

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
        // Sanitize input
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Basic validation
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        
        // Rate limiting check
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!checkRateLimit('login_' . $clientIP, 5, 300)) {
            $errors[] = 'Too many login attempts. Please try again in a few minutes.';
        }
        
        // Authenticate user if no validation errors
        if (empty($errors)) {
            try {
                // Get user from database
                $user = getUserByEmail($email);
                
                if ($user && $user['is_active'] && verifyPassword($password, $user['password_hash'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_gender'] = strtolower($user['gender'] ?? '');
                    
                    // Update last login time
                    updateRecord('users', 
                        ['last_login' => date('Y-m-d H:i:s')], 
                        'id = :id', 
                        ['id' => $user['id']]
                    );
                    
                    // Handle "Remember Me" functionality
                    if ($rememberMe) {
                        $sessionToken = generateRandomString(64);
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        // Store session token in database
                        insertRecord('user_sessions', [
                            'user_id' => $user['id'],
                            'session_token' => $sessionToken,
                            'expires_at' => $expiresAt,
                            'ip_address' => $clientIP,
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                        ]);
                        
                        // Set secure cookie
                        setcookie('remember_token', $sessionToken, [
                            'expires' => strtotime('+30 days'),
                            'path' => '/',
                            'domain' => '',
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]);
                    }
                    
                    // Log successful login
                    logActivity('user_login', [
                        'user_id' => $user['id'],
                        'role' => $user['role'],
                        'ip_address' => $clientIP,
                        'remember_me' => $rememberMe
                    ]);
                    
                    // Role-based redirect
                    $redirectUrl = 'dashboard.php';
                    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                        $redirectUrl = $_GET['redirect'];
                    }
                    
                    setFlashMessage('Welcome back, ' . htmlspecialchars($user['full_name']) . '!', 'success');
                    redirect($redirectUrl);
                    
                } else {
                    // Invalid credentials
                    $errors[] = 'Invalid email or password.';
                    
                    // Log failed login attempt
                    logActivity('failed_login', [
                        'email' => $email,
                        'ip_address' => $clientIP
                    ]);
                }
            } catch (Exception $e) {
                error_log('Login error: ' . $e->getMessage());
                $errors[] = 'An error occurred during login. Please try again.';
            }
        }
    }
}

// Check for remember me token on page load
if (empty($_POST) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $sql = "SELECT u.*, s.session_token FROM users u 
            JOIN user_sessions s ON u.id = s.user_id 
            WHERE s.session_token = :token AND s.expires_at > NOW() AND u.is_active = 1";
    
    $result = selectRecord($sql, ['token' => $token]);
    
    if ($result) {
        // Auto-login user
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_role'] = $result['role'];
        $_SESSION['user_name'] = $result['full_name'];
        $_SESSION['user_email'] = $result['email'];
    $_SESSION['user_gender'] = strtolower($result['gender'] ?? '');
        
        // Update last login
        updateRecord('users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $result['id']]
        );
        
        logActivity('auto_login', ['user_id' => $result['id']]);
        redirect('dashboard.php');
    } else {
        // Invalid or expired token, remove cookie
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>

<?php
// Set page variables for modern template
$page_title = 'Welcome Back';
$show_nav = true;
$base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/pages');

// Include modern header
require_once '../includes/modern_header.php';
?>

<div class="card">
    <div class="text-center mb-4">
        <h2>Sign in to your account</h2>
        <p class="text-muted">Welcome back to SU Mentoring</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> Email Address
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                value="<?php echo htmlspecialchars($email); ?>"
                required
                autocomplete="email"
                placeholder="Enter your email address"
            >
        </div>
        
        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> Password
            </label>
            <div class="password-container">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >
                <button type="button" class="btn btn-outline password-toggle" aria-label="Show password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        
        <div class="form-group d-flex justify-content-between align-items-center">
            <label class="d-flex align-items-center gap-3">
                <input type="checkbox" name="remember_me" id="remember_me">
                <span>Remember me</span>
            </label>
            <a href="forgot-password.php" class="nav-link">Forgot password?</a>
        </div>
        
        <div class="form-group d-flex justify-content-between gap-3">
            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            <a href="signup.php" class="btn btn-outline">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </div>
    </form>

    <?php if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT): ?>
    <div class="alert alert-warning mt-4">
        <h4 class="mb-2"><i class="fas fa-info-circle"></i> Demo Accounts</h4>
        <p class="mb-0"><strong>Mentor:</strong> admin@mentoring.com<br>
        <strong>Password:</strong> Admin123!</p>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    const passwordToggle = document.querySelector('.password-toggle');
    
    // Auto-focus email field on load
    emailField.focus();
    
    // Password visibility toggle
    passwordToggle.addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        
        // Update icon
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Form validation and submission
    form.addEventListener('submit', function(e) {
        const email = emailField.value.trim();
        const password = passwordField.value;
        
        let errors = [];
        
        if (!email) {
            errors.push('Email is required');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Please enter a valid email address');
        }
        
        if (!password) {
            errors.push('Password is required');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `
                <ul style="margin: 0; padding-inline-start: 20px;">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            `;
            
            // Remove any existing alerts
            const existingAlerts = form.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Insert new alert at the top of the form
            form.insertBefore(alertDiv, form.firstChild);
            return;
        }
        
        // Show loading state
        loginBtn.disabled = true;
        const originalContent = loginBtn.innerHTML;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        
        // Re-enable button after timeout (in case of server issues)
        setTimeout(() => {
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalContent;
        }, 5000);
    });
    
    // Enter key navigation
    emailField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            passwordField.focus();
        }
    });
    
    // Clear sensitive data on page unload
    window.addEventListener('beforeunload', function() {
        passwordField.value = '';
    });
    
    // Smooth transition for alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.3s ease-in-out';
    });
});
</script>

<?php require_once '../includes/modern_header.php'; ?>