<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Mentoring Website';
}

// Determine the base path for assets based on current directory level
$currentDir = dirname($_SERVER['PHP_SELF']);
$basePath = '';
if (strpos($currentDir, '/pages') !== false) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    <meta name="description" content="Connect with mentors and mentees in our professional mentoring platform">
    <meta name="keywords" content="mentoring, mentorship, professional development, career guidance">
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>images/favicon.ico">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="<?php echo $basePath; ?>index.php" class="logo">MentorConnect</a>
            <ul class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo $basePath; ?>pages/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo $basePath; ?>pages/profile.php">Profile</a></li>
                    <li><a href="<?php echo $basePath; ?>pages/booking.php">Booking</a></li>
                    <li><a href="<?php echo $basePath; ?>includes/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $basePath; ?>pages/home.php">Home</a></li>
                    <li><a href="<?php echo $basePath; ?>pages/login.php">Login</a></li>
                    <li><a href="<?php echo $basePath; ?>pages/signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <?php echo displayFlashMessage(); ?>