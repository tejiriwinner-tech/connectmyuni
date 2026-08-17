<?php include __DIR__ . '/../components/header.php'; ?>

<?php
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

$success = false;
$error = false;
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Save registration to file
        $registrationFile = __DIR__ . '/data/registrations.json';

        // Create data directory if it doesn't exist
        if (!is_dir(dirname($registrationFile))) {
            mkdir(dirname($registrationFile), 0755, true);
        }

        // Load existing registrations
        $registrations = [];
        if (file_exists($registrationFile)) {
            $regData = file_get_contents($registrationFile);
            $registrations = json_decode($regData, true) ?? [];
        }

        // Create new registration
        $newRegistration = [
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

        // Add to registrations
        $registrations[] = $newRegistration;

        // Save to JSON file
        if (file_put_contents($registrationFile, json_encode($registrations, JSON_PRETTY_PRINT))) {
            $success = true;
            $message = 'Thank you for registering! We look forward to seeing you at our event. A confirmation email will be sent to ' . htmlspecialchars($email) . ' shortly.';
            $_POST = [];
        } else {
            $error = true;
            $message = 'An error occurred while processing your registration. Please try again.';
        }
    }
}

// Get event locations
$eventLocations = [
    'abuja' => 'Abuja - Suite B3, 1st Floor, Panasonic Plaza',
    'lagos' => 'Lagos - De Plazaville Mall, No. 119, Obafemi Awolowo Way',
    'virtual' => 'Virtual - Online Event',
    'hybrid' => 'Hybrid - Both In-Person and Online'
];
?>

<!-- Registration Page Hero Section -->
<section class="registration-page-hero cmi-section-enter">
    <div class="container">
        <h1 class="registration-page-title cmi-fade-up"><?php echo htmlspecialchars($eventTitle); ?></h1>
        <p class="registration-page-subtitle cmi-fade-up">Join us for an enriching educational experience</p>
    </div>
</section>

<!-- Registration Content -->
<section class="registration-page-section">
    <div class="container">
        <div class="registration-wrapper">
            <!-- Left Content -->
            <div class="registration-left">
                <h2>Thank you for your interest!</h2>
                <p>
                    We're thrilled that you want to join us for <strong><?php echo htmlspecialchars($eventTitle); ?></strong>.
                    This is an excellent opportunity to explore global education opportunities, meet with university representatives,
                    and take the next step in your academic journey.
                </p>

                <?php if ($event): ?>
                    <div class="event-info-box">
                        <h3>EVENT DETAILS</h3>

                        <div class="event-info-item">
                            <strong><i class="fas fa-calendar-alt"></i> Date</strong>
                            <span><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                        </div>

                        <div class="event-info-item">
                            <strong><i class="fas fa-map-marker-alt"></i> Type</strong>
                            <span><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                        </div>

                        <?php if (!empty($event['duration'])): ?>
                            <div class="event-info-item">
                                <strong><i class="fas fa-clock"></i> Duration</strong>
                                <span><?php echo htmlspecialchars($event['duration']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($event['capacity'])): ?>
                            <div class="event-info-item">
                                <strong><i class="fas fa-users"></i> Capacity</strong>
                                <span><?php echo htmlspecialchars($event['capacity']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p style="margin-top: 1.5rem; font-size: 0.9rem;">
                        <strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?>
                    </p>
                <?php else: ?>
                    <div class="event-info-box">
                        <h3>CONNECT MYUNI EVENTS</h3>
                        <p>Join us for world-class education seminars, workshops, and networking events designed to help you achieve your global education goals.</p>
                    </div>
                <?php endif; ?>

                <div class="contact-info-box" style="margin-top: 2rem;">
                    <h3>NEED HELP?</h3>
                    <p><strong>Call or WhatsApp us:</strong></p>
                    <p><i class="fas fa-phone"></i> +234 </p>
                    <p><i class="fas fa-envelope"></i> info@connectmyuni.org</p>
                </div>
            </div>

            <!-- Right Form -->
            <div class="registration-right">
                <div class="signup-form-box">
                    <h2 class="form-title">SIGNUP FORM</h2>

                    <?php if ($success): ?>
                        <div class="success-alert">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="error-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-field">
                            <label for="fullName">Full Name <span style="color: var(--secondary-color);">*</span></label>
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
                            <label for="email">Email Address <span style="color: var(--secondary-color);">*</span></label>
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
                            <label for="phone">Phone Number <span style="color: var(--secondary-color);">*</span></label>
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
                            <label for="fieldOfStudy">Field of Study <span style="color: var(--secondary-color);">*</span></label>
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
                            <label for="eventLocation">Select Event Location <span style="color: var(--secondary-color);">*</span></label>
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
                             SUBMIT REGISTRATION
                        </button>
                    </form>

                    <div class="form-note">
                        <p><small>We respect your privacy. Your information will be kept confidential and used only for event-related communications.</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../components/footer.php'; ?>
