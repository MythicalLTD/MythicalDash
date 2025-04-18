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

        if (!isset($args[1])) {
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
            case 'debug':
                self::debug($cliApp, $appInstance);
                break;
            default:
                $cliApp->send('&cInvalid subcommand!');
                break;
        }
    }

    public static function debug(CliApp $cliApp, App $appInstance): void
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

            $cliApp->send('&7Debug mode enabled!');
            $cliApp->send('&7Pterodactyl panel URL: &e' . $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''));
            $cliApp->send('&7Pterodactyl API key: &e' . $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
            $cliApp->send('&7--------------------------------');

            $cliApp->send('&7What do you want to test?');
            $cliApp->send('&71. Users');
            $cliApp->send('&72. Servers');
            $cliApp->send('&73. Locations');
            $cliApp->send('&74. Nodes');
            $cliApp->send('&75. Nests');
            $cliApp->send('&76. Eggs');
            $cliApp->send('&77. All');
            $answer = strtolower(readline('> '));

            switch ($answer) {
                case '1':
                    self::testUsers($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '2':
                    self::testServers($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '3':
                    self::testLocations($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '4':
                    self::testNodes($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '5':
                    self::testNests($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '6':
                    self::testEggs($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                case '7':
                    self::testAll($cliApp, $appInstance, $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
                    break;
                default:
                    $cliApp->send('&cInvalid option!');
                    break;
            }

        } catch (\Exception $e) {
            $cliApp->send('&cError: ' . $e->getMessage());
        }
    }

    public static function testUsers(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing users...');

        try {
            $userResource = new UsersResource($url, $apiKey);
            $users = $userResource->listUsers(1, 50);

            $cliApp->send('&7List of users:');
            foreach ($users['data'] as $user) {
                $cliApp->send("&7ID: &e{$user['attributes']['id']} &7| Username: &e{$user['attributes']['username']} &7| Email: &e{$user['attributes']['email']}");
            }

            $cliApp->send('&7Enter user ID to view (or press enter to skip):');
            $userId = readline('> ');

            if (!empty($userId)) {
                $cliApp->send("&7Fetching user details for ID: &e{$userId}");
                try {
                    $userDetails = $userResource->getUserWithServers($userId);
                    // Debug the response structure
                    if (!is_array($userDetails)) {
                        $cliApp->send('&cError: Invalid response format');

                        return;
                    }

                    // Check if we have valid data
                    if (!isset($userDetails['object']) || !isset($userDetails['attributes'])) {
                        $cliApp->send('&cError: Missing user attributes in response');

                        return;
                    }

                    $attributes = $userDetails['attributes'];

                    $cliApp->send('&7User details:');
                    $cliApp->send('&7ID: &e' . ($attributes['id'] ?? 'N/A'));
                    $cliApp->send('&7Username: &e' . ($attributes['username'] ?? 'N/A'));
                    $cliApp->send('&7Email: &e' . ($attributes['email'] ?? 'N/A'));
                    $cliApp->send('&7First Name: &e' . ($attributes['first_name'] ?? 'N/A'));
                    $cliApp->send('&7Last Name: &e' . ($attributes['last_name'] ?? 'N/A'));
                    $cliApp->send('&7Admin: &e' . ($attributes['root_admin'] ? 'Yes' : 'No'));
                    $cliApp->send('&72FA Enabled: &e' . ($attributes['2fa'] ? 'Yes' : 'No'));
                    $cliApp->send('&7Created at: &e' . ($attributes['created_at'] ?? 'N/A'));
                    $cliApp->send('&7Updated at: &e' . ($attributes['updated_at'] ?? 'N/A'));

                    $cliApp->send('&7--------------------------------');

                    if (isset($attributes['relationships']['servers']['data'])
                        && is_array($attributes['relationships']['servers']['data'])) {
                        $servers = $attributes['relationships']['servers']['data'];
                        if (count($servers) > 0) {
                            $cliApp->send('&7Servers:');
                            foreach ($servers as $server) {
                                if (!isset($server['attributes'])) {
                                    continue;
                                }

                                $serverAttr = $server['attributes'];
                                $status = $serverAttr['status'] ?? 'unknown';
                                $suspended = $serverAttr['suspended'] ? '&c[SUSPENDED]' : '';
                                $memory = $serverAttr['limits']['memory'];
                                $disk = $serverAttr['limits']['disk'];
                                $cpu = $serverAttr['limits']['cpu'];

                                $cliApp->send(
                                    "&7ID: &e{$serverAttr['id']} " .
                                    "&7| Name: &e{$serverAttr['name']} " .
                                    "&7| Status: &e{$status} {$suspended}" .
                                    "&7| Resources: &eRAM: {$memory}MB, Disk: {$disk}MB, CPU: {$cpu}%"
                                );
                            }
                        } else {
                            $cliApp->send('&7No servers found for this user.');
                        }
                    } else {
                        $cliApp->send('&7No servers found for this user.');
                    }
                } catch (\Exception $e) {
                    $cliApp->send('&cError fetching user details: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $cliApp->send('&cError: ' . $e->getMessage());
        }
    }

    public static function testServers(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing servers...');
    }

    public static function testLocations(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing locations...');
    }

    public static function testNodes(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing nodes...');
    }

    public static function testNests(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing nests...');
    }

    public static function testEggs(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing eggs...');
    }

    public static function testAll(CliApp $cliApp, App $appInstance, string $url, string $apiKey): void
    {
        $cliApp->send('&7Testing all...');
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
            'debug' => 'Test specific pterodactyl panel api endpoints!',
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
