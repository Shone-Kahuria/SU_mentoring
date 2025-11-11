<?php
/**
 * Terms of Service Page
 * 
 * Displays the terms of service agreement for the SU Mentoring Platform.
 * Users must agree to these terms when signing up.
 */

require_once '../includes/functions.php';
startSession();

$pageTitle = 'Terms of Service - SU Mentoring Platform';
include '../includes/header.php';
?>

<div class="container">
    <div class="row mt-5 mb-5">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Terms of Service</h1>
            
            <p class="text-muted mb-4">Last Updated: November 11, 2025</p>

            <section class="mb-4">
                <h3>1. Acceptance of Terms</h3>
                <p>
                    By accessing and using the SU Mentoring Platform ("the Platform"), you agree to be bound by these 
                    Terms of Service. If you do not agree to abide by the above, please do not use this service.
                </p>
            </section>

            <section class="mb-4">
                <h3>2. Use of Platform</h3>
                <p>
                    The Platform is intended for educational and mentoring purposes only. You agree to use the Platform 
                    in accordance with all applicable laws and regulations, and you agree not to:
                </p>
                <ul>
                    <li>Engage in any form of harassment or abuse</li>
                    <li>Post or transmit unlawful, threatening, abusive, defamatory, obscene, or otherwise objectionable material</li>
                    <li>Disrupt the normal flow of dialogue within the Platform</li>
                    <li>Use automated systems or bots to access the Platform</li>
                    <li>Attempt to gain unauthorized access to the Platform's systems</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3>3. User Accounts</h3>
                <p>
                    When you create an account, you agree to:
                </p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain the confidentiality of your password</li>
                    <li>Accept responsibility for all activities under your account</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                </ul>
            </section>

            <section class="mb-4">
                <h3>4. Mentorship Agreement</h3>
                <p>
                    Mentorship relationships facilitated through the Platform are professional educational partnerships. 
                    The Platform does not guarantee the success of any mentorship relationship. Both mentors and mentees 
                    agree to conduct interactions respectfully and professionally.
                </p>
            </section>

            <section class="mb-4">
                <h3>5. Intellectual Property Rights</h3>
                <p>
                    All content on the Platform, including text, graphics, logos, and software, is the property of the 
                    Platform or its content suppliers and is protected by international copyright laws. You may not reproduce, 
                    distribute, or transmit any content without permission.
                </p>
            </section>

            <section class="mb-4">
                <h3>6. Limitation of Liability</h3>
                <p>
                    The Platform is provided on an "as is" basis without warranties of any kind. To the fullest extent 
                    permitted by law, the Platform shall not be liable for any indirect, incidental, special, consequential, 
                    or punitive damages resulting from your use or inability to use the Platform.
                </p>
            </section>

            <section class="mb-4">
                <h3>7. Disclaimer of Warranties</h3>
                <p>
                    The Platform makes no warranty that the service will meet your requirements, be uninterrupted, timely, 
                    or secure. Content and information are provided "as is" without warranty of completeness or accuracy.
                </p>
            </section>

            <section class="mb-4">
                <h3>8. Indemnification</h3>
                <p>
                    You agree to indemnify and hold harmless the Platform and its administrators, moderators, and contributors 
                    from any claim, damage, or loss arising from your use of the Platform or your violation of these Terms.
                </p>
            </section>

            <section class="mb-4">
                <h3>9. Termination</h3>
                <p>
                    The Platform reserves the right to terminate or suspend your account immediately if you violate these Terms 
                    or engage in any activity that violates applicable laws or regulations.
                </p>
            </section>

            <section class="mb-4">
                <h3>10. Changes to Terms</h3>
                <p>
                    The Platform reserves the right to modify these Terms at any time. Your continued use of the Platform 
                    after changes constitutes your acceptance of the new Terms.
                </p>
            </section>

            <section class="mb-4">
                <h3>11. Governing Law</h3>
                <p>
                    These Terms of Service are governed by and construed in accordance with the laws applicable to the 
                    jurisdiction in which the Platform operates.
                </p>
            </section>

            <section class="mb-4">
                <h3>12. Contact Information</h3>
                <p>
                    If you have any questions about these Terms of Service, please contact us through the Platform's 
                    support page or email the administrator.
                </p>
            </section>

            <div class="alert alert-info mt-5">
                <strong>Note:</strong> By clicking "I Agree" during signup, you confirm that you have read, understood, 
                and agree to be bound by these Terms of Service.
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
