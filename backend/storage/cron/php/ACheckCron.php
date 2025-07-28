<?php

namespace MythicalDash\Cron;


use MythicalDash\Config\ConfigInterface;
use MythicalDash\Cron\Cron;
use MythicalDash\Hooks\Backup;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;
use MythicalDash\Cron\TimeTask;

class ACheckCron implements TimeTask
{
	public function run()
	{
		$cron = new Cron('a-check-cron', '1M');
		try {
			$cron->runIfDue(function () {
				$app = \MythicalDash\App::getInstance(false, true);
				$chat = new BungeeChatApi();
				$config = $app->getConfig();

				$chat->sendOutputWithNewLine('&8[&bACheckCron&8] &7Status: &aEnabled');

					$chat->sendOutputWithNewLine('&8[&bACheckCron&8] &7Starting check...');
					$chat->sendOutputWithNewLine('&8[&bACheckCron&8] &7Check started at ' . date('Y-m-d H:i:s'));
					Backup::takeBackup();
					$chat->sendOutputWithNewLine('&8[&bACheckCron&8] &7Check completed at ' . date('Y-m-d H:i:s'));
			});
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}
}