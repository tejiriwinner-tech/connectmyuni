<?php
// Bootstrap first — provides autoloading + CONNECTMYUNI_BASE_URL.
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\ContactMessageService;

$page_title = 'Contact Us';
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
                                <p>123 Education Street, Learning City, LC 12345</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h5>Phone Number</h5>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h5>Email Address</h5>
                                <p>info@connectmyuni.net</p>
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
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span style="color: var(--danger);">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

<div class="mb-3">
                            <label for="message" class="form-label">Message <span style="color: var(--danger);">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.contact-info-card {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: 100%;
}

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a56db;
    margin-bottom: 15px;
}

.section-subtitle {
    color: #6b7280;
    margin-bottom: 30px;
    line-height: 1.6;
}

.contact-details {
    margin-top: 30px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1a56db, #1340a8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.contact-text h5 {
    font-size: 16px;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 5px;
}

.contact-text p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.contact-form-card {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.form-title {
    font-size: 24px;
    font-weight: 700;
    color: #1a56db;
    margin-bottom: 25px;
}

.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
}

.form-control:focus {
    border-color: #1a56db;
    box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1);
}

.btn-submit {
    padding: 14px 30px;
    background: linear-gradient(135deg, #1a56db, #1340a8);
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    width: 100%;
    justify-content: center;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(26, 86, 219, 0.3);
}

.alert {
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .contact-section {
        padding: 50px 0;
    }
    .contact-info-card,
    .contact-form-card {
        padding: 25px;
    }
    .section-title {
        font-size: 26px;
    }
}
</style>

<?php include __DIR__ . '/../components/footer.php'; ?>

