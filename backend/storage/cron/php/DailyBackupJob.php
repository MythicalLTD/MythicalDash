<?php

namespace MythicalDash\Cron;


use MythicalDash\Chat\TimedTask;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Cron\Cron;
use MythicalDash\Hooks\Backup;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;
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

				$isEnabled = $config->getDBSetting(ConfigInterface::DAILY_BACKUP_ENABLED, 'true');
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
					TimedTask::markRun('daily-backup-job', true, 'DailyBackupJob heartbeat');
				} else {
					$chat->sendOutputWithNewLine('&8[&bDaily Backup&8] &7Backup is disabled');
					TimedTask::markRun('daily-backup-job', false, 'DailyBackupJob is disabled');
				}
			});
		} catch (\Throwable $e) {
			TimedTask::markRun('daily-backup-job', false, $e->getMessage());
		}
	}
}