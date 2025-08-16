<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
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
