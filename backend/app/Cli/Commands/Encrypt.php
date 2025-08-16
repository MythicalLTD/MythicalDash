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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;

class Encrypt extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        $string = readline('Enter the string to encrypt: ');
        $app->sendOutputWithNewLine('String: ' . \MythicalDash\App::getInstance(true)->encrypt($string) . '');
    }

    public static function getDescription(): string
    {
        return 'Encrypt a MythicalDash string';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
