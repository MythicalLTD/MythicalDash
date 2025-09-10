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

class Rebuild extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            \MythicalDash\App::getInstance(true)->getLogger()->warning('Executed a command without a .env file');
            $app->send('The .env file does not exist. Please create one before running this command');
            exit;
        }
        $app->send('&aRebuilding the database...');

        $app->send('&7Are you sure you want to rebuild the database? This will delete all data! Type &ayes &7to continue or &cno &7to cancel.');
        $app->send('&7This action is irreversible!');
        $app->send('&7Type your answer below:');
        $line = trim(readline('> '));

        if ($line !== 'yes') {
            $app->send('&cRebuild cancelled.');

            return;
        }

        $app->send('&aRebuilding...');

        try {
            \MythicalDash\App::getInstance(true)->loadEnv();
            if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
                $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            } else {
                $app->send('&cFailed to connect to the database: &rDatabase connection failed!');
                exit;
            }
            $db = $db->getPdo();
        } catch (\Exception $e) {
            $app->send('&cFailed to connect to the database: &r' . $e->getMessage());
            exit;
        }

        try {
            $db->query('SET foreign_key_checks = 0');
            $tables = $db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $db->query("DROP TABLE `$table`");
            }
            $db->query('SET foreign_key_checks = 1');
        } catch (\Exception $e) {
            $app->send('&cFailed to rebuild the database: &r' . $e->getMessage());
            exit;
        }

        $app->send('&aDatabase nuked successfully.');
        $app->send('&7Please run the migrations to rebuild the database.');
        exit;
    }

    public static function getDescription(): string
    {
        return 'Rebuild the database!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
