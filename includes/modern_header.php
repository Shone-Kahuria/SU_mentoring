<?php
/**
 * Modern Header Template
 * Backwards-compatible helpers: some pages include this header
 * but may use different sets of utility functions (auth_* vs isLoggedIn()).
 * Provide small wrapper functions when missing so templates don't fatal.
 */
// Load init.php first for base configuration
require_once __DIR__ . '/init.php';

// Then load auth helpers
require_once __DIR__ . '/auth.php';

// If the project defines isLoggedIn()/getCurrentUserRole()/getFlashMessage()
// but not the auth_* wrappers, define them to keep templates compatible.
if (!function_exists('auth_is_logged_in') && function_exists('isLoggedIn')) {
    function auth_is_logged_in() {
        return isLoggedIn();
    }
}

if (!function_exists('auth_get_user_role') && function_exists('getCurrentUserRole')) {
    function auth_get_user_role() {
        return getCurrentUserRole();
    }
}

if (!function_exists('auth_get_flash') && function_exists('getFlashMessage')) {
    function auth_get_flash() {
        return getFlashMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'SU Mentoring'; ?></title>
    
    <!-- Modern CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/modern.css">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php if (isset($show_nav) && $show_nav): ?>
    <nav class="nav-menu">
        <a href="<?php echo $base_url; ?>/pages/home.php" class="nav-link">
            <i class="fas fa-home"></i> Home
        </a>
        <?php if (auth_is_logged_in()): ?>
            <a href="<?php echo $base_url; ?>/pages/dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <?php if (auth_get_user_role() === 'mentee'): ?>
                <a href="<?php echo $base_url; ?>/pages/find-mentor.php" class="nav-link">
                    <i class="fas fa-search"></i> Find Mentor
                </a>
            <?php endif; ?>
            <a href="<?php echo $base_url; ?>/pages/profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="<?php echo $base_url; ?>/includes/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>/pages/login.php" class="nav-link">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="<?php echo $base_url; ?>/pages/signup.php" class="nav-link">
                <i class="fas fa-user-plus"></i> Sign Up
            </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <div class="container">
        <?php if (isset($show_page_header) && $show_page_header && isset($page_title)): ?>
        <header class="page-header">
            <h1><?php echo $page_title; ?></h1>
        </header>
        <?php endif; ?>

        <?php
        // Display flash messages if any
        $flash = auth_get_flash();
        if ($flash): 
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
        <?php endif; ?>