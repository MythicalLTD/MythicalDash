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

namespace MythicalDash\Hooks\MythicalSystems\User;

class Cookies
{
    /**
     * Unset all cookies from inside the session!
     */
    public static function deleteAllCookies(): void
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
    }

    /**
     * Set a permanent cookie inside the session!
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     */
    public static function setCookie(string $name, string $value): void
    {
        // Sanitize input
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        // Set cookie
        setcookie($name, $value, time() + (10 * 365 * 24 * 60 * 60), '/');
    }

    /**
     * Unset a cookie from inside the session!
     *
     * @param string $name The name of the cookie to unset
     */
    public static function unSetCookie(string $name): void
    {
        // Sanitize input
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        // Unset cookie
        setcookie($name, '', -1, '/');
    }

    /**
     * Get all cookies.
     *
     * @return array All the cookies
     */
    public static function getCookies(): array
    {
        // Sanitize cookies retrieved from $_COOKIE and $_REQUEST arrays
        $sanitizedCookies = array_map('htmlspecialchars', array_merge($_COOKIE, $_REQUEST));

        return $sanitizedCookies;
    }

    /**
     * Get a cookie's value.
     *
     * @param string $name The cookie name!
     *
     * @return string|null The cookie value!
     */
    public static function getCookie(string $name): ?string
    {
        if (isset($_COOKIE[$name]) && !$_COOKIE[$name] == null) {
            return $_COOKIE[$name];
        }

        return null;

    }

    /**
     * Update a cookie's value.
     *
     * @param string $name The name of the cookie to update
     * @param string $value The new value for the cookie
     */
    public static function updateCookie(string $name, string $value): void
    {
        // Sanitize input
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        // Update cookie
        if (isset($_COOKIE[$name])) {
            setcookie($name, $value, time() + (10 * 365 * 24 * 60 * 60), '/');
            $_COOKIE[$name] = $value; // Update the cookie value in the current request
        }
    }
}
