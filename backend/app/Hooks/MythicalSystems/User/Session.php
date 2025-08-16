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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Hooks\MythicalSystems\User;

class Session
{
    /**
     * Start or resume a session.
     */
    public static function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Destroy the current session.
     */
    public static function destroy(): void
    {
        session_destroy();
    }

    /**
     * Set a session variable.
     *
     * @param string $name The name of the session variable
     * @param mixed $value The value of the session variable
     */
    public static function set(string $name, $value): void
    {
        self::start();
        $_SESSION[$name] = $value;
    }

    /**
     * Get the value of a session variable.
     *
     * @param string $name The name of the session variable
     *
     * @return mixed|null The value of the session variable if it exists, null otherwise
     */
    public static function get(string $name): string|array|null
    {
        self::start();

        return $_SESSION[$name] ?? null;
    }

    /**
     * Unset a session variable.
     *
     * @param string $name The name of the session variable to unset
     */
    public static function unset(string $name): void
    {
        self::start();
        unset($_SESSION[$name]);
    }
}
