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

class ServerException extends PterodactylException
{
    public static function serverOffline(): self
    {
        return new self('The server is currently offline.');
    }

    public static function transferInProgress(): self
    {
        return new self('A server transfer is currently in progress.');
    }

    public static function backupInProgress(): self
    {
        return new self('A backup operation is currently in progress.');
    }

    public static function powerActionInProgress(): self
    {
        return new self('A power action is currently in progress.');
    }

    public static function installationInProgress(): self
    {
        return new self('Server installation is currently in progress.');
    }

    public static function diskSpaceExceeded(): self
    {
        return new self('Server has exceeded its allocated disk space.');
    }

    public static function reinstallInProgress(): self
    {
        return new self('A server reinstall is currently in progress.');
    }
}
