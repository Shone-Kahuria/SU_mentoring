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

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Welcome Back</h1>
        <p class="text-center mb-2">Sign in to your MentorConnect account</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email Address</label>
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
            
            <!-- Password Field -->
            <div class="form-group">
                <label for="password">Password</label>
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
                    <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                </div>
            </div>
            
            <!-- Remember Me and Forgot Password -->
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    <input type="checkbox" name="remember_me" id="remember_me">
                    <span>Remember me for 30 days</span>
                </label>
                <a href="forgot-password.php" class="link">Forgot password?</a>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">Sign In</button>
        </form>
        
        <div class="text-center mt-2">
            <p>Don't have an account? <a href="signup.php" class="link">Create one here</a></p>
        </div>
        
        <!-- Demo Accounts (Remove in production) -->
        <div class="alert alert-warning mt-2">
            <strong>Demo Accounts:</strong><br>
            <small>
                Mentor: admin@mentoring.com | Password: Admin123!<br>
                (Create additional accounts via signup)
            </small>
        </div>
    </div>
</div>

<script>
// Enhanced login form functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    const rememberCheckbox = document.getElementById('remember_me');
    
    // Auto-focus email field
    emailField.focus();
    
    // Enhanced form validation
    form.addEventListener('submit', function(e) {
        const email = emailField.value.trim();
        const password = passwordField.value;
        
        let errors = [];
        
        if (!email) {
            errors.push('Email is required.');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Please enter a valid email address.');
        }
        
        if (!password) {
            errors.push('Password is required.');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            showAlert(errors.join(' '), 'error');
            return false;
        }
        
        // Show loading state
        loginBtn.disabled = true;
        loginBtn.textContent = 'Signing in...';
        
        // Re-enable button after 5 seconds (in case of server issues)
        setTimeout(() => {
            loginBtn.disabled = false;
            loginBtn.textContent = 'Sign In';
        }, 5000);
    });
    
    // Enter key navigation
    emailField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            passwordField.focus();
        }
    });
    
    passwordField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            form.submit();
        }
    });
    
    // Remember me tooltip
    rememberCheckbox.addEventListener('mouseenter', function() {
        this.title = 'Keep me signed in on this device for 30 days';
    });
    
    // Demo account quick fill (remove in production)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('demo') === 'mentor') {
        emailField.value = 'admin@mentoring.com';
        passwordField.value = 'Admin123!';
    }
    
    // Clear form on page refresh (security)
    window.addEventListener('beforeunload', function() {
        if (passwordField.value) {
            passwordField.value = '';
        }
    });
});

// Add demo account quick fill buttons (remove in production)
function fillDemo(type) {
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    if (type === 'mentor') {
        emailField.value = 'admin@mentoring.com';
        passwordField.value = 'Admin123!';
    }
    
    passwordField.focus();
}
</script>

<?php include '../includes/footer.php'; ?>