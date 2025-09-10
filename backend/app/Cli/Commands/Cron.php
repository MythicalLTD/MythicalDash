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
