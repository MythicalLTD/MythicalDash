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

namespace MythicalDash\Plugins\Mixins;

use MythicalDash\App;
use MythicalDash\Plugins\PluginHelper;

/**
 * Manager class for handling plugin mixins.
 *
 * This class provides functionality to load, register, and manage mixins
 * that can be used by multiple plugins.
 */
class MixinManager
{
    /** @var array Registered mixins by identifier */
    private static array $registeredMixins = [];

    /** @var array Mixin instances by plugin identifier and mixin identifier */
    private static array $mixinInstances = [];

    /**
     * Register a mixin class.
     *
     * @param string $mixinClass The fully qualified class name of the mixin
     *
     * @return bool True if registered successfully, false otherwise
     */
    public static function registerMixin(string $mixinClass): bool
    {
        $logger = App::getInstance(true)->getLogger();

        try {
            // Check if class exists and implements the mixin interface
            if (!class_exists($mixinClass)) {
                $logger->warning("Mixin class does not exist: {$mixinClass}");

                return false;
            }

            if (!is_subclass_of($mixinClass, MythicalDashMixin::class)) {
                $logger->warning("Class {$mixinClass} does not implement MythicalDashMixin");

                return false;
            }

            // Get mixin identifier and register
            $mixinId = $mixinClass::getMixinIdentifier();
            if (isset(self::$registeredMixins[$mixinId])) {
                $logger->warning("Mixin with identifier '{$mixinId}' is already registered");

                return false;
            }

            self::$registeredMixins[$mixinId] = $mixinClass;
            $logger->debug("Registered mixin: {$mixinId} ({$mixinClass})");

            return true;
        } catch (\Throwable $e) {
            $logger->error('Failed to register mixin: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a mixin instance for a specific plugin.
     *
     * @param string $pluginIdentifier The plugin identifier
     * @param string $mixinIdentifier The mixin identifier
     * @param array $config Optional configuration for the mixin
     *
     * @return MythicalDashMixin|null The mixin instance or null if not found/valid
     */
    public static function getMixin(string $pluginIdentifier, string $mixinIdentifier, array $config = []): ?MythicalDashMixin
    {
        $logger = App::getInstance(true)->getLogger();
        $key = "{$pluginIdentifier}:{$mixinIdentifier}";

        // Return cached instance if available
        if (isset(self::$mixinInstances[$key])) {
            return self::$mixinInstances[$key];
        }

        try {
            // Check if mixin is registered
            if (!isset(self::$registeredMixins[$mixinIdentifier])) {
                $logger->warning("No mixin registered with identifier: {$mixinIdentifier}");

                return null;
            }

            $mixinClass = self::$registeredMixins[$mixinIdentifier];
            $instance = new $mixinClass();

            // Initialize the mixin with the plugin identifier
            $instance->initialize($pluginIdentifier, $config);

            // Cache and return the instance
            self::$mixinInstances[$key] = $instance;
            $logger->debug("Created mixin instance: {$mixinIdentifier} for plugin {$pluginIdentifier}");

            return $instance;
        } catch (\Throwable $e) {
            $logger->error("Failed to get mixin {$mixinIdentifier} for plugin {$pluginIdentifier}: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all registered mixin identifiers.
     *
     * @return array List of mixin identifiers
     */
    public static function getRegisteredMixins(): array
    {
        return array_keys(self::$registeredMixins);
    }

    /**
     * Check if a plugin has a specific mixin.
     *
     * @param string $pluginIdentifier The plugin identifier
     * @param string $mixinIdentifier The mixin identifier
     *
     * @return bool True if the plugin has the mixin, false otherwise
     */
    public static function pluginHasMixin(string $pluginIdentifier, string $mixinIdentifier): bool
    {
        $key = "{$pluginIdentifier}:{$mixinIdentifier}";

        return isset(self::$mixinInstances[$key]);
    }

    /**
     * Load mixins for a plugin based on its configuration.
     *
     * @param string $pluginIdentifier The plugin identifier
     *
     * @return array Loaded mixin instances
     */
    public static function loadMixinsForPlugin(string $pluginIdentifier): array
    {
        $logger = App::getInstance(true)->getLogger();
        $loadedMixins = [];

        try {
            $config = PluginHelper::getPluginConfig($pluginIdentifier);
            if (empty($config) || !isset($config['mixins']) || !is_array($config['mixins'])) {
                // No mixins configured
                return [];
            }

            foreach ($config['mixins'] as $mixinId => $mixinConfig) {
                $mixinConfig = is_array($mixinConfig) ? $mixinConfig : [];
                $mixin = self::getMixin($pluginIdentifier, $mixinId, $mixinConfig);

                if ($mixin !== null) {
                    $loadedMixins[$mixinId] = $mixin;
                }
            }

            $logger->debug('Loaded ' . count($loadedMixins) . " mixins for plugin: {$pluginIdentifier}");

            return $loadedMixins;
        } catch (\Throwable $e) {
            $logger->error("Failed to load mixins for plugin {$pluginIdentifier}: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all mixins for a specific plugin.
     *
     * @param string $pluginIdentifier The plugin identifier
     *
     * @return array Mixin instances associated with the plugin
     */
    public static function getMixinsForPlugin(string $pluginIdentifier): array
    {
        $mixins = [];

        foreach (self::$mixinInstances as $key => $instance) {
            if (strpos($key, "{$pluginIdentifier}:") === 0) {
                $parts = explode(':', $key);
                $mixinId = $parts[1] ?? '';
                if ($mixinId) {
                    $mixins[$mixinId] = $instance;
                }
            }
        }

        return $mixins;
    }
}
