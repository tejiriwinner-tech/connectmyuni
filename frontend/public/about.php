<?php
// SEO Meta Configuration
$page_title = 'About Us | Connect MyUni - Trusted Overseas Education Advisors';
$meta_description = 'Learn about Connect MyUni, our 20+ year legacy, expert education counselors, and proven track record placing thousands of students into prestigious global universities.';
$meta_keywords = 'about connect myuni, education consultancy team, overseas education counselors, study abroad agency nigeria, global education partners';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'AboutPage',
    'name'     => 'About Connect MyUni',
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
                'name' => 'About Us',
                'item' => $origin . '/about.php'
            ]
        ]
    ]
];

include __DIR__ . '/../components/header.php'; 
?>

<!-- Modern Hero Section -->
<section class="about-hero-section">
    <div class="about-hero-background">
        <div class="hero-gradient-overlay"></div>
        <div class="hero-pattern"></div>
    </div>
    <div class="container">
        <div class="about-hero-content">
            <div class="hero-badge">
                <i class="fas fa-globe"></i>
                <span>Since 2003</span>
            </div>
            <h1 class="hero-title">About Connect MyUni</h1>
            <p class="hero-subtitle">Your Trusted Partner in Global Education Excellence</p>
            <div class="hero-breadcrumb">
                <a href="<?php echo $base_url; ?>index.php">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">About Us</span>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="about-stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">21+</div>
                    <div class="stat-label">Years of Excellence</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Students Placed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Partner Universities</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-globe-africa"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">30+</div>
                    <div class="stat-label">Study Destinations</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Content Section -->
<section class="about-content-section">
    <div class="container">
        <div class="about-split-layout">
            <div class="about-left-content">
                <div class="section-badge">
                    <i class="fas fa-star"></i>
                    <span>Our Story</span>
                </div>
                <h2 class="section-title">Leading the Way in Global Education</h2>
                <p class="section-text">
                    Connect MyUni is a leading provider of education consulting services, proudly registered in Nigeria with the Corporate Affairs Commission. With over 21 years of dedicated service, Connect MyUni has established itself as a trusted name in the international education landscape.
                </p>
                <p class="section-text">
                    Our mission is to provide high-quality, personalized education consulting to students and institutions, ensuring that both find the perfect fit for their academic and professional aspirations. We believe in the transformative power of education and are committed to making global learning opportunities accessible to all.
                </p>
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Personalized Guidance</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Global Network</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Expert Support</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Proven Results</span>
                    </div>
                </div>
            </div>
            <div class="about-right-content">
                <div class="about-image-showcase">
                    <div class="main-image">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/image1.png" alt="Connect MyUni Team">
                        <div class="image-overlay">
                            <div class="overlay-badge">
                                <i class="fas fa-award"></i>
                                <span>Excellence Award</span>
                            </div>
                        </div>
                    </div>
                    <div class="secondary-images">
                        <div class="secondary-image secondary-1">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/image2.png" alt="Student Success">
                        </div>
                        <div class="secondary-image secondary-2">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/image3.png" alt="Global Reach">
                        </div>
                        <div class="secondary-image secondary-3">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/image4.png" alt="Team Collaboration">
                        </div>
                        <div class="secondary-image secondary-4">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/image5.png" alt="Academic Excellence">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="about-services-section">
    <div class="container">
        <div class="section-header-center">
            <div class="section-badge">
                <i class="fas fa-cogs"></i>
                <span>Our Services</span>
            </div>
            <h2 class="section-title">Comprehensive Education Solutions</h2>
            <p class="section-subtitle">We offer a wide range of services tailored to meet the unique needs of each student and institution</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="service-title">Ticket Booking</h3>
                <p class="service-description">Our platform ensures secure transactions, instant ticket confirmation, and personalized recommendations, backed by a track record of excellence.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="service-title">Scholarship Assistance</h3>
                <p class="service-description">We provide assistance to students in applying for scholarships to fund their education, increasing access to financial aid and opportunities.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="service-title">Accommodation Support</h3>
                <p class="service-description">We assist students in arranging nearby accommodation and offer essential guidance before their departure for a smooth transition.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3 class="service-title">Proof of Funds</h3>
                <p class="service-description">Our service assists students in obtaining documentation demonstrating their financial ability to support their studies abroad.</p>
            </div>
        </div>
    </div>
</section>

<!-- Partner Universities Section -->
<section class="partners-section">
    <div class="container">
        <div class="section-header-center">
            <div class="section-badge">
                <i class="fas fa-handshake"></i>
                <span>Our Partners</span>
            </div>
            <h2 class="section-title">Partner Universities</h2>
            <p class="section-subtitle">Building partnerships with diverse universities worldwide to enhance global learning experiences</p>
        </div>
        <div class="partners-grid">
            <div class="partner-card">
                <div class="partner-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h3 class="partner-name">South Western University (SWU)</h3>
                <p class="partner-location">Cebu, Philippines</p>
                <p class="partner-description">SWU excels in diversity and academic excellence, with nearly 5,000 African students, fostering a global community.</p>
            </div>
            <div class="partner-card">
                <div class="partner-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h3 class="partner-name">Nottingham Trent University (NTU)</h3>
                <p class="partner-location">United Kingdom</p>
                <p class="partner-description">Our project at Nottingham Trent University focuses on African students, fostering a global community and empowering leaders.</p>
            </div>
            <div class="partner-card">
                <div class="partner-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h3 class="partner-name">Lincoln University College (LUC)</h3>
                <p class="partner-location">Malaysia</p>
                <p class="partner-description">Lincoln University is a renowned institution attracting over 8,000 students from Malaysia and Nigeria with diverse programs.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-section">
    <div class="container">
        <div class="section-header-center">
            <div class="section-badge">
                <i class="fas fa-lightbulb"></i>
                <span>Why Us</span>
            </div>
            <h2 class="section-title">Why Choose Connect MyUni?</h2>
            <p class="section-subtitle">Discover what sets us apart in the education consulting landscape</p>
        </div>
        <div class="why-features-grid">
            <div class="why-feature">
                <div class="why-number">01</div>
                <h3 class="why-title">Comprehensive Support</h3>
                <p class="why-description">We offer a comprehensive suite of services, from university applications to visa assistance and accommodation arrangements.</p>
            </div>
            <div class="why-feature">
                <div class="why-number">02</div>
                <h3 class="why-title">Proven Track Record</h3>
                <p class="why-description">With a proven track record of success and numerous satisfied students, Connect MyUni is a trusted partner in education.</p>
            </div>
            <div class="why-feature">
                <div class="why-number">03</div>
                <h3 class="why-title">Personalized Guidance</h3>
                <p class="why-description">Our seasoned professionals offer personalized guidance tailored to your academic goals with in-depth knowledge.</p>
            </div>
            <div class="why-feature">
                <div class="why-number">04</div>
                <h3 class="why-title">Seamless Transition</h3>
                <p class="why-description">Our expertise ensures a seamless transition with services from academic guidance to visa assistance.</p>
            </div>
            <div class="why-feature">
                <div class="why-number">05</div>
                <h3 class="why-title">Global Network</h3>
                <p class="why-description">We have established partnerships with diverse universities worldwide, providing access to leading institutions.</p>
            </div>
            <div class="why-feature">
                <div class="why-number">06</div>
                <h3 class="why-title">Student-Centered Approach</h3>
                <p class="why-description">We simplify studying abroad, empowering students to achieve academic goals confidently. Your success is our mission.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="cta-background">
        <div class="cta-pattern"></div>
    </div>
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Start Your Journey?</h2>
            <p class="cta-subtitle">Whether you're a student dreaming of studying abroad or an institution looking to attract international talent, Connect MyUni is here to guide you every step of the way.</p>
            <div class="cta-buttons">
                <a href="<?php echo $base_url; ?>contact.php" class="btn btn-primary">
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </a>
                <a href="<?php echo $base_url; ?>services.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    Our Services
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="contact-info-section">
    <div class="container">
        <div class="contact-info-grid">
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="contact-title">Email Us</h3>
                <p class="contact-detail">info@connectmyuni.org</p>
            </div>
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fab fa-instagram"></i>
                </div>
                <h3 class="contact-title">Follow Us</h3>
                <p class="contact-detail">@connect_myuni_edu</p>
            </div>
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3 class="contact-title">Visit Us</h3>
                <p class="contact-detail">www.connectmyuni.org</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../components/footer.php'; ?>
