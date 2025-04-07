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

		try {
			if ($config->getSetting(ConfigInterface::REFERRALS_ENABLED, false)) {
				$newUserUuid = User::convertEmailToUUID(email: $email);
				$newUserToken = User::getTokenFromEmail($email);
				if ($newUserUuid) {
					// Generate a referral code
					$referralCode = $username . '_' . $app->generatePin();
					ReferralCodes::create($newUserUuid, $referralCode);

					if (isset($_GET['ref']) && $_GET['ref'] != '') {
						$referrerCode = ReferralCodes::getByCode($_GET['ref']);

						$referrerUuid = $referrerCode['user'];
						$referrerToken = User::getTokenFromUUID($referrerUuid);

						if ($referrerCode) {
							ReferralUses::create($referrerCode['id'], $newUserUuid);

							$newUserBonus = $app->getConfig()->getSetting(ConfigInterface::REFERRALS_COINS_PER_REFERRAL_REDEEMER, 15);
							User::updateInfo($newUserToken, UserColumns::CREDITS, $newUserBonus, false);

							$referrerBonus = $app->getConfig()->getSetting(ConfigInterface::REFERRALS_COINS_PER_REFERRAL, 35);
							User::updateInfo($referrerToken, UserColumns::CREDITS, $referrerBonus, false);
						}
					}

				} else {
					// Nothing
				}
			}
		} catch (Exception $exception) {
			$app->getLogger()->warning('Failed to process referrals: ' . $exception->getMessage());
		}
	}
}