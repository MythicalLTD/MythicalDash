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

use MythicalDash\App;
use MythicalDash\Chat\User\User;
use MythicalDash\Middleware\Firewall;
use MythicalDash\Chat\User\Verification;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Chat\columns\EmailVerificationColumns;

$router->get('/api/user/auth/verify', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $appInstance->allowOnlyGET();

    if (isset($_GET['code']) && $_GET['code'] != '') {
        $code = $_GET['code'];
        global $router;
        Firewall::handle($appInstance, CloudFlareRealIP::getRealIP());
        if (Verification::verify($code, EmailVerificationColumns::$type_verify)) {
            if (User::exists(UserColumns::UUID, Verification::getUserUUID($code))) {
                $token = User::getInfo(User::getTokenFromUUID(Verification::getUserUUID($code)), UserColumns::ACCOUNT_TOKEN, false);
                if ($token != null && $token != '') {
                    setcookie('user_token', $token, time() + 3600, '/');
                    User::updateInfo(User::getTokenFromUUID(Verification::getUserUUID($code)), UserColumns::VERIFIED, 'true', false);
                    Verification::delete($code);
                    UserActivities::add(
                        Verification::getUserUUID($code),
                        UserActivitiesTypes::$verify,
                        CloudFlareRealIP::getRealIP()
                    );
                    exit(header('location: /'));
                }
                $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_USER', 'email_code' => $code]);

            } else {
                $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_USER', 'email_code' => $code]);
            }
        } else {
            $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_CODE', 'email_code' => $code]);
        }
    } else {
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_CODE']);
    }
});
