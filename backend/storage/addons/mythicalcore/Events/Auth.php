<?php

namespace MythicalDash\Addons\mythicalcore\Events;

use Exception;
use MythicalDash\App;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Plugins\PluginHelper;

class Auth extends \MythicalDash\Addons\mythicalcore\MythicalCore
{
	/**
	 * Example usage of plugins (Referrals)
	 * 
	 * @param string $username
	 * @param string $email
	 */
	public function __construct(string $username, string $email)
	{
		$app = App::getInstance(true);
		$config = $app->getConfig();
		
	}
}