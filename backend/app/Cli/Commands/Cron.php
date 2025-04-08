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
use MythicalDash\Cli\CommandBuilder;

class Cron extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        $app->sendOutputWithNewLine('&7Cron job started');
        $app->sendOutputWithNewLine('&aStarting app lifecycle...');
        $app->sendOutputWithNewLine('&7Found cron dir: ' . APP_CRON_DIR);

        if (file_exists(APP_CRON_DIR . '/runner.php')) {
            $app->sendOutputWithNewLine('&aFound runner.php');
            $app->sendOutputWithNewLine('&7Running runner.php...');

            // Open process with real-time output
            $process = popen('php ' . APP_CRON_DIR . '/runner.php 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    if ($output !== false) {
                        $app->sendOutputWithNewLine('&7' . trim($output));
                    }
                }
                $returnValue = pclose($process);

                if ($returnValue === 0) {
                    $app->sendOutputWithNewLine('&arunner.php completed successfully');
                } else {
                    $app->sendOutputWithNewLine('&crunner.php failed with exit code: ' . $returnValue);
                }
            } else {
                $app->sendOutputWithNewLine('&cFailed to start runner.php process');
            }

        } else {
            $app->sendOutputWithNewLine('&crunner.php not found');
        }

        // Also run bash runner if it exists
        if (file_exists(APP_CRON_DIR . '/runner.bash')) {
            $app->sendOutputWithNewLine('&aFound runner.bash');
            $app->sendOutputWithNewLine('&7Running runner.bash...');

            $process = popen('bash ' . APP_CRON_DIR . '/runner.bash 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    if ($output !== false) {
                        $app->sendOutputWithNewLine('&7' . trim($output));
                    }
                }
                $returnValue = pclose($process);

                if ($returnValue === 0) {
                    $app->sendOutputWithNewLine('&arunner.bash completed successfully');
                } else {
                    $app->sendOutputWithNewLine('&crunner.bash failed with exit code: ' . $returnValue);
                }
            }
        }

        $app->sendOutputWithNewLine('&aApp lifecycle finished');
        $app->sendOutputWithNewLine('&7Cron job finished');
    }

    public static function getDescription(): string
    {
        return 'Run MythicalDash cron jobs with live output';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
