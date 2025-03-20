<?php

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class ResourceNotFoundException extends PterodactylException
{
    public static function forResource(string $resource, string $identifier): self
    {
        return new self("The requested {$resource} with identifier '{$identifier}' was not found.");
    }
} 