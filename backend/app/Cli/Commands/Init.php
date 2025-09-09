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
use MythicalDash\App as MainApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;

class Init extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();

        $appInstance = MainApp::getInstance(true, false);
        $appInstance->loadEnv();
        if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT']
            );
        } else {
            $app->send('&cFailed to connect to the database: &rDatabase connection failed!');
            exit;
        }
        $config = new ConfigFactory($db->getPdo());

        $appUrlCfg = $config->getDBSetting(ConfigInterface::APP_URL, null);

        $app->send('&7Please enter your application URL (e.g., https://client.example.com):');
        $app->send('&cPlease do not include a trailing slash');
        $app->send('&cAlso please do not place in your panel url! This is the place where the dashboard will run!');
        $appUrl = readline('> ');

        if (empty($appUrl)) {
            if (!empty($appUrlCfg)) {
                $appUrl = $appUrlCfg;
            } else {
                $app->send('&cApplication URL cannot be empty.');
                exit;
            }
        }

        if (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
            if (!empty($appUrlCfg)) {
                $appUrl = $appUrlCfg;
            } else {
                $app->send('&cInvalid URL format.');
                exit;
            }
        }

        $config->setSetting(ConfigInterface::APP_URL, $appUrl);
        $app->send('&aApplication URL and license key saved successfully.');
    }

    public static function getDescription(): string
    {
        return 'Configure the application';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
