<?php
// Bootstrap the backend layer (autoloading + configuration) first.
require_once __DIR__ . '/../../backend/bootstrap.php';

// Define base URL for consistent navigation across all pages
$base_url = CONNECTMYUNI_BASE_URL;

// Landing-page detection: only the homepage loads the cinematic Three.js layer.
$is_homepage = (basename($_SERVER['PHP_SELF'] ?? '') === 'index.php');

// Get cache buster version based on file modification time
$style_version = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time();
$modern_ui_version = time() + 23; // Force refresh for larger secondary images
// SEO Defaults & Dynamic Configuration
$site_name = 'Connect MyUni';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$http_host = $_SERVER['HTTP_HOST'] ?? 'connectmyuni.com';
$full_origin = "{$scheme}://{$http_host}";

// Page Title
if (!empty($page_title)) {
    $final_title = (stripos($page_title, 'Connect MyUni') !== false) 
        ? $page_title 
        : "{$page_title} | {$site_name} - Overseas Education Consultancy";
} else {
    $final_title = "{$site_name} | Premier Overseas Education & University Admission Consultancy";
}

// Meta Description (Target: 150-160 characters, keyword-rich)
$meta_desc = $meta_description ?? 'Connect MyUni connects ambitious students with world-class universities in the UK, USA, Canada, Australia, and Europe. Expert admission, visa counseling, and scholarship guidance.';

// Meta Keywords
$meta_keys = $meta_keywords ?? 'study abroad nigeria, education consultancy lagos, study in uk, study in canada, study in usa, overseas university admission, student visa counseling, scholarship assistance, connect myuni';

// Canonical URL
$req_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonical = $canonical_url ?? ($full_origin . $req_path);

// Open Graph & Social Sharing Image
if (!empty($og_image)) {
    $social_image = (str_starts_with($og_image, 'http://') || str_starts_with($og_image, 'https://'))
        ? $og_image
        : ($full_origin . '/' . ltrim($og_image, '/'));
} else {
    $social_image = $full_origin . $base_url . 'frontend/assets/images/logo.png';
}

$og_type_val = $og_type ?? 'website';
$og_title_val = $og_title ?? $final_title;
$og_desc_val = $og_description ?? $meta_desc;
$twitter_card_val = $twitter_card ?? 'summary_large_image';
$robots_val = $meta_robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
?>
<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Primary SEO Meta Tags -->
    <title><?php echo htmlspecialchars($final_title); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($final_title); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keys); ?>">
    <meta name="author" content="Connect MyUni">
    <meta name="robots" content="<?php echo htmlspecialchars($robots_val); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">

    <!-- Open Graph / Facebook / WhatsApp Meta Tags -->
    <meta property="og:type" content="<?php echo htmlspecialchars($og_type_val); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title_val); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_desc_val); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($social_image); ?>">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="<?php echo htmlspecialchars($twitter_card_val); ?>">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($canonical); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($og_title_val); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($og_desc_val); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($social_image); ?>">

    <!-- Favicon & Apple Touch Icons -->
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">

    <!-- Global EducationalOrganization Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "EducationalOrganization",
      "name": "Connect MyUni",
      "alternateName": "ConnectMyUni",
      "url": "<?php echo htmlspecialchars($full_origin . $base_url); ?>",
      "logo": "<?php echo htmlspecialchars($social_image); ?>",
      "description": "Premier overseas education and university admission consultancy guiding students to top institutions in the UK, USA, Canada, Australia, and Europe.",
      "telephone": "+2348063325541",
      "contactPoint": [
        {
          "@type": "ContactPoint",
          "telephone": "+234 806 332 5541",
          "contactType": "Admissions Hotline",
          "areaServed": ["NG", "GH", "KE", "Global"],
          "availableLanguage": ["English"]
        },
        {
          "@type": "ContactPoint",
          "telephone": "+63 917 692 3263",
          "contactType": "WhatsApp Counseling Desk",
          "availableLanguage": ["English"]
        }
      ],
      "sameAs": [
        "https://facebook.com/connectmyuni",
        "https://instagram.com/connectmyuni",
        "https://linkedin.com/company/connectmyuni",
        "https://twitter.com/connectmyuni"
      ]
    }
    </script>
<?php if (!empty($schema_json_ld)): ?>
    <!-- Page Specific Schema.org JSON-LD -->
    <script type="application/ld+json">
    <?php echo json_encode($schema_json_ld, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
    </script>
<?php endif; ?>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Unique UI/UX Theme -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-core.css?v=<?php echo $modern_ui_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-components.css?v=<?php echo $modern_ui_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-pages.css?v=<?php echo $modern_ui_version; ?>">
    
    <!-- Legacy CSS (Backup Compatibility) -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/style.css?v=<?php echo $style_version; ?>">

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
    <?php if (!empty($is_homepage)): ?>
    <!-- Cinematic Three.js background layer (homepage only).
         Fixed, behind content, pointer-events: none, aria-hidden.
         Rendered by frontend/assets/js/landing-three.js -->
    <canvas id="cmi-three-canvas" aria-hidden="true" tabindex="-1"></canvas>
    <?php endif; ?>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/+639176923263" class="whatsapp-btn" target="_blank" aria-label="Contact us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Floating Enquire Now Button -->
    <a href="<?php echo $base_url; ?>contact.php" class="enquire-btn" aria-label="Enquire Now">
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
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php
                    $current = basename($_SERVER['PHP_SELF']);
                    $pages = [
                        'index.php'        => 'Home',
                        'about.php'        => 'About',
                        'services.php'     => 'Services',
                        'events.php'       => 'Events',
                        'universities.php' => 'Universities',
                        'gallery.php'      => 'Gallery',
                        'contact.php'      => 'Contact',
                    ];
                    foreach ($pages as $file => $label) {
                        $active = ($current === $file) ? 'active' : '';
                        echo "<li class='nav-item'><a class='nav-link cmi-nav-link {$active}' href='{$base_url}{$file}'>{$label}</a></li>";
                    }
                    ?>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a href="<?php echo $base_url; ?>registration.php" class="btn btn-sm cmi-btn-primary px-3 py-2 fw-semibold rounded-pill d-inline-flex align-items-center gap-2 shadow-sm">
                            <span>Apply Now</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>