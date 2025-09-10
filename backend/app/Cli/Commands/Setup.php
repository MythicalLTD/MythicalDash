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
use MythicalDash\Hooks\MythicalSystems\Utils\XChaCha20;

class Setup extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = App::getInstance();
        self::createDBConnection($cliApp);

        $cliApp->send('&aThe application has been setup!');
    }

    public static function getDescription(): string
    {
        return 'Setup the application!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    public static function createDBConnection(App $cliApp): void
    {
        $defultEncryption = 'xchacha20';
        $defultDBName = 'mythicaldash_remastered';
        $defultDBHost = '127.0.0.1';
        $defultDBPort = '3306';
        $defultDBUser = 'mythicaldash_remastered';
        $defultDBPassword = '';

        $cliApp->send("&7Please enter the database encryption &8[&e$defultEncryption&8]&7");
        $dbEncryption = readline('> ') ?: $defultEncryption;
        $allowedEncryptions = ['xchacha20'];
        if (!in_array($dbEncryption, $allowedEncryptions)) {
            $cliApp->send('&cInvalid database encryption.');
            exit;
        }

        $cliApp->send("&7Please enter the database name &8[&e$defultDBName&8]&7");
        $defultDBName = readline('> ') ?: $defultDBName;

        $cliApp->send("&7Please enter the database host &8[&e$defultDBHost&8]&7");
        $defultDBHost = readline('> ') ?: $defultDBHost;

        $cliApp->send("&7Please enter the database port &8[&e$defultDBPort&8]&7");
        $defultDBPort = readline('> ') ?: $defultDBPort;

        $cliApp->send("&7Please enter the database user &8[&e$defultDBUser&8]&7");
        $defultDBUser = readline('> ') ?: $defultDBUser;

        $cliApp->send("&7Please enter the database password &8[&e$defultDBPassword&8]&7");
        $defultDBPassword = readline('> ') ?: $defultDBPassword;

        try {
            $dsn = "mysql:host=$defultDBHost;port=$defultDBPort;dbname=$defultDBName";
            $pdo = new \PDO($dsn, $defultDBUser, $defultDBPassword);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $cliApp->send('&aSuccessfully connected to the MySQL database.');
        } catch (\PDOException $e) {
            $cliApp->send('&cFailed to connect to the MySQL database: ' . $e->getMessage());
            exit;
        }

        $envTemplate = 'DATABASE_HOST=' . $defultDBHost . '
DATABASE_PORT=' . $defultDBPort . '
DATABASE_USER=' . $defultDBUser . '
DATABASE_PASSWORD=' . $defultDBPassword . '
DATABASE_DATABASE=' . $defultDBName . '
DATABASE_ENCRYPTION=' . $dbEncryption . '
DATABASE_ENCRYPTION_KEY=' . XChaCha20::generateStrongKey(true) . '
REDIS_PASSWORD=eufefwefwefw
REDIS_HOST=127.0.0.1';

        $cliApp->send('&aEnvironment file created successfully.');
        $cliApp->send('&aEncryption key generated successfully.');

        $envFile = fopen(__DIR__ . '/../../../storage/.env', 'w');
        fwrite($envFile, $envTemplate);
        fclose($envFile);

        $cliApp->send('&aEnvironment file created successfully.');
    }
}
