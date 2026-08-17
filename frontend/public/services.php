<?php include __DIR__ . '/../components/header.php'; ?>

<!-- Services Section -->
<section class="services-page-section cmi-section-enter">
    <div class="container">
        <div class="services-content-wrapper">
            <!-- Left Services List -->
            <div class="services-left">
                <h2 class="services-page-title cmi-fade-up">Our Services</h2>

                <div class="services-accordion">
                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Student Placement Programs</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We connect qualified students with universities that match their academic aspirations and career goals, ensuring a perfect fit for their educational journey. Our placement experts work closely with students to identify suitable institutions and guide them through the entire admission process.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Scholarship and Application Support</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We provide comprehensive assistance to students in applying for scholarships and financial aid. Our team helps identify suitable scholarship opportunities, guides students through the application process, and provides support in compiling necessary documents to maximize funding prospects.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Test Preparation Services</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We offer specialized coaching for standardized tests including SAT, ACT, GRE, GMAT, and other required examinations. Our experienced instructors provide comprehensive test preparation strategies, practice materials, and personalized guidance to help students achieve their target scores.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Language Training</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We provide professional language proficiency training for IELTS, TOEFL, and other language examinations. Our instructors deliver intensive courses with focus on all language skills and test-specific strategies to ensure students meet university entry requirements.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Academic Counseling Services</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>Our expert counselors provide personalized guidance on program selection, university ranking, admission requirements, and academic planning. We help students make informed decisions about their education path and ensure they choose institutions that align with their goals.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Pre-Departure Support</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We offer comprehensive pre-departure briefings covering visa requirements, cultural adaptation, academic expectations, health and safety tips, airport pick-up arrangements, and accommodation assistance, ensuring a smooth transition to studying abroad.</p>
                        </div>
                    </div>

                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>Visa Assistance and Documentation</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p>We provide complete support with visa applications and documentation requirements. Our team guides students through every step of the visa process, helps compile necessary documents, and ensures compliance with international education standards and immigration regulations.</p>
                        </div>
                    </div>
                </div>
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
