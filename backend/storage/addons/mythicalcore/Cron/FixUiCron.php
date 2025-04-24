<?php

namespace MythicalDash\Addons\mythicalcore\Cron;

use MythicalDash\Addons\mythicalcore\MythicalCore;
use MythicalDash\Cli\App;
use MythicalDash\Cron\Cron;
use MythicalDash\Cron\TimeTask;

class FixUiCron extends MythicalCore implements TimeTask {
    
    /**
     * Run the cronjob
     */
    public function run(): void {
		$cron = new Cron("fix-ui","1M");
		$cron->runIfDue(function() {
			App::sendOutputWithNewLine('&aRunning UI fix cronjob...');
		});
    
    }
}