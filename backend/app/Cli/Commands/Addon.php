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

class Addon extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {

        /**
         * Initialize the plugin manager.
         */
        require __DIR__ . '/../../../boot/kernel.php';
        global $pluginManager;
        define('APP_ADDONS_DIR', __DIR__ . '/../../../storage/addons');
        define('APP_DEBUG', false);
        $pluginManager->loadKernel();

        if (count($args) > 0) {
            switch ($args[1]) {
                case 'install':
                    // Install an addon.
                    break;
                case 'uninstall':
                    // Uninstall an addon.
                    break;
                case 'list':
                    self::getInstance()->send('&5&lMythical&d&lDash &7- &d&lAddons');
                    self::getInstance()->send('');
                    $addons = $pluginManager->getLoadedMemoryPlugins();
                    foreach ($addons as $plugin) {
                        $addonConfig = \MythicalDash\Plugins\PluginConfig::getConfig($plugin);
                        $name = $addonConfig['plugin']['name'];
                        $version = $addonConfig['plugin']['version'];
                        $description = $addonConfig['plugin']['description'];
                        if ($addonConfig['plugin']['type'] == $type) {
                            self::getInstance()->send("&7 - &b{$name} &8> &d{$version} &8> &7{$description}");
                            self::getInstance()->send('');
                        }
                    }
                    self::getInstance()->send('');
                    break;
                case 'update':
                    // Update an addon.
                    break;
                default:
                    self::getInstance()->send('&cInvalid subcommand!');
                    break;
            }
        } else {
            self::getInstance()->send('&cPlease provide a subcommand!');
        }
    }

    public static function getDescription(): string
    {
        return 'Manage your addons form the command line.';
    }

    public static function getSubCommands(): array
    {
        return [
            'install' => 'Install an addon.',
            'uninstall ' => 'Uninstall an addon.',
            'list' => 'List all installed addons.',
        ];
    }
}
