<?php

namespace MythicalDash\Addons\imagehostbridge\Events;

use Exception;
use MythicalDash\App;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;
use MythicalDash\Plugins\PluginHelper;

class Register extends \MythicalDash\Addons\imagehostbridge\ImageHostBridge
{
	public function __construct(string $username, string $email)
	{
		$app = App::getInstance(true);
		$logger = $app->getLogger();
		$config = $app->getConfig();
		if ($config->getSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, false)) {
			$user = User::getTokenFromEmail($email);
			$api_key = UUIDManager::generateUUID();
			User::updateInfo($user, UserColumns::IMAGE_HOSTING_UPLOAD_KEY, $api_key, false);
			$logger->debug('Image Hosting API Key: ' . $api_key);
		}
		
	}

	
}