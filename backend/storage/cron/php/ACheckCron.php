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
			});
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}
}