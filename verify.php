<?php
require_once 'ClassAutoLoad.php'; // Autoload project classes
require_once __DIR__ . '/plugins/PHPMailer/mail.php'; // Include mail sending function

session_start(); // Start session to access session variables (like OTP)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <style>
        /* Page styling for OTP form */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #c0e0ff, #e6f0ff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .otp-container {
            background: rgba(0, 123, 255, 0.1);
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 340px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .otp-container h2 {
            color: #007BFF;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            font-weight: 500;
            color: #004080;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            transition: 0.2s;
        }

        input[type="text"]:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .btn {
            padding: 12px;
            width: 100%;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.2s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            margin-bottom: 15px;
            font-weight: 500;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <h2>OTP Verification</h2>

        <?php
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $enteredOtp = $_POST['otp'] ?? ''; // Get OTP entered by user
            $expectedOtp = $_SESSION['otp'] ?? null; // Get OTP stored in session

            // Compare entered OTP with expected OTP
            if ($enteredOtp === $expectedOtp) {
                // OTP is correct
                echo "<p class='message success'>✅ Verification successful! Your account is activated.</p>";
            } else {
                // OTP is incorrect
                echo "<p class='message error'>❌ Invalid OTP. Please try again.</p>";
            }
        }
        ?>

        <!-- OTP input form -->
        <form method="post" action="">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>
            <input type="submit" value="Verify" class="btn">
        </form>
    </div>
</body>
</html>
