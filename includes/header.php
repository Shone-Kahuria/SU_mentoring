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
<!--
    MentorConnect Platform
    Layout: Header Template
    Features:
    - Responsive navigation
    - Dynamic asset loading
    - Session-aware menu
    - SEO optimization
-->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    <meta name="description" content="Connect with mentors and mentees in our professional mentoring platform">
    <meta name="keywords" content="mentoring, mentorship, professional development, career guidance, mentor matching">
    <meta name="author" content="MentorConnect Team">
    <meta name="theme-color" content="#0057B7">
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>images/favicon.ico">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="Professional mentoring platform connecting mentors and mentees">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="<?php echo $basePath; ?>pages/home.php" class="logo">MentorConnect</a>
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