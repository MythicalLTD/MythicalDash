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
                    header('location: /');
                    exit;
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
