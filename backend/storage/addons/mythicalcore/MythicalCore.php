<?php

namespace MythicalDash\Addons\mythicalcore;

use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Plugins\MythicalDashPlugin;

class MythicalCore implements MythicalDashPlugin
{

	/**
	 * @inheritDoc
	 */
	public static function processEvents(\MythicalDash\Plugins\PluginEvents $event): void
	{
		$event->on(AppEvent::onRouterReady(), function (\MythicalDash\Router\Router $router) : void {
			new \MythicalDash\Addons\mythicalcore\Events\Router($router);
		});

		$event->on(AuthEvent::onAuthRegisterSuccess(), function (string $username, string $email) : void {
			new \MythicalDash\Addons\mythicalcore\Events\Auth($username, $email);
		});
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