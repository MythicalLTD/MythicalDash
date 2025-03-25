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

use MythicalDash\Plugins\PluginEvents;

interface MythicalDashPlugin
{
	/**
	 * Process the events for the plugin
	 * 
	 * @param PluginEvents $event The event to process
	 * 
	 * @return void
	 */
    public static function processEvents(PluginEvents $event): void;

	/**
	 * Process the plugin install
	 * 
	 * @return void
	 */
	public static function pluginInstall() : void;

	/**
	 * Process the plugin uninstall
	 * 
	 * @return void
	 */
	public static function pluginUninstall(): void;
}
