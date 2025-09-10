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
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->add('/api/user/auth/forgot', function (): void {
    global $eventManager;
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $router;
    $appInstance->allowOnlyPOST();
    /**
     * Check if the required fields are set.
     *
     * @var string
     */
    if (!isset($_POST['email']) || $_POST['email'] == '') {
        $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => 'UNKNOWN', 'error_code' => 'MISSING_EMAIL']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_EMAIL']);
    }

    /**
     * Process the turnstile response.
     *
     * IF the turnstile is enabled
     */
    if ($appInstance->getConfig()->getDBSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'MISSING_TURNSTILE_RESPONSE']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }
        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, CloudFlareRealIP::getRealIP(), $config->getDBSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }
    $email = $_POST['email'];

    Firewall::handle($appInstance, CloudFlareRealIP::getRealIP());
    if (User::exists(UserColumns::EMAIL, $email)) {

        if (User::forgotPassword($email)) {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordSuccess(), ['email' => $_POST['email']]);
            $appInstance->OK('Successfully sent email', []);
        } else {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'FAILED_TO_SEND_EMAIL']);
            $appInstance->BadRequest('Failed to send email', ['error_code' => 'FAILED_TO_SEND_EMAIL']);
        }

    } else {
        $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'EMAIL_DOES_NOT_EXIST']);
        $appInstance->BadRequest('Email does not exist', ['error_code' => 'EMAIL_DOES_NOT_EXIST']);
    }

});
