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

class PermissionMiddleware implements MiddlewareBuilder
{
    public static function handle(App $app, string $context, ?Session $session = null): void
    {
        if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
            if ($session !== null && $session->hasPermission($context)) {
                return;
            }
            $app->BadRequest('You are not authorized to perform this action!', ['error_code' => 'NOT_AUTHORIZED']);
        }

    }
}
