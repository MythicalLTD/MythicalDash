<?php

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class PermissionException extends PterodactylException
{
    public static function missingPermission(string $permission): self
    {
        return new self("Missing required permission: {$permission}");
    }

    public static function notServerOwner(): self
    {
        return new self('You are not the owner of this server.');
    }

    public static function adminRequired(): self
    {
        return new self('This action requires administrative privileges.');
    }

    public static function subUserRestricted(): self
    {
        return new self('This action is restricted for sub-users.');
    }
} 