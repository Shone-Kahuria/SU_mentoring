<?php
/**
 * Home Page (pages/home.php)
 * Landing page with organized structure
 */

require_once '../includes/auth.php';

// Redirect logged-in users to dashboard
if (auth_is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'MentorConnect - Professional Mentoring Platform';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="description" content="Connect with mentors and mentees in our professional mentoring platform">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="../index.php" class="logo">MentorConnect</a>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <?php echo auth_display_flash(); ?>

        <div class="container">
            <!-- Hero Section -->
            <div style="text-align: center; padding: 4rem 0; background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-red) 100%); color: var(--primary-white); margin: -2rem -20px 3rem; border-radius: 10px;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: bold;">Welcome to MentorConnect</h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">Connect with experienced mentors or guide the next generation of professionals</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="signup.php" class="btn" style="background: var(--primary-white); color: var(--primary-blue); font-weight: bold;">Get Started</a>
                    <a href="login.php" class="btn" style="background: transparent; color: var(--primary-white); border: 2px solid var(--primary-white);">Sign In</a>
                </div>
            </div>

            <!-- Features Section -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-title text-blue">For Mentees</div>
                    <div class="card-content">
                        <h3>Accelerate Your Growth</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin: 0.5rem 0;">✓ Connect with industry experts</li>
                            <li style="margin: 0.5rem 0;">✓ Get personalized career guidance</li>
                            <li style="margin: 0.5rem 0;">✓ Schedule flexible mentoring sessions</li>
                            <li style="margin: 0.5rem 0;">✓ Track your progress and goals</li>
                        </ul>
                        <a href="signup.php" class="btn btn-primary mt-1">Find a Mentor</a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-title text-red">For Mentors</div>
                    <div class="card-content">
                        <h3>Share Your Expertise</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin: 0.5rem 0;">✓ Guide the next generation</li>
                            <li style="margin: 0.5rem 0;">✓ Flexible scheduling options</li>
                            <li style="margin: 0.5rem 0;">✓ Build meaningful connections</li>
                            <li style="margin: 0.5rem 0;">✓ Make a lasting impact</li>
                        </ul>
                        <a href="signup.php" class="btn btn-secondary mt-1">Become a Mentor</a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-title text-blue">Platform Features</div>
                    <div class="card-content">
                        <h3>Everything You Need</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin: 0.5rem 0;">✓ Secure messaging system</li>
                            <li style="margin: 0.5rem 0;">✓ Video session scheduling</li>
                            <li style="margin: 0.5rem 0;">✓ Progress tracking</li>
                            <li style="margin: 0.5rem 0;">✓ Goal setting tools</li>
                        </ul>
                        <a href="../about.php" class="btn btn-primary mt-1">Learn More</a>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="dashboard-card">
                <div class="card-title text-center">How It Works</div>
                <div class="card-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
                        <div>
                            <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">1</div>
                            <h4>Sign Up</h4>
                            <p>Create your account as a mentor or mentee and complete your profile.</p>
                        </div>
                        <div>
                            <div style="width: 60px; height: 60px; background: var(--primary-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">2</div>
                            <h4>Connect</h4>
                            <p>Find the perfect mentor or mentee match based on your interests and goals.</p>
                        </div>
                        <div>
                            <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">3</div>
                            <h4>Grow</h4>
                            <p>Schedule sessions, set goals, and track your progress on your mentoring journey.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div style="text-align: center; padding: 3rem 0; background: var(--primary-white); border: 2px solid var(--primary-blue); border-radius: 10px; margin: 2rem 0;">
                <h2 style="color: var(--primary-blue); margin-bottom: 1rem;">Ready to Start Your Mentoring Journey?</h2>
                <p style="font-size: 1.1rem; margin-bottom: 2rem; color: var(--text-dark);">Join thousands of professionals who are already growing through mentoring</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="signup.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 30px;">Sign Up Free</a>
                    <a href="login.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 15px 30px;">Login</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MentorConnect. All rights reserved.</p>
            <p>Connecting mentors and mentees for professional growth and development.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>