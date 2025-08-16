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
use MythicalDash\Chat\Database;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;

class Setsetting extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = App::getInstance();
        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            $cliApp->send('&7The application is not setup!');
            exit;
        }

        $cliApp->send('&aPlease enter the setting you want to update:');
        $setting = readline('> ');

        $cliApp->send('&aPlease enter the value you want to set:');
        $value = readline('> ');
        \MythicalDash\App::getInstance(true)->loadEnv();

        try {
            if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
                $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            } else {
                $cliApp->send('&cFailed to connect to the database: &rDatabase connection failed!');
                exit;
            }
            $config = new ConfigFactory($db->getPdo());
            $config->setSetting($setting, $value);
        } catch (\Exception $e) {
            $cliApp->send('&cAn error occurred while connecting to the database: ' . $e->getMessage());
            exit;
        }

        $cliApp->send('&aSetting &e' . $setting . ' &ahas been set to &e' . $value);

        $cliApp->send('&aThe application has been setup!');
    }

    public static function getDescription(): string
    {
        return 'Update a setting!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
