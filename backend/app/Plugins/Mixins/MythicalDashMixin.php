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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Plugins\Mixins;

/**
 * Base interface for all plugin mixins.
 *
 * Mixins provide reusable functionality that can be included in multiple plugins.
 * They allow for better code organization and reuse across the plugin ecosystem.
 */
interface MythicalDashMixin
{
    /**
     * Initialize the mixin with the plugin identifier.
     *
     * @param string $pluginIdentifier The identifier of the plugin using this mixin
     * @param array $config Optional configuration for the mixin
     */
    public function initialize(string $pluginIdentifier, array $config = []): void;

    /**
     * Get the unique identifier for this mixin.
     *
     * @return string The mixin identifier
     */
    public static function getMixinIdentifier(): string;

    /**
     * Get the version of this mixin.
     *
     * @return string The mixin version
     */
    public static function getMixinVersion(): string;
}
