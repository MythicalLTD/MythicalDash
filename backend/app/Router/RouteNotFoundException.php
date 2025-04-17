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

namespace MythicalDash\Router;

use Exception;

/**
 * An exception class representing that a route hasn't been found.
 */
class RouteNotFoundException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param \Exception|null $previous Previous exception, if any
     */
    public function __construct(string $message, int $code = 404, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Convert the exception to a string representation.
     *
     * @return string String representation of the exception
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
