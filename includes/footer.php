    </main>

    <!-- Site Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MentorConnect. All rights reserved.</p>
            <p>Connecting mentors and mentees for professional growth and development.</p>
            <!-- Social Links -->
            <div class="social-links">
                <a href="#" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="#" target="_blank" rel="noopener noreferrer">Twitter</a>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript -->
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    
    <!-- Dynamic Script Loading -->
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>