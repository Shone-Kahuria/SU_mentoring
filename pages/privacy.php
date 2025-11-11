<?php
/**
 * Privacy Policy Page
 * 
 * Displays the privacy policy for the SU Mentoring Platform.
 * Users must agree to the privacy policy when signing up.
 */

require_once '../includes/functions.php';
startSession();

$pageTitle = 'Privacy Policy - SU Mentoring Platform';
include '../includes/header.php';
?>

<div class="container">
    <div class="row mt-5 mb-5">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Privacy Policy</h1>
            
            <p class="text-muted mb-4">Last Updated: November 11, 2025</p>

            <section class="mb-4">
                <h3>1. Introduction</h3>
                <p>
                    The SU Mentoring Platform ("we," "us," "our," or the "Platform") is committed to protecting your privacy. 
                    This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use 
                    our Platform.
                </p>
            </section>

            <section class="mb-4">
                <h3>2. Information We Collect</h3>
                <p>We collect information you provide directly, such as:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, password, phone number, date of birth, gender</li>
                    <li><strong>Profile Information:</strong> Bio, skills, experience level, education background, expertise areas</li>
                    <li><strong>Mentorship Information:</strong> Mentorship goals, availability, preferences, schedules</li>
                    <li><strong>Communication Data:</strong> Messages, session notes, feedback, ratings</li>
                    <li><strong>Technical Information:</strong> IP address, browser type, device information, usage patterns</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3>3. How We Use Your Information</h3>
                <p>We use the information we collect for the following purposes:</p>
                <ul>
                    <li>To create and maintain your account</li>
                    <li>To facilitate mentorship connections between users</li>
                    <li>To communicate with you about your account and mentorship activities</li>
                    <li>To send administrative notifications and updates</li>
                    <li>To improve and optimize the Platform's features and performance</li>
                    <li>To monitor and prevent fraudulent activities and security threats</li>
                    <li>To comply with legal obligations and enforce our Terms of Service</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3>4. Information Sharing</h3>
                <p>
                    We do not sell, trade, or rent your personal information to third parties. However, we may share your 
                    information in the following circumstances:
                </p>
                <ul>
                    <li><strong>Mentorship Partners:</strong> Limited information is shared with your mentor or mentee to facilitate the mentorship relationship</li>
                    <li><strong>Service Providers:</strong> Third parties who help us operate the Platform (e.g., email providers)</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our legal rights</li>
                    <li><strong>Platform Administrators:</strong> For support, troubleshooting, and platform management</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3>5. Data Security</h3>
                <p>
                    We implement appropriate technical and organizational security measures to protect your personal information 
                    against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over 
                    the Internet is 100% secure, and we cannot guarantee absolute security.
                </p>
            </section>

            <section class="mb-4">
                <h3>6. Data Retention</h3>
                <p>
                    We retain your personal information for as long as necessary to provide our services and fulfill the purposes 
                    outlined in this Privacy Policy. You may request deletion of your account and associated data at any time.
                </p>
            </section>

            <section class="mb-4">
                <h3>7. Your Rights and Choices</h3>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate information</li>
                    <li>Request deletion of your information</li>
                    <li>Opt-out of non-essential communications</li>
                    <li>Request a copy of your data in a portable format</li>
                </ul>
                <p>
                    To exercise any of these rights, please contact us through the Platform's support page or email the administrator.
                </p>
            </section>

            <section class="mb-4">
                <h3>8. Cookies and Tracking</h3>
                <p>
                    The Platform uses cookies and similar tracking technologies to enhance your experience. Cookies help us remember 
                    your preferences and improve our services. You can disable cookies through your browser settings, though some 
                    features may not function properly.
                </p>
            </section>

            <section class="mb-4">
                <h3>9. Third-Party Links</h3>
                <p>
                    The Platform may contain links to third-party websites. We are not responsible for the privacy practices of 
                    external websites. We encourage you to review their privacy policies before providing personal information.
                </p>
            </section>

            <section class="mb-4">
                <h3>10. Children's Privacy</h3>
                <p>
                    The Platform is not intended for use by individuals under the age of 18. We do not knowingly collect personal 
                    information from children under 18. If we become aware of such collection, we will promptly delete the information.
                </p>
            </section>

            <section class="mb-4">
                <h3>11. Changes to This Privacy Policy</h3>
                <p>
                    We may update this Privacy Policy periodically to reflect changes in our practices or applicable laws. We will 
                    notify you of any material changes by posting the updated policy on the Platform.
                </p>
            </section>

            <section class="mb-4">
                <h3>12. Contact Us</h3>
                <p>
                    If you have questions about this Privacy Policy or our privacy practices, please contact us through the 
                    Platform's support page or email the administrator.
                </p>
            </section>

            <div class="alert alert-info mt-5">
                <strong>Note:</strong> By using the Platform, you consent to our collection and use of information as described 
                in this Privacy Policy.
            </div>

            <div class="mt-4 mb-5">
                <a href="signup.php" class="btn btn-primary">Back to Sign Up</a>
                <a href="home.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
