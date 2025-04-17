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

namespace MythicalDash\Hooks\MythicalSystems;

class Main
{
    /**
     * Init the library.
     */
    public static function init(): bool
    {
        return true;
    }

    /**
     * Check if the connection is over https!
     */
    public static function isHTTPS(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        return false;
    }

    /**
     * Returns the main app url.
     */
    public static function getUrl(): string
    {
        $prot = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $svhost = $_SERVER['HTTP_HOST'];
        $appURL = $prot . '://' . $svhost;

        return $appURL;
    }
}
