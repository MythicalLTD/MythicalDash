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
