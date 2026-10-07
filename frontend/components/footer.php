    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-brand-header">
                        <div class="footer-logo">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/logo.png" alt="Connect MyUni Logo">
                        </div>
                        <div class="footer-brand-text">
                            <h3 class="footer-title">Connect MyUni</h3>
                            <p class="footer-tagline">Your Gateway to Global Education. We connect ambitious students with world-class universities across the globe.</p>
                        </div>
                    </div>
                    <div class="footer-social">
                        <?php
                        // Try to get social media links from database if available
                        try {
                            $social_links = [];
                            if (isset($pdo) && $pdo) {
                                // Check if settings table exists
                                $checkTable = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'settings')");
                                if ($checkTable && $checkTable->fetchColumn()) {
                                    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'social_%' AND is_public = TRUE");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $social_links[$row['setting_key']] = $row['setting_value'];
                                    }
                                }
                            }

                            // Use database links if available, otherwise use placeholders
                            $facebook_link = $social_links['social_facebook'] ?? '#';
                            $twitter_link = $social_links['social_twitter'] ?? '#';
                            $instagram_link = $social_links['social_instagram'] ?? '#';
                            $linkedin_link = $social_links['social_linkedin'] ?? '#';
                        } catch (Exception $e) {
                            // Fallback to placeholder links if database fails
                            $facebook_link = '#';
                            $twitter_link = '#';
                            $instagram_link = '#';
                            $linkedin_link = '#';
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($facebook_link); ?>" class="social-link" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($twitter_link); ?>" class="social-link" aria-label="Twitter" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($instagram_link); ?>" class="social-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($linkedin_link); ?>" class="social-link" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-nav">
                        <li><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_url; ?>about.php">About Us</a></li>
                        <li><a href="<?php echo $base_url; ?>services.php">Our Services</a></li>
                        <li><a href="<?php echo $base_url; ?>universities.php">Partner Universities</a></li>
                        <li><a href="<?php echo $base_url; ?>events.php">Events</a></li>
                        <li><a href="<?php echo $base_url; ?>contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-bottom">
                    <p class="footer-text">Copyright <?php echo date('Y'); ?> — Connect MyUni. All rights reserved. | Website by: <a href="https://teejay-graphix-portfolio.vercel.app/" target="_blank" rel="noopener noreferrer" class="website-credit" style="color:var(--accent,#4C8EF7);text-decoration:none;font-weight:700;">TEEJAY GRAPHIX</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (Deferred) -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Service worker cleanup and fresh stylesheet reload -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                registrations.forEach(function(reg) {
                    reg.unregister();
                });
            });
        }

        window.addEventListener('load', function() {
            document.querySelectorAll('link[rel="stylesheet"]').forEach(function(link) {
                if (link.href && link.href.indexOf('style.css') !== -1) {
                    link.href = link.href.split('?')[0] + '?v=' + Date.now();
                }
            });
        });
    </script>

    <!-- Custom JavaScript (Deferred) -->
    <?php
        $js_version = @filemtime(__DIR__ . '/../assets/js/main.js') ?: time();
        $base_url = isset($base_url) ? $base_url : (defined('CONNECTMYUNI_BASE_URL') ? CONNECTMYUNI_BASE_URL : '/');
    ?>

    <!-- Professional Interactions (New Redesign) -->
    <script defer src="<?php echo $base_url; ?>frontend/assets/js/professional-interactions.js?v=<?php echo $js_version; ?>"></script>

    <!-- Animations: cmi-* scroll-reveal system (Stage 04A). Loaded before main.js so reveal states initialize correctly. -->
    <script defer src="<?php echo $base_url; ?>frontend/assets/js/animations.js?v=<?php echo $js_version; ?>"></script>
    <script defer src="<?php echo $base_url; ?>frontend/assets/js/main.js?v=<?php echo $js_version; ?>"></script>

        <?php if (!empty($is_homepage)): ?>
            <?php
                $three_version = @filemtime(__DIR__ . '/../assets/js/landing-three.js') ?: time();
                $matrix_3d_version = @filemtime(__DIR__ . '/../assets/js/global-3d-matrix.js') ?: time();
            ?>
            <!-- Cinematic landing layer: single Three.js scene, homepage only.
                 defer preserves order: three.min.js loads before scripts. -->
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
            <script defer src="<?php echo $base_url; ?>frontend/assets/js/landing-three.js?v=<?php echo $three_version; ?>"></script>
            <script defer src="<?php echo $base_url; ?>frontend/assets/js/global-3d-matrix.js?v=<?php echo $matrix_3d_version; ?>"></script>
        <?php endif; ?>
    </body>

    </html>