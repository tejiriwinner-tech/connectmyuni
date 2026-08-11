-- ==========================================================
-- Connect MyUni — PostgreSQL Schema for Supabase
-- Database: connect_myuni (or postgres in Supabase)
-- Character Set: UTF8
-- ==========================================================

-- ==========================================================
-- Table: admin_users
-- ==========================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin' CHECK (role IN ('admin', 'editor', 'viewer')),
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for admin_users
CREATE INDEX IF NOT EXISTS idx_admin_users_username ON admin_users(username);
CREATE INDEX IF NOT EXISTS idx_admin_users_email ON admin_users(email);
CREATE INDEX IF NOT EXISTS idx_admin_users_is_active ON admin_users(is_active);

-- Trigger for updated_at on admin_users
CREATE OR REPLACE FUNCTION update_admin_users_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trigger_admin_users_updated_at
    BEFORE UPDATE ON admin_users
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: countries
-- ==========================================================
CREATE TABLE IF NOT EXISTS countries (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    flag_emoji VARCHAR(10) NULL,
    description TEXT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for countries
CREATE INDEX IF NOT EXISTS idx_countries_slug ON countries(slug);
CREATE INDEX IF NOT EXISTS idx_countries_is_featured ON countries(is_featured);
CREATE INDEX IF NOT EXISTS idx_countries_sort_order ON countries(sort_order);

-- Trigger for updated_at on countries
CREATE OR REPLACE TRIGGER trigger_countries_updated_at
    BEFORE UPDATE ON countries
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: universities
-- ==========================================================
CREATE TABLE IF NOT EXISTS universities (
    id SERIAL PRIMARY KEY,
    country_id INTEGER NOT NULL REFERENCES countries(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    location VARCHAR(200) NULL,
    description TEXT NULL,
    logo_path VARCHAR(255) NULL,
    website_url VARCHAR(255) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for universities
CREATE INDEX IF NOT EXISTS idx_universities_country_id ON universities(country_id);
CREATE INDEX IF NOT EXISTS idx_universities_slug ON universities(slug);
CREATE INDEX IF NOT EXISTS idx_universities_is_featured ON universities(is_featured);

-- Trigger for updated_at on universities
CREATE OR REPLACE TRIGGER trigger_universities_updated_at
    BEFORE UPDATE ON universities
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: services
-- ==========================================================
CREATE TABLE IF NOT EXISTS services (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    icon_class VARCHAR(100) NULL,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for services
CREATE INDEX IF NOT EXISTS idx_services_slug ON services(slug);
CREATE INDEX IF NOT EXISTS idx_services_is_active ON services(is_active);
CREATE INDEX IF NOT EXISTS idx_services_sort_order ON services(sort_order);

-- Trigger for updated_at on services
CREATE OR REPLACE TRIGGER trigger_services_updated_at
    BEFORE UPDATE ON services
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();


-- ==========================================================
-- Table: testimonials
-- ==========================================================
CREATE TABLE IF NOT EXISTS testimonials (
    id SERIAL PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_university VARCHAR(200) NULL,
    student_country VARCHAR(100) NULL,
    testimonial_text TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    rating SMALLINT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for testimonials
CREATE INDEX IF NOT EXISTS idx_testimonials_is_featured ON testimonials(is_featured);
CREATE INDEX IF NOT EXISTS idx_testimonials_sort_order ON testimonials(sort_order);

-- Trigger for updated_at on testimonials
CREATE OR REPLACE TRIGGER trigger_testimonials_updated_at
    BEFORE UPDATE ON testimonials
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: gallery_images
-- ==========================================================
CREATE TABLE IF NOT EXISTS gallery_images (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    category VARCHAR(50) NULL,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for gallery_images
CREATE INDEX IF NOT EXISTS idx_gallery_images_category ON gallery_images(category);
CREATE INDEX IF NOT EXISTS idx_gallery_images_is_active ON gallery_images(is_active);
CREATE INDEX IF NOT EXISTS idx_gallery_images_sort_order ON gallery_images(sort_order);

-- Trigger for updated_at on gallery_images
CREATE OR REPLACE TRIGGER trigger_gallery_images_updated_at
    BEFORE UPDATE ON gallery_images
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: hero_slides
-- ==========================================================
CREATE TABLE IF NOT EXISTS hero_slides (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NULL,
    cta_text VARCHAR(100) NULL,
    cta_url VARCHAR(255) NULL,
    image_path VARCHAR(255) NOT NULL,
    mobile_image_path VARCHAR(255) NULL,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for hero_slides
CREATE INDEX IF NOT EXISTS idx_hero_slides_is_active ON hero_slides(is_active);
CREATE INDEX IF NOT EXISTS idx_hero_slides_sort_order ON hero_slides(sort_order);

-- Trigger for updated_at on hero_slides
CREATE OR REPLACE TRIGGER trigger_hero_slides_updated_at
    BEFORE UPDATE ON hero_slides
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();


-- ==========================================================
-- Table: events
-- ==========================================================
CREATE TABLE IF NOT EXISTS events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(20) DEFAULT 'announcement' CHECK (category IN ('webinar', 'workshop', 'announcement', 'video')),
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
    status VARCHAR(20) DEFAULT 'published' CHECK (status IN ('draft', 'published', 'cancelled', 'archived')),
    views_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for events
CREATE INDEX IF NOT EXISTS idx_events_slug ON events(slug);
CREATE INDEX IF NOT EXISTS idx_events_category ON events(category);
CREATE INDEX IF NOT EXISTS idx_events_status ON events(status);
CREATE INDEX IF NOT EXISTS idx_events_event_date ON events(event_date);
CREATE INDEX IF NOT EXISTS idx_events_created_at ON events(created_at);

-- Trigger for updated_at on events
CREATE OR REPLACE TRIGGER trigger_events_updated_at
    BEFORE UPDATE ON events
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();


-- ==========================================================
-- Table: event_registrations
-- ==========================================================
CREATE TABLE IF NOT EXISTS event_registrations (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    field_of_study VARCHAR(200) NOT NULL,
    event_location VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for event_registrations
CREATE INDEX IF NOT EXISTS idx_event_registrations_event_id ON event_registrations(event_id);
CREATE INDEX IF NOT EXISTS idx_event_registrations_email ON event_registrations(email);
CREATE INDEX IF NOT EXISTS idx_event_registrations_created_at ON event_registrations(created_at);

-- ==========================================================
-- Table: contact_messages
-- ==========================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied', 'closed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for contact_messages
CREATE INDEX IF NOT EXISTS idx_contact_messages_email ON contact_messages(email);
CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status);
CREATE INDEX IF NOT EXISTS idx_contact_messages_created_at ON contact_messages(created_at);

-- Trigger for updated_at on contact_messages
CREATE OR REPLACE TRIGGER trigger_contact_messages_updated_at
    BEFORE UPDATE ON contact_messages
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();


-- ==========================================================
-- Table: blog_posts
-- ==========================================================
CREATE TABLE IF NOT EXISTS blog_posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content TEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    author_id INTEGER NULL REFERENCES admin_users(id) ON DELETE SET NULL,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for blog_posts
CREATE INDEX IF NOT EXISTS idx_blog_posts_slug ON blog_posts(slug);
CREATE INDEX IF NOT EXISTS idx_blog_posts_status ON blog_posts(status);
CREATE INDEX IF NOT EXISTS idx_blog_posts_published_at ON blog_posts(published_at);
CREATE INDEX IF NOT EXISTS idx_blog_posts_author_id ON blog_posts(author_id);

-- Trigger for updated_at on blog_posts
CREATE OR REPLACE TRIGGER trigger_blog_posts_updated_at
    BEFORE UPDATE ON blog_posts
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: scholarships
-- ==========================================================
CREATE TABLE IF NOT EXISTS scholarships (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    university_id INTEGER NULL REFERENCES universities(id) ON DELETE SET NULL,
    country_id INTEGER NULL REFERENCES countries(id) ON DELETE SET NULL,
    description TEXT NOT NULL,
    eligibility TEXT NULL,
    amount VARCHAR(100) NULL,
    deadline DATE NULL,
    application_url VARCHAR(255) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for scholarships
CREATE INDEX IF NOT EXISTS idx_scholarships_slug ON scholarships(slug);
CREATE INDEX IF NOT EXISTS idx_scholarships_is_featured ON scholarships(is_featured);
CREATE INDEX IF NOT EXISTS idx_scholarships_deadline ON scholarships(deadline);
CREATE INDEX IF NOT EXISTS idx_scholarships_university_id ON scholarships(university_id);
CREATE INDEX IF NOT EXISTS idx_scholarships_country_id ON scholarships(country_id);

-- Trigger for updated_at on scholarships
CREATE OR REPLACE TRIGGER trigger_scholarships_updated_at
    BEFORE UPDATE ON scholarships
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();

-- ==========================================================
-- Table: ai_content_requests
-- ==========================================================
CREATE TABLE IF NOT EXISTS ai_content_requests (
    id SERIAL PRIMARY KEY,
    admin_user_id INTEGER NULL REFERENCES admin_users(id) ON DELETE SET NULL,
    content_type VARCHAR(50) NOT NULL,
    prompt TEXT NOT NULL,
    generated_content TEXT NULL,
    approved_content TEXT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'generated', 'reviewed', 'approved', 'rejected')),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for ai_content_requests
CREATE INDEX IF NOT EXISTS idx_ai_content_requests_status ON ai_content_requests(status);
CREATE INDEX IF NOT EXISTS idx_ai_content_requests_content_type ON ai_content_requests(content_type);
CREATE INDEX IF NOT EXISTS idx_ai_content_requests_created_at ON ai_content_requests(created_at);
CREATE INDEX IF NOT EXISTS idx_ai_content_requests_admin_user_id ON ai_content_requests(admin_user_id);

-- Trigger for updated_at on ai_content_requests
CREATE OR REPLACE TRIGGER trigger_ai_content_requests_updated_at
    BEFORE UPDATE ON ai_content_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_admin_users_updated_at();


-- ==========================================================
-- Seed Data
-- ==========================================================

-- Seed: Default admin user (password: admin123)
-- Note: Password is already hashed with PHP password_hash()
INSERT INTO admin_users (username, email, password_hash, role, is_active)
VALUES (
    'admin',
    'admin@connectmyuni.net',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    TRUE
) ON CONFLICT (username) DO NOTHING;

-- Seed: Sample countries
INSERT INTO countries (name, slug, flag_emoji, description, is_featured, sort_order) VALUES
('United Kingdom', 'united-kingdom', '🇬🇧', 'Study in the UK with world-renowned universities.', TRUE, 1),
('United States', 'united-states', '🇺🇸', 'Study in the USA with top-ranked institutions.', TRUE, 2),
('Canada', 'canada', '🇨🇦', 'Study in Canada with welcoming communities.', TRUE, 3),
('Australia', 'australia', '🇦🇺', 'Study in Australia with excellent quality of life.', TRUE, 4),
('Malaysia', 'malaysia', '🇲🇾', 'Affordable quality education in Malaysia.', FALSE, 5),
('Philippines', 'philippines', '🇵🇭', 'Study in the Philippines with affordable tuition.', FALSE, 6)
ON CONFLICT (slug) DO NOTHING;

-- Seed: Sample services
INSERT INTO services (title, slug, description, icon_class, sort_order, is_active) VALUES
('University Placement', 'university-placement', 'We help you find and apply to the perfect university.', 'fa-university', 1, TRUE),
('Visa Assistance', 'visa-assistance', 'Complete guidance through the student visa process.', 'fa-passport', 2, TRUE),
('Test Preparation', 'test-preparation', 'IELTS, TOEFL, and other exam preparation courses.', 'fa-book-open', 3, TRUE),
('Scholarship Guidance', 'scholarship-guidance', 'Find and apply for scholarships to fund your education.', 'fa-award', 4, TRUE),
('Pre-Departure Briefing', 'pre-departure-briefing', 'Prepare for your journey with our comprehensive briefing.', 'fa-plane-departure', 5, TRUE),
('Post-Arrival Support', 'post-arrival-support', 'We assist you even after you arrive.', 'fa-hand-holding-heart', 6, TRUE),
('Document Processing', 'document-processing', 'Help with attestation, translation, and document prep.', 'fa-file-alt', 7, TRUE),
('Career Counseling', 'career-counseling', 'Plan your career path with our expert counselors.', 'fa-briefcase', 8, TRUE)
ON CONFLICT (slug) DO NOTHING;

-- ==========================================================
-- Schema Migration Complete
-- ==========================================================

