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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Services\Cloud\MythicalCloudLogs;

class Logs extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        define('APP_DEBUG', false);
        $appInstance = \MythicalDash\App::getInstance(false, true);

        $app->send('&7Starting log upload process...');
        $appInstance->getLogger()->debug('Initiating log upload to cloud storage');

        // Upload dashboard logs
        $dashboardLogsUrl = MythicalCloudLogs::uploadDashboardLogsToCloud();

        // Upload web server logs
        $webServerLogsUrl = MythicalCloudLogs::uploadWebServerLogsToCloud();

        if ($dashboardLogsUrl && $webServerLogsUrl) {
            $appInstance->getLogger()->debug('Successfully uploaded all logs to cloud storage');

            $app->send('&aLogs successfully uploaded to cloud storage!');
            $app->send('&7Dashboard Logs: &d' . $dashboardLogsUrl);
            $app->send('&7Web Server Logs: &d' . $webServerLogsUrl);
            $app->send('&7For support assistance, please provide both log URLs above');
        } else {
            $appInstance->getLogger()->error('Failed to upload one or more log files to cloud storage');
            $app->send('&cError: Failed to upload logs to cloud storage');

            if (!$dashboardLogsUrl) {
                $app->send('&c- Dashboard logs upload failed: ' . $dashboardLogsUrl);
            }
            if (!$webServerLogsUrl) {
                $app->send('&c- Web server logs upload failed: ' . $webServerLogsUrl);
            }
        }
    }

    public static function getDescription(): string
    {
        return 'Upload logs to the cloud';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
