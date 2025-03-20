<?php

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