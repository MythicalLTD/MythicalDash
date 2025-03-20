<?php

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