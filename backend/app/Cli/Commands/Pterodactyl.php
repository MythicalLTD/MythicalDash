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

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Cli\App as CliApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Pterodactyl\Admin\Resources\NestsResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\NodesResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource;
use MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource;

class Pterodactyl extends CliApp implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = CliApp::getInstance();
        $appInstance = App::getInstance(true);

        if (!isset($args[0])) {
            $cliApp->send('&cPlease provide a subcommand!');

            return;
        }

        switch ($args[1]) {
            case 'configure':
                self::configurePterodactyl($cliApp, $appInstance);
                break;
            case 'test':
                self::testConnection($cliApp, $appInstance);
                break;
            case 'logs':
                self::getLogs($cliApp, $appInstance);
                break;
            default:
                $cliApp->send('&cInvalid subcommand!');
                break;
        }
    }

    public static function getDescription(): string
    {
        return 'Manage Pterodactyl panel configuration and utilities';
    }

    public static function getSubCommands(): array
    {
        return [
            'configure' => 'Configure Pterodactyl panel URL and API key',
            'test' => 'Test connection to Pterodactyl panel',
            'logs' => 'View recent panel activity',
        ];
    }

    private static function configurePterodactyl(CliApp $cliApp, App $appInstance): void
    {
        $cliApp->send('&7Do you want to configure the Pterodactyl panel settings? [y/n]');
        $answer = strtolower(readline('> '));

        if ($answer !== 'y') {
            $cliApp->send('&cConfiguration cancelled.');

            return;
        }

        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            // Get current values
            $currentUrl = $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
            $currentApiKey = $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '');

            $cliApp->send("&7Current panel URL: &e{$currentUrl}");
            $cliApp->send('&7Enter new panel URL (or press enter to keep current):');
            $newUrl = readline('> ');

            if (!empty($newUrl)) {
                $config->setSetting(ConfigInterface::PTERODACTYL_BASE_URL, rtrim($newUrl, '/'));
                $cliApp->send('&aPanel URL updated successfully!');
            }

            $cliApp->send('&7Current API key is ' . (!empty($currentApiKey) ? '&aset' : '&cnot set'));
            $cliApp->send('&7Enter new API key (or press enter to keep current):');
            $newApiKey = readline('> ');

            if (!empty($newApiKey)) {
                $config->setSetting(ConfigInterface::PTERODACTYL_API_KEY, $newApiKey);
                $cliApp->send('&aAPI key updated successfully!');
            }

            $cliApp->send('&7Pterodactyl panel configuration completed successfully!');

            $cliApp->send('&7Do you want to test the connection to the Pterodactyl panel? [y/n]');
            $answer = strtolower(readline('> '));
            if ($answer === 'y') {
                self::testConnection($cliApp, $appInstance);
            }

        } catch (\Exception $e) {
            $cliApp->send('&cError: ' . $e->getMessage());
        }
    }

    private static function testConnection(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            $url = $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
            $apiKey = $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '');

            if (empty($url) || empty($apiKey)) {
                $cliApp->send('&cPterodactyl panel is not configured. Please run &epterodactyl configure &cfirst.');

                return;
            }

            $userResource = new UsersResource($url, $apiKey);
            $serverResource = new ServersResource($url, $apiKey);
            $nodeResource = new NodesResource($url, $apiKey);
            $locationResource = new LocationsResource($url, $apiKey);
            $nestResource = new NestsResource($url, $apiKey);

            // Try to list users to test connection
            try {
                $userResource->listUsers(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with users]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

            $cliApp->send('&7Testing with servers...');
            try {
                $serverResource->listServers(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with servers]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

            $cliApp->send('&7Testing with locations...');
            try {
                $locationResource->listLocations(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with locations]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

            $cliApp->send('&7Testing with nests...');
            try {
                $nestResource->listNests(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with nests]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

            $cliApp->send('&7Testing with nodes...');
            try {
                $nodeResource->listNodes(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with nodes]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

            $cliApp->send('&7Testing with eggs...');
            try {
                $nestResource->listEggs(1, 1);
                $cliApp->send('&aConnection to Pterodactyl panel successful! [Tested with eggs]');
            } catch (\Exception $e) {
                $cliApp->send('&cConnection failed: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            $cliApp->send('&cConnection failed: ' . $e->getMessage());
        }
    }

    private static function getLogs(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            $url = $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
            $apiKey = $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '');

            if (empty($url) || empty($apiKey)) {
                $cliApp->send('&cPterodactyl panel is not configured. Please run &epterodactyl configure &cfirst.');

                return;
            }

            $userResource = new UsersResource($url, $apiKey);

            // Get recent activity by listing users
            $users = $userResource->listUsers(1, 10);
            $cliApp->send('&7Recent panel activity:');
            $cliApp->send('&7Last 10 users in the system:');
            foreach ($users['data'] as $user) {
                $cliApp->send("&7- &e{$user['attributes']['username']} &7(&e{$user['attributes']['email']}&7)");
            }

        } catch (\Exception $e) {
            $cliApp->send('&cFailed to get logs: ' . $e->getMessage());
        }
    }
}
