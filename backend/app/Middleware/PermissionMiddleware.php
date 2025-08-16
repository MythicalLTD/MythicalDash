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

namespace MythicalDash\Middleware;

use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;

class PermissionMiddleware implements MiddlewareBuilder
{
    public static function handle(App $app, string $context, ?Session $session = null): void
    {
        if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
            if ($session !== null && $session->hasPermission($context)) {
                return;
            }
            if ($app->getConfig()->getDBSetting(ConfigInterface::FORCE_2FA, 'false') === 'true') {
                if ($session->getInfo(UserColumns::TWO_FA_ENABLED, false) === 'false') {
                    $app->BadRequest('Two-factor authentication is required for this action. Please enable 2FA in your account settings.', ['error_code' => '2FA_REQUIRED']);
                }
            }
            $app->BadRequest('You are not authorized to perform this action!', ['error_code' => 'NOT_AUTHORIZED']);
        }

    }
}
