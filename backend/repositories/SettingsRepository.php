<?php

namespace ConnectMyUni\Repositories;

use PDO;
use PDOException;

class SettingsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get a setting value by key
     */
    public function getSetting(string $key, $default = null)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key AND is_public = TRUE");
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['setting_value'] : $default;
        } catch (PDOException $e) {
            error_log("Error getting setting {$key}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Get all public settings
     */
    public function getAllPublicSettings(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings WHERE is_public = TRUE");
            $settings = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            return $settings;
        } catch (PDOException $e) {
            error_log("Error getting public settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all settings (including non-public)
     */
    public function getAllSettings(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM settings ORDER BY setting_key");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update or create a setting
     */
    public function updateSetting(string $key, string $value, string $type = 'text', string $description = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description)
                VALUES (:key, :value, :type, :description)
                ON CONFLICT (setting_key)
                DO UPDATE SET
                    setting_value = :value,
                    setting_type = :type,
                    description = COALESCE(:description, settings.description),
                    updated_at = CURRENT_TIMESTAMP
            ");

            return $stmt->execute([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]);
        } catch (PDOException $e) {
            error_log("Error updating setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a setting
     */
    public function deleteSetting(string $key): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM settings WHERE setting_key = :key");
            return $stmt->execute(['key' => $key]);
        } catch (PDOException $e) {
            error_log("Error deleting setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get social media links specifically
     */
    public function getSocialMediaLinks(): array
    {
        $social_keys = ['social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin'];
        $links = [];

        foreach ($social_keys as $key) {
            $links[$key] = $this->getSetting($key, '#');
        }

        return $links;
    }
}
