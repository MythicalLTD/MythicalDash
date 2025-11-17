<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Cron;

use MythicalDash\Hooks\Backup;
use MythicalDash\Chat\TimedTask;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi;

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
