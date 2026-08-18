<?php
// Bootstrap the backend layer (autoloading + configuration) first.
require_once __DIR__ . '/../../backend/bootstrap.php';

// Define base URL for consistent navigation across all pages
$base_url = CONNECTMYUNI_BASE_URL;

// Get cache buster version based on file modification time
$style_version = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="description" content="Connect MyUni - Leading Education Consultancy Services in Nigeria.">
    <meta name="author" content="Connect MyUni">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Connect MyUni' : 'Connect MyUni'; ?></title>
    <!-- Favicon & Logo -->
<link rel="icon" type="image/png" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">
<link rel="apple-touch-icon" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">
<meta property="og:image" content="<?php echo $base_url; ?>frontend/assets/images/logo.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
        <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/style.css?v=<?php echo $style_version; ?>">

    <!-- Animations & Premium UI Layer (Stage 04A / 04B / 04C) — must load AFTER style.css -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/animations.css?v=<?php echo $style_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/ui-upgrade.css?v=<?php echo $style_version; ?>">

    <!-- No-JS fallback: keep cmi-* reveal content visible if JavaScript is unavailable -->
    <noscript>
        <style>
            .cmi-fade-up, .cmi-fade-in, .cmi-slide-in-left, .cmi-slide-in-right,
            .cmi-scale, .cmi-image, .cmi-section-enter,
            .cmi-stagger-children > *, .cmi-stagger-row > *,
            .cmi-stagger-group > [class*="col"] {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
            }
        </style>
    </noscript>
</head>

<body>
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/+639176923263" class="whatsapp-btn" target="_blank" aria-label="Contact us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Floating Enquire Now Button -->
    <a href="<?php echo $base_url; ?>frontend/public/contact.php" class="enquire-btn" aria-label="Enquire Now">
        <i class="fas fa-envelope"></i>
        <span>Enquire</span>
    </a>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
                <img src="<?php echo $base_url; ?>frontend/assets/images/logo.png" alt="Connect MyUni Logo" class="logo-img">
                <span class="logo-text">Connect MyUni</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php
                    $current = basename($_SERVER['PHP_SELF']);
                    $pages = [
                        'index.php'            => 'Home',
                        'about.php'            => 'About',
                        'services.php'         => 'Services',
                        'updates.php'          => 'Updates',
                        'events.php'           => 'Events',
                        'gallery.php'          => 'Gallery',
                        'registration.php'     => 'Event Registration',
                        'universities.php' => 'Partner Universities',
                    ];
                    foreach ($pages as $file => $label) {
                        $active = ($current === $file) ? 'active' : '';
                        echo "<li class='nav-item'><a class='nav-link cmi-nav-link {$active}' href='{$base_url}{$file}'>{$label}</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>