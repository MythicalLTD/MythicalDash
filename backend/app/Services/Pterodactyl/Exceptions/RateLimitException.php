<?php

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class RateLimitException extends PterodactylException
{
    protected int $retryAfter;

    public static function withRetryAfter(int $seconds): self
    {
        $exception = new self('Too many requests. Please try again later.');
        $exception->retryAfter = $seconds;
        return $exception;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
} 