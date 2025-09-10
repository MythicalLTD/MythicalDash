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
use MythicalDash\Plugins\Mixins\MixinManager;

class PluginManager
{
    private array $plugins = [];
    private $logger;

    public function __construct()
    {
        $this->logger = App::getInstance(true)->getLogger();
    }

    public function loadKernel(): void
    {
        global $eventManager;
        try {
            $pluginFiles = $this->getPluginFiles();
            foreach ($pluginFiles as $plugin) {
                $this->processPlugin($plugin, $eventManager);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to start plugins: ' . $e->getMessage());
        }
    }

    /**
     * Get the loaded memory plugins.
     *
     * @return array The loaded memory plugins
     */
    public function getLoadedMemoryPlugins(): array
    {
        try {
            return $this->plugins;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plugin names: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get plugins without loading them - used for cron jobs.
     *
     * @return array List of plugin names without loading
     */
    public function getPluginsWithoutLoader(): array
    {
        try {
            $pluginsDir = PluginHelper::getPluginsDir();
            $allFiles = scandir($pluginsDir);

            return array_values(array_filter($allFiles, function ($file) {
                return !in_array($file, ['.', '..', '', '.gitignore', '.gitkeep']);
            }));
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plugins without loader: ' . $e->getMessage());

            return [];
        }
    }

    public function doesPluginExist(string $identifier): bool
    {
        return array_key_exists($identifier, $this->plugins);
    }

    public function getEventManager(): PluginEvents
    {
        return new PluginEvents();
    }

    public function getLoadedPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get mixins for a specific plugin.
     *
     * @param string $plugin The plugin identifier
     *
     * @return array List of mixin instances
     */
    public function getPluginMixins(string $plugin): array
    {
        return MixinManager::getMixinsForPlugin($plugin);
    }

    /**
     * Check if a plugin has a specific mixin.
     *
     * @param string $plugin The plugin identifier
     * @param string $mixinId The mixin identifier
     *
     * @return bool True if the plugin has the mixin, false otherwise
     */
    public function hasPluginMixin(string $plugin, string $mixinId): bool
    {
        return MixinManager::pluginHasMixin($plugin, $mixinId);
    }

    private function getPluginFiles(): array
    {
        $pluginsDir = PluginHelper::getPluginsDir();
        $allFiles = scandir($pluginsDir);

        return array_filter($allFiles, function ($file) {
            return !in_array($file, ['.', '..', '', '.gitignore', '.gitkeep']);
        });
    }

    private function processPlugin(string $plugin, $eventManager): void
    {
        if (!PluginConfig::isValidIdentifier($plugin)) {
            $this->logger->warning('Invalid plugin identifier: ' . $plugin);

            return;
        }

        $config = $this->loadPluginConfig($plugin);
        if (!$config) {
            return;
        }

        $this->validateAndLoadPlugin($plugin, $config, $eventManager);
    }

    private function loadPluginConfig(string $plugin): ?array
    {
        $config = PluginHelper::getPluginConfig($plugin);
        if (empty($config)) {
            $this->logger->warning('Plugin config is empty for: ' . $plugin);

            return null;
        }

        if (!PluginConfig::isConfigValid($config)) {
            $this->logger->warning('Invalid config for plugin: ' . $plugin);

            return null;
        }

        return $config;
    }

    private function validateAndLoadPlugin(string $plugin, array $config, $eventManager): void
    {
        if (in_array($plugin, $this->plugins)) {
            $this->logger->error('Duplicated plugin identifier: ' . $plugin);

            return;
        }

        if (!PluginDependencies::checkDependencies($config)) {
            $this->logger->error('Plugin ' . $plugin . ' has unmet dependencies!');

            return;
        }

        // Load mixins for this plugin
        $this->loadMixinsForPlugin($plugin, $config);

        $this->loadPlugin($plugin, $config, $eventManager);
    }

    private function loadPlugin(string $plugin, array $config, $eventManager): void
    {
        $this->logger->debug('Plugin ' . $plugin . ' was loaded in the memory!');
        $this->plugins[] = $plugin;
        PluginProcessor::process($config['plugin']['identifier'], $eventManager);
    }

    /**
     * Load mixins for a plugin based on its configuration.
     *
     * @param string $plugin The plugin identifier
     * @param array $config The plugin configuration
     */
    private function loadMixinsForPlugin(string $plugin, array $config): void
    {
        try {
            // Check if plugin has mixins configured
            if (!isset($config['mixins']) || !is_array($config['mixins'])) {
                return;
            }

            $mixins = MixinManager::loadMixinsForPlugin($plugin);
            $mixinCount = count($mixins);

            if ($mixinCount > 0) {
                $this->logger->debug("Loaded {$mixinCount} mixins for plugin: {$plugin}");
            }
        } catch (\Throwable $e) {
            $this->logger->error("Failed to load mixins for plugin {$plugin}: " . $e->getMessage());
        }
    }
}
