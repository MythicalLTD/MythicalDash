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
