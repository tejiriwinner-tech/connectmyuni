-- ==========================================================
-- Connect MyUni — Database Schema Migration
-- Database: connect_myuni
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- ==========================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS connect_myuni
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE connect_myuni;

-- ==========================================================
-- Table: countries
-- ==========================================================
CREATE TABLE IF NOT EXISTS countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    flag_emoji VARCHAR(10) NULL,
    description TEXT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_is_featured (is_featured),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: universities
-- ==========================================================
CREATE TABLE IF NOT EXISTS universities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    location VARCHAR(200) NULL,
    description TEXT NULL,
    logo_path VARCHAR(255) NULL,
    website_url VARCHAR(255) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_country_id (country_id),
    INDEX idx_slug (slug),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: testimonials
-- ==========================================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_university VARCHAR(200) NULL,
    student_country VARCHAR(100) NULL,
    testimonial_text TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    rating TINYINT UNSIGNED DEFAULT 5,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_is_featured (is_featured),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: events
-- ==========================================================
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category ENUM('webinar', 'workshop', 'announcement', 'video') NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NULL,
    description TEXT NOT NULL,
    details TEXT NULL,
    image_path VARCHAR(255) DEFAULT 'asset/image1.png',
    registration_link VARCHAR(255) NULL,
    location VARCHAR(200) NULL,
    duration VARCHAR(100) NULL,
    capacity VARCHAR(100) NULL,
    requirements TEXT NULL,
    contact_info TEXT NULL,
    status ENUM('draft', 'published', 'cancelled', 'archived') DEFAULT 'published',
    views_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: event_registrations
-- ==========================================================
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    field_of_study VARCHAR(200) NOT NULL,
    event_location VARCHAR(200) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: blog_posts
-- ==========================================================
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    author_id INT UNSIGNED NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: scholarships
-- ==========================================================
CREATE TABLE IF NOT EXISTS scholarships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    university_id INT UNSIGNED NULL,
    country_id INT UNSIGNED NULL,
    description TEXT NOT NULL,
    eligibility TEXT NULL,
    amount VARCHAR(100) NULL,
    deadline DATE NULL,
    application_url VARCHAR(255) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE SET NULL,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_is_featured (is_featured),
    INDEX idx_deadline (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Table: ai_content_requests
-- ==========================================================
CREATE TABLE IF NOT EXISTS ai_content_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT UNSIGNED NULL,
    content_type VARCHAR(50) NOT NULL,
    prompt TEXT NOT NULL,
    generated_content LONGTEXT NULL,
    approved_content LONGTEXT NULL,
    status ENUM('pending', 'generated', 'reviewed', 'approved', 'rejected') DEFAULT 'pending',
    approved_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_content_type (content_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Seed: Default admin user (password: admin123)
-- ==========================================================
INSERT INTO admin_users (username, email, password_hash, role, is_active)
VALUES (
    'admin',
    'admin@connectmyuni.net',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1
) ON DUPLICATE KEY UPDATE id=id;

-- ==========================================================
-- Seed: Sample countries
-- ==========================================================
INSERT INTO countries (name, slug, flag_emoji, description, is_featured, sort_order) VALUES
('United Kingdom', 'united-kingdom', '🇬🇧', 'Study in the UK with world-renowned universities.', 1, 1),
('United States', 'united-states', '🇺🇸', 'Study in the USA with top-ranked institutions.', 1, 2),
('Canada', 'canada', '🇨🇦', 'Study in Canada with welcoming communities.', 1, 3),
('Australia', 'australia', '🇦🇺', 'Study in Australia with excellent quality of life.', 1, 4),
('Malaysia', 'malaysia', '🇲🇾', 'Affordable quality education in Malaysia.', 0, 5),
('Philippines', 'philippines', '🇵🇭', 'Study in the Philippines with affordable tuition.', 0, 6)
ON DUPLICATE KEY UPDATE id=id;

-- ==========================================================
-- Seed: Sample services
-- ==========================================================
INSERT INTO services (title, slug, description, icon_class, sort_order, is_active) VALUES
('University Placement', 'university-placement', 'We help you find and apply to the perfect university.', 'fa-university', 1, 1),
('Visa Assistance', 'visa-assistance', 'Complete guidance through the student visa process.', 'fa-passport', 2, 1),
('Test Preparation', 'test-preparation', 'IELTS, TOEFL, and other exam preparation courses.', 'fa-book-open', 3, 1),
('Scholarship Guidance', 'scholarship-guidance', 'Find and apply for scholarships to fund your education.', 'fa-award', 4, 1),
('Pre-Departure Briefing', 'pre-departure-briefing', 'Prepare for your journey with our comprehensive briefing.', 'fa-plane-departure', 5, 1),
('Post-Arrival Support', 'post-arrival-support', 'We assist you even after you arrive.', 'fa-hand-holding-heart', 6, 1),
('Document Processing', 'document-processing', 'Help with attestation, translation, and document prep.', 'fa-file-alt', 7, 1),
('Career Counseling', 'career-counseling', 'Plan your career path with our expert counselors.', 'fa-briefcase', 8, 1)
ON DUPLICATE KEY UPDATE id=id;

-- ==========================================================
-- Migration complete
-- ==========================================================
