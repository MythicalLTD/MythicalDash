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

use MythicalDash\App;

class PluginRequiredConfigs
{
    /**
     * Get required configs for a plugin.
     *
     * @param string $identifier The plugin identifier
     *
     * @return array The required configs
     */
    public static function getRequiredConfigs(string $identifier): array
    {
        $config = PluginConfig::getConfig($identifier);

        return $config['plugin']['requiredConfigs'] ?? [];
    }

    /**
     * Check if all required configs are set for a plugin.
     *
     * @param string $identifier The plugin identifier
     *
     * @return bool True if all required configs are set
     */
    public static function areRequiredConfigsSet(string $identifier): bool
    {
        try {
            $requiredConfigs = self::getRequiredConfigs($identifier);
            if (empty($requiredConfigs)) {
                return true;
            }

            $settings = PluginSettings::getSettings($identifier);
            $configuredKeys = array_column($settings, 'key');

            foreach ($requiredConfigs as $required) {
                if (!in_array($required, $configuredKeys)) {
                    App::getInstance(true)->getLogger()->warning(
                        "Missing required config '$required' for plugin: $identifier"
                    );

                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error(
                'Error checking required configs: ' . $e->getMessage()
            );

            return false;
        }
    }
}
