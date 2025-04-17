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
    }
}
