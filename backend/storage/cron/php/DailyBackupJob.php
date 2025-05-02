<?php

namespace MythicalDash\Cron;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MythicalDash\Chat\Announcements\Announcements;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\Earn\GyaniLinks;
use MythicalDash\Chat\Earn\Linkvertise;
use MythicalDash\Chat\Earn\ShareUS;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\Gateways\PayPalDB;
use MythicalDash\Chat\Gateways\StripeDB;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Redeem\RedeemCoins;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\Tickets\Tickets;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Config\PublicConfig;
use MythicalDash\Cron\Cron;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Chat\Earn\LinkPays;
use MythicalDash\Hooks\Backup;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;
use MythicalDash\Plugins\PluginConfig;
use MythicalDash\Cron\TimeTask;

class DailyBackupJob implements TimeTask
{
	public function run()
	{
		$cron = new Cron('daily-backup-job', '1D');
		try {
			$cron->runIfDue(function () {
				$app = \MythicalDash\App::getInstance(false, true);
				$chat = new BungeeChatApi();
				$config = $app->getConfig();

				$isEnabled = $config->getSetting(ConfigInterface::DAILY_BACKUP_ENABLED, 'true');
				if ($isEnabled === 'true') {
					$isEnabled = true;
				} else {
					$isEnabled = false;
				}

				$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Status: ' . ($isEnabled ? '&aEnabled' : '&cDisabled'));

				if ($isEnabled) {
					$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Starting backup...');
					$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Backup started at ' . date('Y-m-d H:i:s'));
					Backup::takeBackup();
					$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Backup completed at ' . date('Y-m-d H:i:s'));
				} else {
					$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Backup is disabled');
				}
			});
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}
}