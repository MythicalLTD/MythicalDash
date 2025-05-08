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

namespace MythicalDash\Services\ProxyCheck;

use MythicalDash\Chat\ProxyList\ProxyList;

class ProxyCheck
{
    public static function hasProxy(string $ip): bool
    {
		if ($ip == '127.0.0.1' || $ip == '::1' || $ip == 'localhost' || $ip == '0.0.0.0' || $ip == '::') {
			return false;
		}
        $proxy = ProxyList::exists($ip);
        if ($proxy) {
            return true;
        }

        return false;
    }
}
