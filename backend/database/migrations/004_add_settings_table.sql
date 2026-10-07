-- ==========================================================
-- Migration: Add Settings Table for Social Media Links
-- Purpose: Store website settings including social media links
-- SECURITY: Public - no sensitive data
-- ==========================================================

-- ==========================================================
-- Table: settings
-- Purpose: Store website configuration and social media links
-- SECURITY: Public - contains non-sensitive configuration data
-- ==========================================================
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type VARCHAR(20) DEFAULT 'text' CHECK (setting_type IN ('text', 'url', 'boolean', 'number')),
    description TEXT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_settings_setting_key ON settings(setting_key);
CREATE INDEX IF NOT EXISTS idx_settings_is_public ON settings(is_public);

-- Trigger for updated_at
CREATE TRIGGER trigger_settings_updated_at
    BEFORE UPDATE ON settings
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ==========================================================
-- Seed Data: Social Media Links
-- ==========================================================
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('social_facebook', '#', 'url', 'Facebook social media link', TRUE),
('social_twitter', '#', 'url', 'Twitter social media link', TRUE),
('social_instagram', '#', 'url', 'Instagram social media link', TRUE),
('social_linkedin', '#', 'url', 'LinkedIn social media link', TRUE),
('site_title', 'Connect MyUni', 'text', 'Website title', TRUE),
('site_description', 'Your Gateway to Global Education', 'text', 'Website description', TRUE)
ON CONFLICT (setting_key) DO NOTHING;

-- ==========================================================
-- Migration Complete
-- ==========================================================
