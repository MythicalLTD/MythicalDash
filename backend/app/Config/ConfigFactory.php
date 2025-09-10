<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Config;

use MythicalDash\App;
use MythicalDash\Hooks\MythicalSystems\Utils\XChaCha20;

class ConfigFactory
{
    private \PDO $db;
    private string $encryption_key;
    private array $cache = [];

    private string $table_name = 'mythicaldash_settings';

    public function __construct(\PDO $db)
    {
        try {
            $this->db = $db;
        } catch (\Exception $e) {
            throw new \Exception('Failed to connect to the MYSQL Server! ', $e->getMessage());
        }
        $this->encryption_key = $_ENV['DATABASE_ENCRYPTION_KEY'];
    }

    /**
     * Get a setting from the database.
     *
     * @param string $name The name of the setting
     *
     * @return string|null The value of the setting
     *
     * @deprecated Use getDBSetting instead
     */
    public function getSetting(string $name, ?string $fallback = null): ?string
    {
        // Check if the setting is in the cache
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $stmt = $this->db->prepare("SELECT * FROM {$this->table_name} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $result['value'] = XChaCha20::decrypt($result['value'], $this->encryption_key);
            // Store the result in the cache
            $this->cache[$name] = $result['value'];

            return $result['value'];
        }

        return $fallback ?? null;
    }

    /**
     * Get a setting from the database, with reflection fallback to default values.
     *
     * @param string $name The name of the setting
     * @param string|null $fallback Fallback value if reflection fails
     *
     * @return string|null The value of the setting from database or default values, or fallback if reflection fails
     */
    public function getDBSetting(string $name, ?string $fallback = null): ?string
    {
        // Check if the setting is in the cache
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->table_name} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $result['value'] = XChaCha20::decrypt($result['value'], $this->encryption_key);
            // Store the result in the cache
            $this->cache[$name] = $result['value'];

            return $result['value'];
        }

        // If not found in database, try to get default value using reflection
        try {
            $reflection = new \ReflectionClass('MythicalDash\Config\PublicConfig');
            $method = $reflection->getMethod('getPublicSettingsWithDefaults');
            $defaultValues = $method->invoke(null);

            if (isset($defaultValues[$name])) {
                // Store the default value in cache
                $this->cache[$name] = $defaultValues[$name];

                return $defaultValues[$name];
            }
        } catch (\ReflectionException $e) {
            // Log the error but don't throw it
            error_log('Failed to get default value via reflection for setting "' . $name . '": ' . $e->getMessage());

            // Return fallback if reflection fails
            return $fallback;
        }

        return $fallback ?? null;
    }

    public function getSettings(array $columns = []): array
    {
        $query = "SELECT name, value FROM {$this->table_name}";
        if (!empty($columns)) {
            $placeholders = array_fill(0, count($columns), '?');
            $query .= ' WHERE name IN (' . implode(',', $placeholders) . ')';
        }
        $query .= ' ORDER BY name ASC';
        $stmt = $this->db->prepare($query);
        if (!empty($columns)) {
            $stmt->execute($columns);
        } else {
            $stmt->execute();
        }
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $result) {
            $decrypted = XChaCha20::decrypt($result['value'], $this->encryption_key);
            $settings[$result['name']] = $decrypted;
            $this->cache[$result['name']] = $decrypted;
        }

        return $settings;
    }

    /**
     * Set a setting in the database.
     *
     * @param string $name The name of the setting
     * @param string $value The value of the setting
     *
     * @throws \Exception If the setting already exists
     *
     * @return bool True if the setting was set successfully
     */
    public function setSetting(string $name, string $value): bool
    {
        $encrypted_value = XChaCha20::encrypt($value, $this->encryption_key);
        $stmt = $this->db->prepare("INSERT INTO {$this->table_name} (name, value, date) VALUES (:name, :value, NOW()) ON DUPLICATE KEY UPDATE value = :value, date = NOW()");
        $result = $stmt->execute(['name' => $name, 'value' => $encrypted_value]);
        if ($result) {
            // Update the cache
            $this->cache[$name] = $value;
        }

        return $result;
    }

    /**
     * ⚠️ DANGER ZONE - HANDLE WITH EXTREME CAUTION ⚠️.
     *
     * This function is used to dump all settings from the database.
     *
     * WARNING: This function will return ALL settings from the database in their decrypted form.
     * This includes potentially sensitive information like API keys, tokens, and credentials.
     *
     * Only use this function for debugging purposes in a secure environment.
     * Never expose this data publicly or log it to files that could be accessed by others.
     *
     * The settings are returned as a simple key-value array with no encryption.
     * Be extremely careful with how you handle and store this data.
     *
     * @return array All settings from database in plain text
     */
    public function dumpSettings(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table_name} ORDER BY name ASC");
        $stmt->execute();
        $appInstance = App::getInstance(true);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['name']] = $appInstance->decrypt($result['value']);
        }

        return $settings;
    }

    public static function getConfigurableSettings(): array
    {
        $ref = new \ReflectionClass(ConfigInterface::class);

        return $ref->getConstants();
    }
}
