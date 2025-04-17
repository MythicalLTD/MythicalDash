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

namespace MythicalDash\Plugins\Mixins;

use MythicalDash\App;

/**
 * Abstract base class for plugin mixins.
 *
 * This class provides common functionality for mixins and implements
 * the basic methods required by the MythicalDashMixin interface.
 */
abstract class AbstractMixin implements MythicalDashMixin
{
    /** @var string The plugin identifier that this mixin is attached to */
    protected string $pluginIdentifier = '';

    /** @var array Configuration for this mixin */
    protected array $config = [];

    protected $logger;

    public function initialize(string $pluginIdentifier, array $config = []): void
    {
        $this->pluginIdentifier = $pluginIdentifier;
        $this->config = $config;
        $this->logger = App::getInstance(true)->getLogger();

        $this->onInitialize();
    }

    /**
     * Get the plugin identifier.
     *
     * @return string The plugin identifier
     */
    public function getPluginIdentifier(): string
    {
        return $this->pluginIdentifier;
    }

    /**
     * Get the mixin configuration.
     *
     * @return array The mixin configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if key is not found
     *
     * @return mixed The configuration value or default
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Hook method called after initialization.
     *
     * Override this method in your mixin to perform additional initialization.
     */
    protected function onInitialize(): void
    {
        // Default implementation does nothing
    }

    /**
     * Log a debug message.
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    protected function debug(string $message, array $context = []): void
    {
        $mixinId = static::getMixinIdentifier();
        $this->logger->debug("[Mixin:{$mixinId}] {$message} " . json_encode($context));
    }

    /**
     * Log an error message.
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    protected function error(string $message, array $context = []): void
    {
        $mixinId = static::getMixinIdentifier();
        $this->logger->error("[Mixin:{$mixinId}] {$message} " . json_encode($context));
    }
}
