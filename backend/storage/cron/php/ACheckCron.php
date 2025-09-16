<?php

namespace MythicalDash\Cron;


use MythicalDash\Cron\Cron;
use MythicalDash\Cron\TimeTask;
use MythicalDash\Chat\TimedTask;

class ACheckCron implements TimeTask
{
	public function run()
	{
		$cron = new Cron('a-check-cron', '1M');
		try {
			$cron->runIfDue(function () {
				TimedTask::markRun('a-check-cron', true, 'ACheckCron heartbeat');
			});
		} catch (\Throwable $e) {
			TimedTask::markRun('a-check-cron', false, $e->getMessage());
		}
	}
}