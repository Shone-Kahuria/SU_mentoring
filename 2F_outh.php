<?php
require_once 'ClassAutoLoad.php';              // autoloads $layout, $form, etc.
require_once __DIR__ . '/plugins/PHPMailer/mail.php';

session_start();

echo "<p>Starting signup process...</p>";

// --- Process form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        echo "<p>Captured user: <b>$username</b>, email: <b>$email</b></p>";
        echo "<p>(Pretend saving user to DB)</p>";

        // Generate OTP
        $otp = rand(100000, 999999);
        echo "<p>OTP generated: <b>$otp</b></p>";

        // Send OTP via PHPMailer
        $subject = "Verify your SU_mentouring Account";
        $body    = "<p>Hello <b>$username</b>,</p><p>Your verification code is: <b>$otp</b></p>";

        $result = sendMail($email, $subject, $body);

        if ($result === true) {
            $_SESSION['signup_email'] = $email;
            $_SESSION['otp'] = $otp;
            echo "<p style='color:green;'>✅ Email sent successfully to $email</p>";

            // Redirect to OTP verification page
            header("Location: verify.php");
            exit;
        } else {
            echo "<p style='color:red;'>❌ Failed to send email: $result</p>";
        }
    }
}

// --- Render the page using autoloaded instances ---
$layout->header($conf);
$form->signup();
$layout->footer($conf);
