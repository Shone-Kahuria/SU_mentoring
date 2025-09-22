<?php
// signup.php

// --- Configuration ---
$conf = [
    'site_name' => 'SU Mentorship'
];

// --- Layout class ---
class Layout {
    public function header($conf) {
        ?>
        <header class="site-header">
            <h1>Welcome to <?php echo $conf['site_name']; ?></h1>
        </header>
        <?php
    }

    public function footer($conf) {
        ?>
        <footer class="site-footer">
            <p>&copy; <?php echo date("Y"); ?> <?php echo $conf['site_name']; ?> - All Rights Reserved</p>
        </footer>
        <?php
    }
}

// --- Form class ---
class Form {
    public function signup() {
        ?>
        <form method="post" action="signup.php" class="signup-form">
            <h2>Create Account</h2>
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>

            <input type="submit" value="Sign Up" class="btn">
            <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>
        </form>
        <?php
    }
}

// --- Instantiate classes ---
$layout = new Layout();
$form = new Form();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - <?php echo $conf['site_name']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #c0e0ff, #e6f0ff);
            margin: 0;
            display: flex;
            flex-direction: column; /* Stack header → form → footer vertically */
            align-items: center;
            min-height: 100vh;
        }

        .site-header, .site-footer {
            text-align: center;
            width: 100%;
            padding: 20px 0;
            background: rgba(0, 123, 255, 0.05);
        }

        .signup-form {
            background: rgba(0, 123, 255, 0.1); /* light bluish transparent */
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 340px;
            display: flex;
            flex-direction: column;
            margin: 40px 0; /* spacing between header/footer */
        }

        .signup-form h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #007BFF;
            font-weight: 600;
        }

        label {
            margin-bottom: 5px;
            color: #004080;
            font-weight: 500;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            transition: 0.2s;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .btn {
            padding: 12px;
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

        .login-link {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .login-link a {
            color: #004080;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    $layout->header($conf);
    $form->signup();
    $layout->footer($conf);
    ?>
</body>
</html>
