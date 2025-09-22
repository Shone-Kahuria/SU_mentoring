<?php

$valid_username = "Mentor 1";
$valid_password = "mentor123";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === $valid_username && $password === $valid_password) {
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mentorship Programme Login</title>
    <style>
        body {
            background: #87ceeb;
            color: #000;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #fff;
            max-width: 400px;
            margin: 80px auto;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px #0002;
        }
        h2 {
            color: #e53935;
            text-align: center;
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #000;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #87ceeb;
            border-radius: 4px;
        }
        .btn {
            background: #e53935;
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background: #000; 
        }
        .error {
            color: #e53935;
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>SU Mentorship Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
