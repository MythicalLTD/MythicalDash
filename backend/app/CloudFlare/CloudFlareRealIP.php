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

namespace MythicalDash\CloudFlare;

class CloudFlareRealIP
{
    /**
     * Get the real client IP address, considering Cloudflare and Nginx proxy headers.
     *
     * Order of precedence:
     * 1. HTTP_CF_CONNECTING_IP (Cloudflare)
     * 2. HTTP_X_FORWARDED_FOR (first IP, may be a comma-separated list)
     * 3. HTTP_X_REAL_IP (set by Nginx)
     * 4. REMOTE_ADDR (fallback)
     *
     * @return string Real client IP address
     */
    public static function getRealIP()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For can be a comma+space separated list of IPs. The first is the original client.
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}
