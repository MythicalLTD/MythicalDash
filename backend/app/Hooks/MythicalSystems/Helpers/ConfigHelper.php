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

namespace MythicalDash\Hooks\MythicalSystems\Helpers;

/**
 * Class ConfigHelper.
 */
class ConfigHelper
{
    private string $configPath;
    private array $configData;

    /**
     * ConfigHelper constructor.
     *
     * @param string $configFile the path to the configuration file
     *
     * @throws \Exception if the configuration file doesn't exist
     */
    public function __construct(string $configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Exception("Configuration file '$configFile' not found.");
        }
        $this->configPath = $configFile;
        $this->configData = $this->readConfig();
    }

    /**
     * Get a value from the configuration file.
     *
     * @param string $section the section in the configuration file
     * @param string $key the key within the section
     *
     * @return string|null the value associated with the key, or null if not found
     */
    public function get(string $section, string $key): ?string
    {
        return $this->configData[$section][$key] ?? null;
    }

    /**
     * Set a value in the configuration file.
     *
     * @param string $section the section in the configuration file
     * @param string $key the key within the section
     * @param string $value the value to set
     *
     * @return bool true if the operation succeeded, false otherwise
     */
    public function set(string $section, string $key, string $value): bool
    {
        $this->configData[$section][$key] = $value;

        return $this->writeConfig();
    }

    /**
     * Add a new section or key-value pair in the configuration file.
     *
     * @param string $section the section in the configuration file
     * @param string $key the key within the section
     * @param string $value the value to set
     *
     * @return bool true if the operation succeeded, false otherwise
     */
    public function add(string $section, string $key, string $value): bool
    {
        if (!isset($this->configData[$section])) {
            $this->configData[$section] = [];
        }
        $this->configData[$section][$key] = $value;

        return $this->writeConfig();
    }

    /**
     * Remove a section or key from the configuration file.
     *
     * @param string $section the section in the configuration file
     * @param string|null $key (Optional) The key within the section to remove. If null, the entire section will be removed.
     *
     * @return bool true if the operation succeeded, false otherwise
     */
    public function remove(string $section, ?string $key = null): bool
    {
        if ($key === null) {
            unset($this->configData[$section]);
        } else {
            unset($this->configData[$section][$key]);
        }

        return $this->writeConfig();
    }

    /**
     * Read the configuration file.
     *
     * @return array the parsed configuration data
     */
    private function readConfig(): array
    {
        $config = file_get_contents($this->configPath);

        return json_decode($config, true) ?: [];
    }

    /**
     * Write the configuration file.
     *
     * @return bool true if the operation succeeded, false otherwise
     */
    private function writeConfig(): bool
    {
        $this->configData['__last_updated'] = date('Y-m-d H:i:s');
        $jsonConfig = json_encode($this->configData, JSON_PRETTY_PRINT);

        return file_put_contents($this->configPath, $jsonConfig) !== false;
    }
}
