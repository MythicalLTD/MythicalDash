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

namespace MythicalDash\Chat\columns;

class EmailVerificationColumns
{
    public static string $code = 'code';
    public static string $user = 'user';
    public static array $type = ['password', 'verify'];
    public static string $type_verify = 'verify';
    public static string $type_password = 'password';

    public static function getColumns(): array
    {
        return [
            'code' => 'code',
            'user' => 'user',
            'type' => ['password', 'verify'],
        ];
    }
}
