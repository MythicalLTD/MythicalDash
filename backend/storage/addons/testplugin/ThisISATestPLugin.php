<?php

namespace MythicalDash\Addons\testplugin;

use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Plugins\MythicalDashPlugin;

class ThisISATestPLugin implements MythicalDashPlugin
{
	/**
	 * @inheritDoc
	 */
	public static function processEvents(\MythicalDash\Plugins\PluginEvents $event): void
	{

	}

	/**
	 * @inheritDoc
	 */
	public static function pluginInstall(): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public static function pluginUninstall(): void
	{
	}
}