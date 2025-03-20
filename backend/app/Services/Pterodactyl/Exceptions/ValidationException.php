<?php

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class ValidationException extends PterodactylException
{
    public static function withErrors(array $errors): self
    {
        $exception = new self('The given data was invalid.');
        return $exception->setErrors($errors);
    }
} 