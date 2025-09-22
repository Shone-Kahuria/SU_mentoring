<?php
/**
 * Home Page for Mentoring Website
 * Landing page for visitors and redirects for logged-in users
 */

require_once 'includes/functions.php';
startSession();

// Development mode test links (remove in production)
if (isset($_GET['dev']) && $_GET['dev'] === 'test') {
    echo '<!DOCTYPE html><html><head><title>Development Tests</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css"></head><body>';
    echo '<div class="container" style="padding: 2rem;">';
    echo '<h1>Development Test Links</h1>';
    echo '<div style="display: flex; gap: 1rem; flex-wrap: wrap; margin: 2rem 0;">';
    echo '<a href="test_database.php" class="btn btn-primary">Test Database</a>';
    echo '<a href="test_email.php" class="btn btn-primary">Test Email System</a>';
    echo '<a href="pages/signup.php" class="btn">Test Signup</a>';
    echo '<a href="pages/login.php" class="btn">Test Login</a>';
    echo '<a href="pages/forgot-password.php" class="btn">Test Password Reset</a>';
    echo '</div>';
    echo '<p><strong>Note:</strong> This development panel is only visible with ?dev=test parameter.</p>';
    echo '<p><a href="index.php">← Back to normal site</a></p>';
    echo '</div></body></html>';
    exit;
}

// Redirect logged-in users to dashboard
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
}

// Redirect all users to the home page in pages folder
header('Location: pages/home.php');
exit;
?>

<?php include 'includes/header.php'; ?>

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
                <a href="about.php" class="btn btn-primary mt-1">Learn More</a>
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

    <!-- Testimonials -->
    <div class="dashboard-card">
        <div class="card-title text-center">What Our Users Say</div>
        <div class="card-content">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary-blue);">
                    <p style="font-style: italic; margin-bottom: 1rem;">"MentorConnect helped me find an amazing mentor who guided me through my career transition. The platform is easy to use and the mentors are incredibly knowledgeable."</p>
                    <div style="font-weight: bold; color: var(--primary-blue);">- Sarah Johnson, Software Developer</div>
                </div>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary-red);">
                    <p style="font-style: italic; margin-bottom: 1rem;">"As a mentor, I love being able to share my experience and help others grow. The scheduling system makes it easy to manage my mentoring sessions."</p>
                    <div style="font-weight: bold; color: var(--primary-red);">- Michael Chen, Senior Manager</div>
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

<script>
// Add some interactive elements
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add animation to cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards
    document.querySelectorAll('.dashboard-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

<?php include 'includes/footer.php'; ?>