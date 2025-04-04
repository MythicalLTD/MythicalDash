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
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\User;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Chat\columns\UserColumns;

class MakeAdmin extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();

        $app->send('&7What account do you want to give admin?');
        $app->send("Please enter the account's email address");
        $email = trim(readline('> '));
        $appInstance = \MythicalDash\App::getInstance(true);
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            if (User::exists(UserColumns::EMAIL, $email)) {
                $token = User::getTokenFromEmail($email);
                if ($token) {
                    $app->send('&aUser found!');

                    User::updateInfo($token, UserColumns::ROLE_ID, '8', false);
                    $app->send('&aUser updated!');
                } else {
                    $app->send('&cError: User not found!');

                    return;
                }
            } else {
                $app->send('&cError: User not found!');

                return;
            }
        } catch (\Exception $e) {
            $app->send('&cError: ' . $e->getMessage());

            return;
        }

    }

    public static function getDescription(): string
    {
        return 'Make an account a system admin!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
