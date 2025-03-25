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

class PluginProcessor
{
    private static array $pluginCache = [];
    private static array $validationCache = [];

    /**
     * Get the event class for a plugin.
     *
     * @param string $identifier The plugin identifier
     *
     * @return MythicalDashPlugin|null The event class instance or null if not found
     */
    public static function getEventProcessor(string $identifier): ?MythicalDashPlugin
    {
        // Return cached instance if available
        if (isset(self::$pluginCache[$identifier])) {
            return self::$pluginCache[$identifier];
        }

        $logger = App::getInstance(true)->getLogger();
        $logger->debug('Initializing event processor for plugin: ' . $identifier);

        try {
            // Get and validate plugin config
            $config = PluginHelper::getPluginConfig($identifier);
            if (empty($config)) {
                $logger->warning('Invalid or empty config for plugin: ' . $identifier);

                return null;
            }

            $entryClass = $config['plugin']['name'];

            $eventClass = "MythicalDash\\Addons\\{$identifier}\\{$entryClass}";
            if (!class_exists($eventClass)) {
                $logger->warning("Event class not found: {$eventClass}");

                return null;
            }

            if (!is_subclass_of($eventClass, MythicalDashPlugin::class)) {
                $logger->warning("Class {$eventClass} does not implement MythicalDashPlugin");

                return null;
            }

            // Create and cache instance
            $instance = new $eventClass();
            self::$pluginCache[$identifier] = $instance;

            $logger->debug('Successfully initialized event processor for: ' . $identifier);

            return $instance;

        } catch (\Throwable $e) {
            $logger->error('Failed to initialize plugin event processor: ' . $e->getMessage(), false);

            return null;
        }
    }

    /**
     * Check if a plugin has a valid event implementation.
     *
     * @param string $identifier The plugin identifier
     *
     * @return bool True if plugin has valid event, false otherwise
     */
    public static function hasValidEvent(string $identifier): bool
    {
        // Use cached validation result if available
        if (isset(self::$validationCache[$identifier])) {
            return self::$validationCache[$identifier];
        }

        $result = self::getEventProcessor($identifier) !== null;
        self::$validationCache[$identifier] = $result;

        return $result;
    }

    /**
     * Process an event for a plugin.
     *
     * @param string $identifier The plugin identifier
     * @param PluginEvents $event The event to process
     */
    public static function process(string $identifier, PluginEvents $event): void
    {
        $logger = App::getInstance(true)->getLogger();
        $logger->debug('Processing event for plugin: ' . $identifier);

        try {
            $processor = self::getEventProcessor($identifier);
            if ($processor === null) {
                $logger->warning('No valid event processor found for plugin: ' . $identifier);

                return;
            }

            $processor->processEvents($event);
            $logger->debug('Successfully processed event for plugin: ' . $identifier);

        } catch (\Throwable $e) {
            $logger->error('Failed to process plugin event', false);
        }
    }
}
