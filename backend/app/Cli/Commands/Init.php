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
