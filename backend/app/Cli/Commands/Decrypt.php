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

class Decrypt extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        $string = readline('Enter the encrypted string: ');
        $app->sendOutputWithNewLine('String: ' . \MythicalDash\App::getInstance(true)->decrypt($string) . '');
    }

    public static function getDescription(): string
    {
        return 'Decrypt a MythicalDash encrypted string';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
