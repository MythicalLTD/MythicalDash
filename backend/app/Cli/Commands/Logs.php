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
 */

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\App as MainApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Services\Cloud\MythicalCloudLogs;

class Logs extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        $appInstance = MainApp::getInstance(true, false);

        $app->send('&7Starting log upload process...');
        $appInstance->getLogger()->info('Initiating log upload to cloud storage');

        // Upload dashboard logs
        $dashboardLogsUrl = MythicalCloudLogs::uploadDashboardLogsToCloud();

        // Upload web server logs
        $webServerLogsUrl = MythicalCloudLogs::uploadWebServerLogsToCloud();

        if ($dashboardLogsUrl && $webServerLogsUrl) {
            $appInstance->getLogger()->info('Successfully uploaded all logs to cloud storage');

            $app->send('&aLogs successfully uploaded to cloud storage!');
            $app->send('&7Dashboard Logs: &d' . $dashboardLogsUrl);
            $app->send('&7Web Server Logs: &d' . $webServerLogsUrl);
            $app->send('&7For support assistance, please provide both log URLs above');
        } else {
            $appInstance->getLogger()->error('Failed to upload one or more log files to cloud storage');
            $app->send('&cError: Failed to upload logs to cloud storage');

            if (!$dashboardLogsUrl) {
                $app->send('&c- Dashboard logs upload failed');
            }
            if (!$webServerLogsUrl) {
                $app->send('&c- Web server logs upload failed');
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
