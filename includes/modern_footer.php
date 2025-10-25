    </div><!-- /.container -->

    <footer class="py-4 mt-4" style="background-color: var(--light-color);">
        <div class="container">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> SU Mentoring. All rights reserved.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="nav-link">Privacy Policy</a>
                    <a href="#" class="nav-link">Terms of Service</a>
                    <a href="#" class="nav-link">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Add any JavaScript here -->
    <script>
        // Add any common JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>