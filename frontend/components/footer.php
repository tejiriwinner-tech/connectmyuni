    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="footer-content">
                <p class="footer-text">Copyright <?php echo date('Y'); ?> — Connect MyUni. All rights reserved. | Website by: <span class="website-credit">Hexz-Hub</span></p>
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
                <!-- Animations: cmi-* scroll-reveal system (Stage 04A). Loaded before main.js so reveal states initialize correctly. -->
        <script defer src="<?php echo $base_url; ?>frontend/assets/js/animations.js?v=<?php echo $js_version; ?>"></script>
        <script defer src="<?php echo $base_url; ?>frontend/assets/js/main.js?v=<?php echo $js_version; ?>"></script>

        <?php if (!empty($is_homepage)): ?>
            <?php $three_version = @filemtime(__DIR__ . '/../assets/js/landing-three.js') ?: time(); ?>
            <!-- Cinematic landing layer: single Three.js scene, homepage only.
                 defer preserves order: three.min.js loads before landing-three.js. -->
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
            <script defer src="<?php echo $base_url; ?>frontend/assets/js/landing-three.js?v=<?php echo $three_version; ?>"></script>
        <?php endif; ?>
    </body>

    </html>