<?php
// footer.php
// Closes the main container and includes the application script. Feel free
// to add additional scripts here. Keep scripts at the bottom for improved
// page load performance.
?>
        <?php if (!empty($_SESSION['user_id'])): ?>
        </main>
        </div> <!-- end app-container -->
        <?php else: ?>
        </main>
        <?php endif; ?>
    <!-- Bootstrap JS bundle (includes Popper); integrity attribute removed for local development -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>