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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class PluginHelper
{
    /**
     * Get the plugins directory.
     *
     * @return string The plugins directory
     */
    public static function getPluginsDir(): string
    {
        try {
            $pluginsDir = APP_ADDONS_DIR;
            if (is_dir($pluginsDir) && is_readable($pluginsDir) && is_writable($pluginsDir)) {
                return $pluginsDir;
            }

            return '';
        } catch (\Exception) {
            return '';
        }
    }

    /**
     * Get the plugin config.
     *
     * @param string $identifier The plugin identifier
     *
     * @return array The plugin config
     */
    public static function getPluginConfig(string $identifier): array
    {
        $app = \MythicalDash\App::getInstance(true);
        $logger = $app->getLogger();
        $configPath = self::getPluginsDir() . '/' . $identifier . '/conf.yml';

        try {
            $logger->debug('Getting plugin config for: ' . $identifier);

            if (!file_exists($configPath)) {
                $logger->warning('Plugin config file not found: ' . $configPath);

                return [];
            }

            $config = Yaml::parseFile($configPath);

            if (!is_array($config)) {
                $logger->warning('Invalid plugin config format for: ' . $identifier);

                return [];
            }

            $logger->debug('Successfully loaded config for plugin: ' . $identifier);

            return $config;

        } catch (ParseException $e) {
            $logger->error('YAML parse error in plugin config: ' . $identifier . ' - ' . $e->getMessage());

            return [];
        } catch (\Exception $e) {
            $logger->error('Failed to load plugin config: ' . $identifier . ' - ' . $e->getMessage());

            return [];
        }
    }
}
