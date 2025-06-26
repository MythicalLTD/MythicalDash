<?php

namespace MythicalDash\Addons\imagehostbridge;

use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Plugins\Events\Events\UserEvent;
use MythicalDash\Plugins\MythicalDashPlugin;

class ImageHostBridge implements MythicalDashPlugin
{
	/**
	 * @inheritDoc
	 */
	public static function processEvents(\MythicalDash\Plugins\PluginEvents $event): void
	{
		$event->on(AuthEvent::onAuthRegisterSuccess(), function (string $username, string $email) : void {
			new \MythicalDash\Addons\imagehostbridge\Events\Register($username, $email);
		});

		$event->on(AuthEvent::onAuthLoginSuccess(), function (string $login) : void {
			new \MythicalDash\Addons\imagehostbridge\Events\Login($login);
		});

		$event->on(AppEvent::onRouterReady(), function (\MythicalDash\Router\Router $router) : void {
			new \MythicalDash\Addons\imagehostbridge\Events\Router($router);
		});

		$event->on(UserEvent::onUserDelete(), function (string $user) : void {
			new \MythicalDash\Addons\imagehostbridge\Events\DeleteUser($user);
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