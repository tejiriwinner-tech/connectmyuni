<?php

declare(strict_types=1);

namespace ConnectMyUni\Helpers {

    use ConnectMyUni\Database;
    use ConnectMyUni\Repositories\SettingsRepository;
    use PDO;

    class SettingsHelper
    {
        private static ?array $cachedSettings = null;

        public const DEFAULTS = [
            'site_title'        => 'Connect MyUni',
            'site_description'  => 'Your Gateway to Global Education. Premier overseas education & university admission consultancy.',
            'contact_email'     => 'info@connectmyuni.net',
            'contact_phone'     => '+234 806 332 5541',
            'contact_address'   => 'Plot 100, Lincoln College of Science Management & Technology, Nyanya-Karshi Rd, Kurudu Azhata, FCT Abuja',
            'social_facebook'   => 'https://facebook.com/connectmyuni',
            'social_twitter'    => 'https://twitter.com/connectmyuni',
            'social_instagram'  => 'https://instagram.com/connectmyuni',
            'social_linkedin'   => 'https://linkedin.com/company/connectmyuni',
            'social_youtube'    => '#',
            'seo_meta_keywords' => 'study abroad nigeria, education consultancy lagos, study in uk, study in canada, study in usa, overseas university admission, student visa counseling, scholarship assistance, connect myuni',
        ];

        /**
         * Get all public settings merged with defaults
         */
        public static function all(): array
        {
            if (self::$cachedSettings !== null) {
                return self::$cachedSettings;
            }

            $settings = self::DEFAULTS;

            try {
                $pdo = Database::getConnection();
                $repo = new SettingsRepository($pdo);
                $dbSettings = $repo->getAllPublicSettings();

                if (!empty($dbSettings) && is_array($dbSettings)) {
                    foreach ($dbSettings as $key => $val) {
                        if ($val !== null && trim((string)$val) !== '') {
                            $settings[$key] = trim((string)$val);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently fall back to defaults
            }

            self::$cachedSettings = $settings;
            return $settings;
        }

        /**
         * Get a single setting by key
         */
        public static function get(string $key, $default = null)
        {
            $all = self::all();
            if (array_key_exists($key, $all)) {
                return $all[$key];
            }
            return $default ?? (self::DEFAULTS[$key] ?? null);
        }

        /**
         * Helper to get a phone number with digits only (for wa.me and tel links)
         */
        public static function cleanPhone(?string $phone = null): string
        {
            $p = $phone ?? (string)self::get('contact_phone', '+234 806 332 5541');
            $clean = preg_replace('/[^0-9]/', '', (string)$p);
            return $clean ?: '2348063325541';
        }

        /**
         * Reset request cache
         */
        public static function clearCache(): void
        {
            self::$cachedSettings = null;
        }
    }
}

namespace {
    // Global root namespace helper functions
    if (!function_exists('get_site_setting')) {
        function get_site_setting(string $key, $default = null)
        {
            return \ConnectMyUni\Helpers\SettingsHelper::get($key, $default);
        }
    }

    if (!function_exists('get_site_settings')) {
        function get_site_settings(): array
        {
            return \ConnectMyUni\Helpers\SettingsHelper::all();
        }
    }

    if (!function_exists('get_clean_phone')) {
        function get_clean_phone(?string $phone = null): string
        {
            return \ConnectMyUni\Helpers\SettingsHelper::cleanPhone($phone);
        }
    }
}
