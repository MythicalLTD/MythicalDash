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

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class AuthenticationException extends PterodactylException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid API credentials provided.');
    }

    public static function missingToken(): self
    {
        return new self('No API token provided.');
    }

    public static function tokenExpired(): self
    {
        return new self('API token has expired.');
    }
}
