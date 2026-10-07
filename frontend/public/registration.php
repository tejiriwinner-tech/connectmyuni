<?php
require_once __DIR__ . '/../../backend/bootstrap.php';
use ConnectMyUni\Services\EventService;

// Get event ID from URL
$eventId = $_GET['event_id'] ?? null;
$event = null;
$eventTitle = 'Connect MyUni Event';

// Load event details from the database if an ID is provided
if ($eventId) {
    try {
        $event = (new EventService())->getForPublic((string) $eventId);
    } catch (\Throwable $e) {
        $event = null;
    }
    if ($event) {
        $eventTitle = $event['title'];
    }
}

// SEO Meta Configuration
if ($event) {
    $page_title = 'Register for ' . htmlspecialchars($eventTitle) . ' | Connect MyUni';
    $meta_description = 'Reserve your seat for ' . htmlspecialchars($eventTitle) . ' with Connect MyUni. Free consultation, admissions advice, and direct university representative access.';
} else {
    $page_title = 'Student Registration & Consultation Booking | Connect MyUni';
    $meta_description = 'Register for free overseas education and university admission counseling with Connect MyUni. Start your university application to top institutions in the UK, USA, Canada, and Europe.';
}
$meta_keywords = 'student registration, study abroad booking, overseas counseling registration, admission appointment nigeria';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebPage',
    'name'     => $page_title,
    'description' => $meta_description,
    'breadcrumb' => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $origin . '/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Register',
                'item' => $origin . '/registration.php'
            ]
        ]
    ]
];

// Form state
$success = false;
$error = false;
$message = '';

// Handle form submission before HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || !\ConnectMyUni\Config\Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = true;
        $message = 'Security validation failed. Please refresh and try again.';
    } else {
        $fullName = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $fieldOfStudy = trim($_POST['fieldOfStudy'] ?? '');
        $eventLocation = trim($_POST['eventLocation'] ?? '');
        $eventIdFromForm = trim($_POST['eventId'] ?? $eventId);

        // Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($fieldOfStudy) || empty($eventLocation)) {
            $error = true;
            $message = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = true;
            $message = 'Please enter a valid email address.';
        } else {
            // 1. Persist to database if eventId is valid numeric
            $savedToDb = false;
            try {
                $pdo = \ConnectMyUni\Database::getConnection();
                $dbEventId = (is_numeric($eventIdFromForm) && (int)$eventIdFromForm > 0) ? (int)$eventIdFromForm : null;
                
                // If no specific event is linked, pick the first published event or fallback gracefully
                if ($dbEventId === null) {
                    $firstEv = $pdo->query("SELECT id FROM events WHERE status = 'published' ORDER BY event_date ASC LIMIT 1")->fetchColumn();
                    $dbEventId = $firstEv ? (int)$firstEv : null;
                }

                if ($dbEventId !== null) {
                    $stmt = $pdo->prepare("
                        INSERT INTO event_registrations (event_id, full_name, email, phone, field_of_study, event_location, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $dbEventId,
                        $fullName,
                        $email,
                        $phone,
                        $fieldOfStudy,
                        $eventLocation
                    ]);
                    $savedToDb = true;
                }
            } catch (\Throwable $e) {
                error_log('[Registration] Database insert failed: ' . $e->getMessage());
            }

            // 2. Also persist to local JSON file backup
            $registrationFile = __DIR__ . '/data/registrations.json';
            if (!is_dir(dirname($registrationFile))) {
                mkdir(dirname($registrationFile), 0755, true);
            }
            $registrations = [];
            if (file_exists($registrationFile)) {
                $regData = file_get_contents($registrationFile);
                $registrations = json_decode($regData, true) ?? [];
            }
            $registrations[] = [
                'id' => uniqid('reg_'),
                'fullName' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'fieldOfStudy' => $fieldOfStudy,
                'eventLocation' => $eventLocation,
                'eventId' => $eventIdFromForm,
                'eventTitle' => $eventTitle,
                'registrationDate' => date('Y-m-d H:i:s')
            ];
            file_put_contents($registrationFile, json_encode($registrations, JSON_PRETTY_PRINT));

            $success = true;
            $message = 'Thank you for registering! We look forward to seeing you at our event. A confirmation email will be sent to ' . htmlspecialchars($email) . ' shortly.';
            $_POST = [];
        }
    }
}

// Get event locations
$eventLocations = [
    'abuja' => 'Abuja - Lincoln College Campus, Kurudu Azhata, FCT',
    'lagos' => 'Lagos - De Plazaville Mall, No. 119, Obafemi Awolowo Way',
    'virtual' => 'Virtual - Online Live Stream & Zoom',
    'hybrid' => 'Hybrid - Both In-Person and Online'
];

$csrfToken = \ConnectMyUni\Config\Security::generateCsrfToken();
include __DIR__ . '/../components/header.php';
?>

<!-- Registration Page Hero Section -->
<section class="registration-page-hero cmi-section-enter">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-calendar-check"></i>
                <span>Event Registration</span>
            </div>
            <h1 class="registration-page-title cmi-fade-up"><?php echo htmlspecialchars($eventTitle); ?></h1>
            <p class="registration-page-subtitle cmi-fade-up">Join us for an enriching educational experience</p>
            <div class="hero-steps">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">Fill Form</div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">Confirm</div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-text">Attend</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Page-specific CSS -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1E1B4B 0%, #0D9488 100%);
        --accent-gradient: linear-gradient(135deg, #0F766E 0%, #F59E0B 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.07);
        --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
    }

    /* Hero Section */
    .registration-page-hero {
        position: relative;
        padding: 5rem 0 4rem;
        overflow: hidden;
        background: linear-gradient(135deg, #0F0E2E 0%, #1E1B4B 60%, #0F766E 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: #FFFFFF !important;
    }

    .hero-background, .hero-overlay {
        display: none !important;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: var(--color-accent) !important;
        padding: 0.45rem 1.1rem;
        border-radius: 50px;
        font-family: var(--font-heading);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .registration-page-title {
        font-size: clamp(2.2rem, 4vw, 3.25rem);
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        text-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        line-height: 1.15;
    }

    .registration-page-subtitle {
        font-size: 1.15rem;
        color: rgba(255, 255, 255, 0.9) !important;
        margin-bottom: 1.75rem;
        line-height: 1.6;
    }

    .hero-steps {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 2.5rem !important;
        margin-top: 0.5rem;
    }

    .step-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 0.5rem;
    }

    .step-number {
        width: 44px;
        height: 44px;
        background: var(--color-secondary) !important;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.4);
    }

    .step-text {
        font-size: 0.85rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.9) !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Registration Section */
    .registration-page-section {
        padding: 4rem 0;
        background: white;
    }

    .registration-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
    }

    /* Left Content Cards */
    .welcome-card, .event-details-card, .contact-support-card {
        background: var(--glass-bg);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .welcome-card:hover, .event-details-card:hover, .contact-support-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .welcome-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .welcome-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1e293b;
    }

    .welcome-card p {
        color: #64748b;
        line-height: 1.7;
    }

    /* Event Details Card */
    .card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(37, 99, 235, 0.1);
    }

    .card-header i {
        width: 40px;
        height: 40px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .event-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .info-icon {
        width: 45px;
        height: 45px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .info-content {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }

    .event-description {
        background: rgba(37, 99, 235, 0.05);
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid #2563eb;
    }

    .event-description strong {
        color: #2563eb;
        display: block;
        margin-bottom: 0.5rem;
    }

    .event-description p {
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* Contact Support Card */
    .contact-methods {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .contact-method {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .contact-method:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        transform: translateX(5px);
    }

    .contact-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
    }

    .contact-icon.whatsapp {
        background: #25D366;
    }

    .contact-icon.email {
        background: #2563eb;
    }

    .contact-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .contact-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .contact-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }

    .contact-arrow {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Right Form Section */
    .signup-form-box {
        background: var(--glass-bg);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: var(--border-radius);
        padding: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: sticky;
        top: 2rem;
    }

    .form-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .form-icon {
        width: 70px;
        height: 70px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
    }

    .form-title {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }

    .form-subtitle {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Alerts */
    .success-alert, .error-alert {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .success-alert {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .error-alert {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .alert-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .success-alert .alert-icon {
        background: #22c55e;
        color: white;
    }

    .error-alert .alert-icon {
        background: #ef4444;
        color: white;
    }

    .alert-content strong {
        display: block;
        margin-bottom: 0.25rem;
        color: #1e293b;
    }

    .alert-content p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Modern Form */
    .modern-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-field label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }

    .form-field label i {
        color: #2563eb;
        width: 20px;
    }

    .required {
        color: #ef4444;
    }

    .form-input {
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-input::placeholder {
        color: #94a3b8;
    }

    .btn-submit-registration {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 1.25rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        margin-top: 1rem;
    }

    .btn-submit-registration:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }

    .btn-submit-registration i {
        transition: transform 0.3s ease;
    }

    .btn-submit-registration:hover i {
        transform: translateX(4px);
    }

    .form-note {
        display: flex;
        align-items: start;
        gap: 0.75rem;
        padding: 1rem;
        background: rgba(37, 99, 235, 0.05);
        border-radius: 12px;
        margin-top: 1.5rem;
    }

    .note-icon {
        color: #2563eb;
        font-size: 1.1rem;
        margin-top: 0.1rem;
    }

    .form-note p {
        margin: 0;
        color: #64748b;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
        .registration-wrapper {
            grid-template-columns: 1fr;
        }

        .signup-form-box {
            position: static;
        }

        .event-info-grid {
            grid-template-columns: 1fr;
        }

        .hero-steps {
            flex-wrap: wrap;
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        .registration-page-hero {
            padding: 4rem 0 3rem;
        }

        .welcome-card, .event-details-card, .contact-support-card,
        .signup-form-box {
            padding: 1.5rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }
</style>

<!-- Registration Content -->
<section class="registration-page-section">
    <div class="container">
        <div class="registration-wrapper">
            <!-- Left Content -->
            <div class="registration-left">
                <div class="welcome-card">
                    <div class="welcome-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Thank you for your interest!</h2>
                    <p>
                        We're thrilled that you want to join us for <strong><?php echo htmlspecialchars($eventTitle); ?></strong>.
                        This is an excellent opportunity to explore global education opportunities, meet with university representatives,
                        and take the next step in your academic journey.
                    </p>
                </div>

                <?php if ($event): ?>
                    <div class="event-details-card">
                        <div class="card-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>EVENT DETAILS</h3>
                        </div>

                        <div class="event-info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Date</span>
                                    <span class="info-value"><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Type</span>
                                    <span class="info-value"><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($event['duration'])): ?>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Duration</span>
                                    <span class="info-value"><?php echo htmlspecialchars($event['duration']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($event['capacity'])): ?>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Capacity</span>
                                    <span class="info-value"><?php echo htmlspecialchars($event['capacity']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="event-description">
                            <strong>Description:</strong>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="event-details-card">
                        <div class="card-header">
                            <i class="fas fa-globe"></i>
                            <h3>CONNECT MYUNI EVENTS</h3>
                        </div>
                        <p>Join us for world-class education seminars, workshops, and networking events designed to help you achieve your global education goals.</p>
                    </div>
                <?php endif; ?>

                <div class="contact-support-card">
                    <div class="card-header">
                        <i class="fas fa-headset"></i>
                        <h3>NEED HELP?</h3>
                    </div>
                    <div class="contact-methods">
                        <a href="https://wa.me/639176923263" class="contact-method" target="_blank" rel="noopener">
                            <div class="contact-icon whatsapp">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">WhatsApp Desk</span>
                                <span class="contact-value">+63 917 692 3263</span>
                            </div>
                            <i class="fas fa-external-link-alt contact-arrow"></i>
                        </a>
                        <a href="tel:+2348063325541" class="contact-method">
                            <div class="contact-icon phone" style="background: var(--color-primary);">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Admissions Hotline</span>
                                <span class="contact-value">+234 806 332 5541</span>
                            </div>
                            <i class="fas fa-external-link-alt contact-arrow"></i>
                        </a>
                        <a href="mailto:info@connectmyuni.net" class="contact-method">
                            <div class="contact-icon email">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Email</span>
                                <span class="contact-value">info@connectmyuni.net</span>
                            </div>
                            <i class="fas fa-external-link-alt contact-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Form -->
            <div class="registration-right">
                <div class="signup-form-box">
                    <div class="form-header">
                        <div class="form-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="form-title">SIGNUP FORM</h2>
                        <p class="form-subtitle">Complete the form below to register</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="success-alert">
                            <div class="alert-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="alert-content">
                                <strong>Registration Successful!</strong>
                                <p><?php echo $message; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="error-alert">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="alert-content">
                                <strong>Registration Failed</strong>
                                <p><?php echo $message; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="modern-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <div class="form-field">
                            <label for="fullName">
                                <i class="fas fa-user"></i>
                                Full Name <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="fullName"
                                name="fullName"
                                class="form-input"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-field">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address <span class="required">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your email address"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-field">
                            <label for="phone">
                                <i class="fas fa-phone"></i>
                                Phone Number <span class="required">*</span>
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                class="form-input"
                                placeholder="Enter your phone number"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-field">
                            <label for="fieldOfStudy">
                                <i class="fas fa-book"></i>
                                Field of Study <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="fieldOfStudy"
                                name="fieldOfStudy"
                                class="form-input"
                                placeholder="Enter your field of study"
                                value="<?php echo htmlspecialchars($_POST['fieldOfStudy'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-field">
                            <label for="eventLocation">
                                <i class="fas fa-map-marker-alt"></i>
                                Select Event Location <span class="required">*</span>
                            </label>
                            <select
                                id="eventLocation"
                                name="eventLocation"
                                class="form-input"
                                required>
                                <option value="">-- Please choose an option --</option>
                                <?php foreach ($eventLocations as $key => $location): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"
                                        <?php echo ($_POST['eventLocation'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="eventId" value="<?php echo htmlspecialchars($eventId ?? ''); ?>">

                        <button type="submit" class="btn-submit-registration">
                            <span class="btn-text">SUBMIT REGISTRATION</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <div class="form-note">
                        <div class="note-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p><small>We respect your privacy. Your information will be kept confidential and used only for event-related communications.</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../components/footer.php'; ?>
