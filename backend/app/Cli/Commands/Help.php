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
