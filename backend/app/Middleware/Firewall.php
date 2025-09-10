<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Middleware;

use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\ProxyCheck\ProxyCheck;

class Firewall implements MiddlewareBuilder
{
    public static function handle(App $app, string $context, ?Session $session = null): void
    {
        /**
         * Firewall check.
         */
        if ($app->getConfig()->getDBSetting(ConfigInterface::FIREWALL_ENABLED, 'false') == 'true') {
            /**
             * Block VPNs.
             */
            if ($app->getConfig()->getDBSetting(ConfigInterface::FIREWALL_BLOCK_VPN, 'false') == 'true') {
                // Check if user has VPN bypass permission
                $hasVpnBypassPermission = false;
                if ($session !== null) {
                    $hasVpnBypassPermission = $session->hasPermission(\MythicalDash\Permissions::USER_PERMISSION_BYPASS_VPN);
                }

                if (ProxyCheck::hasProxy($context) && !$hasVpnBypassPermission) {
                    $app->BadRequest('You are using a vpn or a proxy!', ['error_code' => 'PROXY_DETECTED']);
                } elseif ($hasVpnBypassPermission) {
                    // Log that user has VPN bypass permission
                    $app->getLogger()->info(
                        sprintf(
                            'User has VPN bypass permission - allowing VPN/proxy connection from %s',
                            $context
                        )
                    );
                }
            }
        }
    }
}
