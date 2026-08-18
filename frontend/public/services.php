<?php
include __DIR__ . '/../components/header.php';

// ─────────────────────────────────────────────
// Services — from the CMS database
// ─────────────────────────────────────────────
use ConnectMyUni\Services\ServiceService;

$services = [];
try {
    $services = (new ServiceService())->getAllActive();
} catch (\Throwable $e) {
    error_log('Services load failed: ' . $e->getMessage());
    $services = [];
}
?>

<!-- Services Section -->
<section class="services-page-section cmi-section-enter">
    <div class="container">
        <div class="services-content-wrapper">
            <!-- Left Services List -->
            <div class="services-left">
                <h2 class="services-page-title cmi-fade-up">Our Services</h2>

                <?php if (empty($services)): ?>
                    <p class="text-muted">Services are currently being updated. Please contact us for assistance.</p>
                <?php else: ?>
                <div class="services-accordion">
                    <?php foreach ($services as $svc): ?>
                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>
                                <?php if (!empty($svc['icon_class'])): ?>
                                    <i class="fas <?php echo htmlspecialchars($svc['icon_class']); ?> me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($svc['title'] ?? ''); ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p><?php echo htmlspecialchars($svc['description'] ?? ''); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Services Image -->
            <div class="services-right cmi-slide-in-right">
                <div class="services-image-container">
                    <img src="<?php echo $base_url; ?>frontend/assets/images/background1.png" alt="Our Services">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Get In Touch Section -->
<section class="get-in-touch-section cmi-section-enter">
    <div class="container">
        <h2 class="get-in-touch-title cmi-fade-up">Get In Touch With Us</h2>
        <div class="contact-details">
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-phone"></i>
                <span>Phone: +234 806 332 5541</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-envelope"></i>
                <span>Email: info@connectmyuni.org</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-globe"></i>
                <span>Visit: www.connectmyuni.org</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-map-marker-alt"></i>
                <span>Office: Suite B3, 1st Floor, 2 Michika St, Abuja, Nigeria</span>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleAccordion(button) {
        const item = button.parentElement;
        const body = item.querySelector('.service-accordion-body');
        const isActive = item.classList.contains('active');

        // Close all accordion items
        document.querySelectorAll('.service-accordion-item').forEach(el => {
            el.classList.remove('active');
        });

        // Open clicked item if it wasn't active
        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
