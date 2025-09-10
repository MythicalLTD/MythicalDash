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
