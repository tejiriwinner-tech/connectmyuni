<?php include __DIR__ . '/../components/header.php'; ?>

<!-- =============================================
     HERO SECTION — full-width background image
     Cards overlap out of the bottom, like FAB Ed
     ============================================= -->
<section id="home" class="hero-section">

    <!-- Dark overlay -->
    <div class="hero-overlay"></div>

    <!-- Hero text — vertically centred in upper portion -->
    <div class="hero-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 text-center text-white position-relative">
                    <h1 class="hero-title fw-bold mb-3">
                        Unite Your Passion with Purpose at<br>
                        <span class="text-highlight">CONNECT MYUNI</span>
                    </h1>
                    <p class="hero-subtitle mb-4">
                        Explore world-class education with a team dedicated to your global success.
                    </p>
                    <a href="#contact" class="btn btn-hero-cta">GET IN TOUCH</a>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- Cards now sit inside a blue block below the hero and overlap up into the hero -->
<section class="hero-cards-section">
    <div class="hero-cards-overlap">
        <div class="container">
            <div class="row g-4">

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Student-Centered Approach</h3>
                        <p class="service-card-text">We are dedicated to understanding the unique goals and needs of each student, providing personalized guidance to help them achieve academic success and navigate the complexities of international education.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Tailored Placement Services</h3>
                        <p class="service-card-text">Our team connects students with institutions that best align with their academic and professional aspirations, ensuring they receive the best possible education opportunities around the world.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Compliance &amp; Global Standards</h3>
                        <p class="service-card-text">We adhere strictly to international education and visa regulations, ensuring our students meet all requirements while enjoying a seamless and compliant study experience in their chosen destination.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<!-- =============================================
     ABOUT SECTION
     ============================================= -->
<section id="about" class="about-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="about-title">Your Trusted Partner in<br>Global Education</h2>
                <p class="about-text">
                    Connect MyUni is a leading provider of education consulting services, proudly registered in Nigeria with the Corporate Affairs Commission. With over 21 years of dedicated service, Connect MyUni has established itself as a trusted name in the international education landscape. Our mission is to provide high-quality, personalized education consulting to students and institutions, ensuring that both find the perfect fit for their academic and professional aspirations.
                </p>
                <p class="about-text">
                    At Connect MyUni, we offer a wide range of services tailored to meet the unique needs of each student and institution. These services include language training, student placements, admission assistance, and career counseling. We also collaborate with universities and colleges worldwide to facilitate international student placements, ensuring a smooth transition for students seeking education abroad.
                </p>
                <a href="#" class="btn btn-about">Read More</a>
            </div>

            <div class="col-lg-6">
                <div class="about-image-collage">
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image1.png');"></div>
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image2.png');"></div>
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image3.png');"></div>
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image1.png');"></div>
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image2.png');"></div>
                    <div class="collage-image" style="background-image: url('<?php echo $base_url; ?>frontend/assets/images/image3.png');"></div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     SERVICES SECTION
     ============================================= -->
<section id="services" class="services-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="services-title">OUR SERVICES</h2>
                <div class="accordion accordion-services" id="servicesAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Student Placement
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                We connect qualified students with world-class universities and colleges, ensuring they find the perfect institution that matches their academic goals and aspirations.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Training Programs
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Our comprehensive training programs prepare students for international education, covering academic skills, cultural adaptation, and professional development.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Test Preparation Services
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Expert preparation for standardized tests including IELTS, TOEFL, SAT, GRE, and GMAT with proven strategies and experienced instructors.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Language Training
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Intensive English and other language programs designed to enhance communication skills and academic proficiency for international study.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Consulting Services
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Personalized consulting to guide your academic journey, from university selection to career planning and professional development.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Post-Study Support
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Continuous support after graduation including career guidance, visa assistance, and networking opportunities with alumni and industry professionals.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSeven">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                Scholarship/Financial Aid Assistance
                            </button>
                        </h2>
                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                Expert guidance in identifying and applying for scholarships, grants, and financial aid opportunities to make education affordable and accessible.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="services-image-box">
                    <img src="<?php echo $base_url; ?>frontend/assets/images/image2.png" alt="Graduates" class="services-image">
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     APPROACH SECTION WITH RED HERO BACKGROUND
     ============================================= -->
<section class="approach-hero-section">
    <div class="approach-hero-overlay"></div>
    <div class="approach-hero-content">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="approach-hero-title text-white">OUR APPROACH</h2>
                    <p class="approach-hero-text text-white">
                        At Connect MyUni, we believe in a personalized, student-centered approach to education consulting. We take the time to understand your unique goals, strengths, and challenges, tailoring our services to meet your individual needs. Whether you're navigating the complexities of studying abroad or seeking professional development, we are with you every step of the way, offering expert guidance and practical support.
                    </p>
                    <p class="approach-hero-text text-white">
                        Our holistic approach combines academic counseling, training, and consulting with a commitment to empowering students and professionals to reach their full potential. By fostering strong partnerships with universities, businesses, and organizations worldwide, we ensure that our clients have access to the best opportunities available. At Connect MyUni, your success is our mission.
                    </p>
                    <a href="#contact" class="btn btn-approach-hero">TALK TO OUR CONSULTANTS</a>
                </div>

                <div class="col-lg-6">
                    <div class="approach-image-wrapper">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/image3.png" alt="Our Consultants Team" class="approach-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     NEWS & EVENTS SECTION
     ============================================= -->
<section id="events" class="events-section">
    <div class="container">
        <h2 class="events-section-title">LATEST NEWS &amp; EVENTS</h2>

        <div class="events-carousel-wrapper">
            <div id="eventsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">

                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="carousel-cards-row">
                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image1.png" alt="Navigating Global Education">
                                        <span class="event-badge">WEBINAR</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">Navigating Global Education</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> June 15, 2024</p>
                                        <p class="event-description">Join us for an exclusive webinar on how to choose the right university abroad and prepare for your journey.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image2.png" alt="IELTS Preparation Masterclass">
                                        <span class="event-badge">WORKSHOP</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">IELTS Preparation Masterclass</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> July 10, 2024</p>
                                        <p class="event-description">Intensive workshop covering all aspects of IELTS exam preparation with expert trainers and proven strategies.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image3.png" alt="Study Abroad Success Stories">
                                        <span class="event-badge">VIDEO</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">Study Abroad Success Stories</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> July 20, 2024</p>
                                        <p class="event-description">Watch inspiring stories from our successful students studying abroad and their transformational experiences.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="carousel-cards-row">
                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image1.png" alt="Scholarship Opportunities">
                                        <span class="event-badge">WEBINAR</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">Scholarship Opportunities</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> August 5, 2024</p>
                                        <p class="event-description">Discover the best scholarship opportunities available for international students and how to apply effectively.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image2.png" alt="University Application Workshop">
                                        <span class="event-badge">WORKSHOP</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">University Application Workshop</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> August 15, 2024</p>
                                        <p class="event-description">Complete guide to university applications, essays, interviews, and success strategies from experienced mentors.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-card-wrapper">
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo $base_url; ?>frontend/assets/images/image3.png" alt="Career Development Series">
                                        <span class="event-badge">VIDEO</span>
                                    </div>
                                    <div class="event-content">
                                        <h3 class="event-title">Career Development Series</h3>
                                        <p class="event-date"><i class="fas fa-calendar"></i> August 25, 2024</p>
                                        <p class="event-description">Video series on career planning, professional development, and networking strategies for global professionals.</p>
                                        <a href="events.php" class="btn btn-event-link">View More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#eventsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

        <div class="events-footer">
            <a href="updates.php" class="btn btn-view-all">VIEW ALL EVENTS</a>
        </div>
    </div>
</section>


<!-- =============================================
     CONTACT SECTION
     ============================================= -->
<section id="contact" class="contact-section">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <div class="contact-map-wrapper">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.4531280450886!2d3.1656!3d9.0765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104ba5e78cffffff%3A0x8c8c8c8c8c8c8c8c!2s2%20Michika%20St%2C%20Garki%2C%20Abuja!5e0!3m2!1sen!2sng!4v1234567890" width="100%" height="400" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="contact-info">
                    <div class="contact-logo-wrapper">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/logo.png" alt="Connect MyUni Logo" class="contact-logo">
                    </div>
                    <h2 class="contact-title">CONTACT US</h2>

                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <div class="contact-details">
                            <h5>Address</h5>
                            <p>Plot 100, Lincoln College of Science management and Technology along Nyanya Karshi road, Kurudu Azhata, FCT Abuja </p>
                        </div>
                    </div>

                    <!--<div class="contact-item">-->
                    <!--    <i class="fas fa-phone contact-icon"></i>-->
                    <!--    <div class="contact-details">-->
                    <!--        <h5>Phone</h5>-->
                    <!--        <p><a href="tel:+2348063325541">+234 806 332 5541</a></p>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <div class="contact-details">
                            <h5>Phone</h5>
                            <p><a href="tel:+2348146734474">+2348146734474</a></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <div class="contact-details">
                            <h5>Email</h5>
                            <p><a href="mailto:info@connectmyuni.net">info@connectmyuni.net</a></p>
                        </div>
                    </div>

                    <div class="contact-cta">
                        <a href="https://wa.me/+639176923263" class="btn btn-whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/../components/footer.php'; ?>