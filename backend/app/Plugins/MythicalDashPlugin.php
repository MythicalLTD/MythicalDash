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

interface MythicalDashPlugin
{
    /**
     * Process the events for the plugin.
     *
     * @param PluginEvents $event The event to process
     */
    public static function processEvents(PluginEvents $event): void;

    /**
     * Process the plugin install.
     */
    public static function pluginInstall(): void;

    /**
     * Process the plugin uninstall.
     */
    public static function pluginUninstall(): void;
}
