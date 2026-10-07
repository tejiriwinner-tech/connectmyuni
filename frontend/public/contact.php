<?php
// Bootstrap first — provides autoloading + CONNECTMYUNI_BASE_URL.
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\ContactMessageService;

// SEO Meta Configuration
$page_title = 'Contact Us | Connect MyUni Admissions & Counseling Office';
$meta_description = 'Get in touch with Connect MyUni for free study abroad counseling. Call +234 806 332 5541 or chat on WhatsApp (+63 917 692 3263) to begin your overseas university application.';
$meta_keywords = 'contact connect myuni, study abroad counseling nigeria, education consultancy abuja, university admission contact, study in uk advisor';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'ContactPage',
    'name'     => 'Contact Connect MyUni',
    'description' => $meta_description,
    'mainEntity' => [
        '@type' => 'LocalBusiness',
        'name'  => 'Connect MyUni',
        'telephone' => '+2348063325541',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Plot 100, Lincoln College of Science Management & Technology, Nyanya-Karshi Rd, Kurudu Azhata',
            'addressLocality' => 'Abuja',
            'addressRegion' => 'FCT',
            'addressCountry' => 'NG'
        ]
    ]
];

$success = '';
$error = '';

// Handle form submission (processed BEFORE any HTML output so the session
// and CSRF token can be initialised cleanly under the session-save path).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $service = new ContactMessageService();
                $service->create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'subject' => $subject,
                    'message' => $message,
                ]);

                $success = 'Thank you for contacting us! We will get back to you soon.';
            } catch (\Throwable $e) {
                error_log('Contact form save failed: ' . $e->getMessage());
                $error = 'Sorry, we could not process your message right now. Please try again later.';
            }
        }
    }
}

// Prime the CSRF session before rendering the header so session_start()
// never runs after headers have been sent.
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/../components/header.php';
?>
<!-- Page Hero -->
<section class="page-hero cmi-section-enter">
    <div class="container">
        <div class="hero-content text-center">
            <div class="hero-badge">
                <i class="fas fa-headset"></i>
                <span>Direct Counseling Support</span>
            </div>
            <h1 class="page-hero__title cmi-fade-up">Contact Our Admissions Team</h1>
            <p class="hero-subtitle cmi-fade-up">
                Speak directly with our senior educational advisors in Abuja or connect with our international desks via WhatsApp and phone.
            </p>
            <div class="hero-breadcrumb">
                <a href="<?php echo $base_url; ?>index.php">Home</a>
                <span class="mx-2">/</span>
                <span>Contact</span>
            </div>
        </div>
    </div>
</section>

<!-- =============================================
     CONTACT PAGE
     ============================================= -->
<section id="contact" class="contact-section cmi-section-enter">
    <div class="container">
        <div class="row">
            <!-- Contact Info -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="contact-info-card cmi-slide-in-left">
                    <h2 class="section-title cmi-fade-up">Get In Touch</h2>
                    <p class="section-subtitle">Have questions? We would love to hear from you. Send us a message and we will respond as soon as possible.</p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h5>Our Location</h5>
                                <p>Plot 100, Lincoln College of Science Management &amp; Technology, Nyanya-Karshi Rd, Kurudu Azhata, FCT Abuja</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h5>Phone Number</h5>
                                <p><a href="tel:+2348063325541" style="color:inherit;text-decoration:none;font-weight:600;">+234 806 332 5541</a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h5>Email Address</h5>
                                <p><a href="mailto:info@connectmyuni.net" style="color:inherit;text-decoration:none;font-weight:600;">info@connectmyuni.net</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form-card cmi-slide-in-right">
                    <h3 class="form-title">Send Us a Message</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span style="color: var(--danger);">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       placeholder="e.g. Adebayo Johnson"
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span style="color: var(--danger);">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="name@example.com"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone / WhatsApp Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       placeholder="+234 800 000 0000"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject / Interest <span style="color: var(--danger);">*</span></label>
                                <select class="form-select form-control" id="subject" name="subject" required>
                                    <option value="" disabled <?php echo empty($_POST['subject']) ? 'selected' : ''; ?>>Select a topic...</option>
                                    <option value="Undergraduate Admission" <?php echo (($_POST['subject'] ?? '') === 'Undergraduate Admission') ? 'selected' : ''; ?>>Undergraduate Admission</option>
                                    <option value="Postgraduate / Masters" <?php echo (($_POST['subject'] ?? '') === 'Postgraduate / Masters') ? 'selected' : ''; ?>>Postgraduate / Masters</option>
                                    <option value="Visa & Immigration Counseling" <?php echo (($_POST['subject'] ?? '') === 'Visa & Immigration Counseling') ? 'selected' : ''; ?>>Visa &amp; Immigration Counseling</option>
                                    <option value="Scholarship Inquiries" <?php echo (($_POST['subject'] ?? '') === 'Scholarship Inquiries') ? 'selected' : ''; ?>>Scholarship Inquiries</option>
                                    <option value="General Inquiry" <?php echo (($_POST['subject'] ?? '') === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message <span style="color: var(--danger);">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required
                                      placeholder="Tell us about your desired country, course of study, or questions..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-paper-plane"></i>
                            <span>Send Message</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-section {
    padding: 5rem 0;
    background: var(--bg-main);
}

.contact-info-card {
    background: #FFFFFF !important;
    padding: 3rem 2.5rem !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-card) !important;
    border: 1px solid rgba(226, 232, 240, 0.85) !important;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.contact-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-brand);
}

.section-title {
    font-family: var(--font-heading) !important;
    font-size: clamp(1.85rem, 3vw, 2.35rem) !important;
    font-weight: 800 !important;
    color: var(--color-primary) !important;
    margin-bottom: 0.75rem !important;
    letter-spacing: -0.02em;
}

.section-subtitle {
    color: var(--text-secondary);
    margin-bottom: 2rem;
    line-height: 1.65;
    font-size: 0.98rem;
}

.contact-details {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    padding: 1rem;
    background: var(--bg-surface-alt);
    border-radius: var(--radius-md);
    border: 1px solid rgba(226, 232, 240, 0.6);
    transition: transform var(--transition-fast), border-color var(--transition-fast);
}

.contact-item:hover {
    transform: translateX(4px);
    border-color: rgba(13, 148, 136, 0.35);
}

.contact-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
    color: #FFFFFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
}

.contact-text h5 {
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 0.25rem;
}

.contact-text p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
}

.contact-form-card {
    background: #FFFFFF !important;
    padding: 3rem 2.5rem !important;
    border-radius: var(--radius-xl) !important;
    box-shadow: var(--shadow-card) !important;
    border: 1px solid rgba(226, 232, 240, 0.85) !important;
    position: relative;
    overflow: hidden;
}

.contact-form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-secondary);
}

.form-title {
    font-family: var(--font-heading) !important;
    font-size: 1.6rem !important;
    font-weight: 800 !important;
    color: var(--color-primary) !important;
    margin-bottom: 1.75rem !important;
    letter-spacing: -0.015em;
}

.form-label {
    font-family: var(--font-heading);
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.form-control, .form-select {
    border: 1.5px solid #E2E8F0 !important;
    border-radius: var(--radius-md) !important;
    padding: 0.75rem 1rem !important;
    font-size: 0.92rem !important;
    color: var(--text-primary) !important;
    background: #FFFFFF !important;
    transition: all var(--transition-fast) !important;
}

.form-control:focus, .form-select:focus {
    border-color: var(--color-secondary) !important;
    box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15) !important;
    outline: none !important;
}

.btn-submit {
    padding: 0.95rem 2rem !important;
    background: var(--gradient-secondary) !important;
    color: #FFFFFF !important;
    border: none !important;
    border-radius: var(--radius-pill) !important;
    font-family: var(--font-heading) !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    width: 100% !important;
    box-shadow: 0 4px 16px rgba(13, 148, 136, 0.35) !important;
    transition: transform var(--transition-normal), box-shadow var(--transition-normal) !important;
}

.btn-submit:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(13, 148, 136, 0.45) !important;
}

.alert {
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    font-size: 0.95rem;
}

@media (max-width: 768px) {
    .contact-section {
        padding: 3.5rem 0;
    }
    .contact-info-card,
    .contact-form-card {
        padding: 2rem 1.5rem !important;
    }
}
</style>

<?php include __DIR__ . '/../components/footer.php'; ?>

