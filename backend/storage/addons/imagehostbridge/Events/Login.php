<?php

namespace MythicalDash\Addons\imagehostbridge\Events;

use MythicalDash\App;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;

class Login extends \MythicalDash\Addons\imagehostbridge\ImageHostBridge
{
	public function __construct(string $login)
	{
		$app = App::getInstance(true);
		$config = $app->getConfig();
		$logger = $app->getLogger();
		if ($config->getSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, false)) {
			$user = User::getTokenFromEmail($login);
			$api_key = User::getInfo($user, UserColumns::IMAGE_HOSTING_UPLOAD_KEY, false);

			if ($api_key === '' || $api_key === null) {
				$api_key = UUIDManager::generateUUID();
				User::updateInfo($user, UserColumns::IMAGE_HOSTING_UPLOAD_KEY, $api_key, false);
			}
			$logger->debug('Image Hosting API Key: ' . $api_key);
		}
		
	}

	
}