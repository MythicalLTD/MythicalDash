<?php

namespace MythicalDash\Addons\mythicalcore\Events;

use MythicalDash\App;
use MythicalDash\Plugins\PluginHelper;

class Router extends \MythicalDash\Addons\mythicalcore\MythicalCore {

	public function __construct(\MythicalDash\Router\Router $router) {
		$router->add('/api/system/mythicalcore', function (): void {
			$appInstance = App::getInstance(true);
			$appInstance->allowOnlyGET();
			$pluginConfig = PluginHelper::getPluginConfig('mythicalcore');
			
			$appInstance->OK('MythicalCore is ready',[
				'plugin' => $pluginConfig,
			]);
		});
	}
}