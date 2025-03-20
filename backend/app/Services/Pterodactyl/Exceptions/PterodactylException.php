<?php

namespace MythicalDash\Services\Pterodactyl\Exceptions;

use Exception;

class PterodactylException extends Exception
{
    protected array $errors = [];

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 