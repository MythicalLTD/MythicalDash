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

class Help extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cmdInstance = self::getInstance();
        $cmdInstance->send($cmdInstance->bars);
        $cmdInstance->send('&5&lMythical&d&lDash &7- &d&lHelp');
        $cmdInstance->send('');

        $commands = scandir(__DIR__);

        foreach ($commands as $command) {
            if ($command === '.' || $command === '..' || $command === 'Command.php') {
                continue;
            }

            $command = str_replace('.php', '', $command);
            $commandClass = "MythicalDash\\Cli\\Commands\\$command";
            $commandFile = __DIR__ . "/$command.php";

            require_once $commandFile;

            if (!class_exists($commandClass)) {
                return;
            }

            $description = $commandClass::getDescription();
            $command = lcfirst($command);
            $subCommands = $commandClass::getSubCommands();
            $cmdInstance->send("&b{$command} &8> &7{$description}");

            if (!empty($subCommands)) {
                foreach ($subCommands as $subCommand => $description) {
                    $cmdInstance->send("    &8> &b{$command} {$subCommand} &8- &7{$description}");
                }
            }

        }
        $cmdInstance->send('');
        $cmdInstance->send('&d&lPlugin Commands:');
        $pluginDir = getcwd() . '/backend/storage/addons';
        if (is_dir($pluginDir)) {
            $plugins = array_diff(scandir($pluginDir), ['.', '..']);
            foreach ($plugins as $plugin) {
                $commandsFolder = $pluginDir . "/$plugin/Commands";
                if (!is_dir($commandsFolder)) {
                    continue;
                }
                $commandFiles = array_diff(scandir($commandsFolder), ['.', '..']);
                foreach ($commandFiles as $commandFile) {
                    if (!str_ends_with($commandFile, '.php')) {
                        continue;
                    }
                    $className = pathinfo($commandFile, PATHINFO_FILENAME);
                    $commandClass = "MythicalDash\\Addons\\$plugin\\Commands\\$className";
                    $commandFilePath = $commandsFolder . "/$commandFile";
                    require_once $commandFilePath;
                    if (!class_exists($commandClass)) {
                        continue;
                    }
                    $description = $commandClass::getDescription();
                    $command = lcfirst($className);
                    $subCommands = $commandClass::getSubCommands();
                    $cmdInstance->send("&b[{$plugin}] {$command} &8> &7{$description}");
                    if (!empty($subCommands)) {
                        foreach ($subCommands as $subCommand => $subDesc) {
                            $cmdInstance->send("    &8> &b{$command} {$subCommand} &8- &7{$subDesc}");
                        }
                    }
                }
            }
        }
        $cmdInstance->send('');
        $cmdInstance->send($cmdInstance->bars);
    }

    public static function getDescription(): string
    {
        return 'Get help for all commands';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
