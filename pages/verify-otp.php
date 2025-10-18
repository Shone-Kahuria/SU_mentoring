<?php
require_once '../includes/functions.php';
startSession();

// Redirect if not coming from signup
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['otp']) || !isset($_SESSION['otp_time'])) {
    redirect('signup.php');
}

$pageTitle = 'Verify Account - MentorConnect';
$errors = [];

// Process OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = $_POST['otp'] ?? '';
    
    // Validate OTP
    if (empty($enteredOtp)) {
        $errors[] = 'Please enter the verification code.';
    } elseif ($enteredOtp !== $_SESSION['otp']) {
        $errors[] = 'Invalid verification code. Please try again.';
    } elseif (time() - $_SESSION['otp_time'] > 600) { // 10 minutes expiry
        $errors[] = 'Verification code has expired. Please sign up again.';
        // Clear session and redirect
        session_destroy();
        redirect('signup.php');
    }
    
    // If OTP is valid
    if (empty($errors)) {
        // Activate the user account
        $userId = $_SESSION['temp_user_id'];
        // Update the user's email_verified flag. updateRecord signature is: (table, dataArray, whereClause, whereParams)
        $updated = updateRecord('users', ['email_verified' => 1], 'id = :id', ['id' => $userId]);
        if (!$updated) {
            $errors[] = 'Failed to verify account. Please contact support.';
        }
        
        // Clear verification session data
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['otp']);
        unset($_SESSION['otp_time']);
        
        // Set success message and redirect to login
        setFlashMessage('Account verified successfully! Please log in to continue.', 'success');
        redirect('login.php');
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h1 class="form-title">Verify Your Account</h1>
        <p class="text-center mb-2">Enter the verification code sent to your email</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="verify-otp.php" class="otp-form">
            <div class="form-group">
                <label for="otp">Verification Code</label>
                <input type="text" 
                       id="otp" 
                       name="otp" 
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       title="Please enter the 6-digit code"
                       required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Verify Account</button>
        </form>
        
        <p class="text-center mt-2">
            Didn't receive the code? <a href="signup.php">Sign up again</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>