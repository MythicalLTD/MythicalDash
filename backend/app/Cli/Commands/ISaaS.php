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
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;

class ISaaS extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = App::getInstance();
        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            $cliApp->send('&7The application is not setup!');
            exit;
        }

        if (count($args) < 2) {
            $cliApp->send('&cUsage: php app ISaaS <setting_name> <setting_value>');
            exit;
        }

        $setting = $args[1];
        $value = $args[2];
        \MythicalDash\App::getInstance(true)->loadEnv();

        try {
            if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
                $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            } else {
                $cliApp->send('&cFailed to connect to the database: &rDatabase connection failed!');
                exit;
            }
            $config = new ConfigFactory($db->getPdo());
            
            $result = $config->setSetting($setting, $value);
            if ($result) {
                $cliApp->send('&aSetting &e' . $setting . ' &ahas been set to &e' . $value);
                $cliApp->send('&aThe setting has been updated successfully!');
            } else {
                $cliApp->send('&c✗ Failed to update setting.');
                exit;
            }
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error updating setting: ' . $e->getMessage());
            exit;
        }
    }

    public static function getDescription(): string
    {
        return 'SAAS Installer Command!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
