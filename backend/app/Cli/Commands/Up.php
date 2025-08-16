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

class Up extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        if (file_exists(__DIR__ . '/../../../storage/caches/maintenance.php')) {
            unlink(__DIR__ . '/../../../storage/caches/maintenance.php');
            $app->send('&aThe server is no longer in maintenance mode!');
            \MythicalDash\App::getInstance(true)->getLogger()->info('The server is no longer in maintenance mode!');
            exit;
        }
        \MythicalDash\App::getInstance(true)->getLogger()->error('The server is not in maintenance mode!');
        $app->send('&cThe server is not in maintenance mode!');
        exit;

    }

    public static function getDescription(): string
    {
        return 'Remove the server from maintenance mode';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
