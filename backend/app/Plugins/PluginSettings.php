<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Plugins;

use MythicalDash\Chat\Database;

/**
 * PluginSettings class for managing plugin settings in the database.
 *
 * This class provides secure and advanced methods for managing plugin settings
 * with proper error handling, input validation, and transaction support.
 */
class PluginSettings extends PluginDB
{
    /**
     * Set a setting in the database with validation and error handling.
     *
     * @param string $identifier The identifier of the plugin
     * @param string $key The key of the setting
     * @param array $settings The settings to set
     *
     * @throws \PDOException If database operation fails
     * @throws InvalidArgumentException If input validation fails
     */
    public static function setSettings(string $identifier, string $key, array $settings): void
    {
        self::validateInput($identifier, $key);

        $conn = Database::getPdoConnection();
        $conn->beginTransaction();

        try {
            // Check if setting already exists
            $stmt = $conn->prepare('
					SELECT id 
					FROM mythicaldash_addons_settings 
					WHERE identifier = :identifier 
					AND `key` = :key
					LIMIT 1
				');

            $stmt->execute([
                ':identifier' => self::sanitizeInput($identifier),
                ':key' => self::sanitizeInput($key),
            ]);

            $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
            $value = self::sanitizeInput($settings['value'] ?? '');

            if ($exists) {
                $stmt = $conn->prepare("
						UPDATE mythicaldash_addons_settings 
						SET value = :value, 
							date = CURRENT_TIMESTAMP,
							deleted = 'false'
						WHERE identifier = :identifier 
						AND `key` = :key
					");
            } else {
                $stmt = $conn->prepare("
						INSERT INTO mythicaldash_addons_settings 
						(identifier, `key`, value, locked, deleted, date) 
						VALUES (:identifier, :key, :value, 'false', 'false', CURRENT_TIMESTAMP)
					");
            }

            $stmt->execute([
                ':identifier' => $identifier,
                ':key' => $key,
                ':value' => $value,
            ]);

            $conn->commit();
        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \PDOException('Failed to set setting: ' . $e->getMessage());
        }
    }

    /**
     * Delete a setting from the database with transaction support.
     *
     * @param string $identifier The identifier of the plugin
     * @param string $key The key of the setting
     *
     * @throws \PDOException If database operation fails
     * @throws InvalidArgumentException If input validation fails
     */
    public static function deleteSettings(string $identifier, string $key): void
    {
        self::validateInput($identifier, $key);

        $conn = Database::getPdoConnection();
        $conn->beginTransaction();

        try {
            $stmt = $conn->prepare("
					UPDATE mythicaldash_addons_settings 
					SET deleted = 'true',
						date = CURRENT_TIMESTAMP
					WHERE identifier = :identifier 
					AND `key` = :key
				");

            $stmt->execute([
                ':identifier' => self::sanitizeInput($identifier),
                ':key' => self::sanitizeInput($key),
            ]);

            $conn->commit();
        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \PDOException('Failed to delete setting: ' . $e->getMessage());
        }
    }

    /**
     * Get a setting from the database with proper error handling.
     *
     * @param string $identifier The identifier of the plugin
     * @param string $key The key of the setting
     *
     * @throws \PDOException If database operation fails
     * @throws InvalidArgumentException If input validation fails
     *
     * @return string The value of the setting
     */
    public static function getSetting(string $identifier, string $key): string
    {
        self::validateInput($identifier, $key);

        $conn = Database::getPdoConnection();

        try {
            $stmt = $conn->prepare("
					SELECT value 
					FROM mythicaldash_addons_settings 
					WHERE identifier = :identifier 
					AND `key` = :key 
					AND deleted = 'false'
					LIMIT 1
				");

            $stmt->execute([
                ':identifier' => self::sanitizeInput($identifier),
                ':key' => self::sanitizeInput($key),
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ? $result['value'] : '';
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to get setting: ' . $e->getMessage());
        }
    }

    /**
     * Get all settings for a plugin with proper error handling.
     *
     * @param string $identifier The identifier of the plugin
     *
     * @throws \PDOException If database operation fails
     * @throws InvalidArgumentException If input validation fails
     *
     * @return array All settings for the plugin
     */
    public static function getSettings(string $identifier): array
    {
        self::validateInput($identifier);

        $conn = Database::getPdoConnection();

        try {
            $stmt = $conn->prepare("
					SELECT * 
					FROM mythicaldash_addons_settings 
					WHERE identifier = :identifier 
					AND deleted = 'false'
				");

            $stmt->execute([
                ':identifier' => self::sanitizeInput($identifier),
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to get settings: ' . $e->getMessage());
        }
    }

    /**
     * Validate input parameters.
     *
     * @param string $identifier The identifier to validate
     * @param string|null $key The key to validate (optional)
     *
     * @throws InvalidArgumentException If validation fails
     */
    private static function validateInput(string $identifier, ?string $key = null): void
    {
        if (empty($identifier) || strlen($identifier) > 255) {
            throw new \InvalidArgumentException('Invalid identifier');
        }

        if ($key !== null && (empty($key) || strlen($key) > 255)) {
            throw new \InvalidArgumentException('Invalid key');
        }
    }

    /**
     * Sanitize input to prevent SQL injection.
     *
     * @param string $input The input to sanitize
     *
     * @return string The sanitized input
     */
    private static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}
