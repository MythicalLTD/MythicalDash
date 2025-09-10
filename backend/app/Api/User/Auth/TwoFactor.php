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
use PragmaRX\Google2FA\Google2FA;
use MythicalDash\Chat\User\Session;
use MythicalDash\Middleware\Firewall;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->get('/api/user/auth/2fa/setup', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);

    $appInstance->allowOnlyGET();
    $google2fa = new Google2FA();
    $session = new Session($appInstance);

    $secret = $google2fa->generateSecretKey();
    $session->setInfo(UserColumns::TWO_FA_KEY, $secret, true);
    $appInstance->OK('Successfully generated secret key', ['secret' => $secret]);
});

$router->post('/api/user/auth/2fa/setup', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $appInstance->allowOnlyPOST();
    global $eventManager;
    global $router;
    /**
     * Process the turnstile response.
     *
     * IF the turnstile is enabled
     */
    if ($appInstance->getConfig()->getDBSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuth2FAVerifyFailed(), ['error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }
        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, CloudFlareRealIP::getRealIP(), $config->getDBSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuth2FAVerifyFailed(), ['error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }

    Firewall::handle($appInstance, CloudFlareRealIP::getRealIP());

    $google2fa = new Google2FA();

    if (!isset($_POST['code'])) {
        $appInstance->getLogger()->debug('Code missing');
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_CODE']);
    }

    $secret = User::getInfo($_COOKIE['user_token'], UserColumns::TWO_FA_KEY, true);
    $code = $_POST['code'];

    if ($google2fa->verifyKey($secret, $code, null, null, null)) {
        User::updateInfo($_COOKIE['user_token'], UserColumns::TWO_FA_ENABLED, 'true', false);
        User::updateInfo($_COOKIE['user_token'], UserColumns::TWO_FA_BLOCKED, 'false', false);

        $eventManager->emit(AuthEvent::onAuth2FAVerifySuccess(), ['secret' => $secret]);
        UserActivities::add(
            User::getInfo($_COOKIE['user_token'], UserColumns::UUID, false),
            UserActivitiesTypes::$two_factor_verify,
            CloudFlareRealIP::getRealIP()
        );
        $appInstance->OK('Code valid go on!', ['secret' => $secret]);
    } else {
        $eventManager->emit(AuthEvent::onAuth2FAVerifyFailed(), ['error_code' => 'INVALID_CODE']);
        $appInstance->Unauthorized('Code invalid', ['error_code' => 'INVALID_CODE']);
    }
});

$router->get('/api/auth/2fa/setup/kill', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();

    $session = new Session($appInstance);

    $session->setInfo(UserColumns::TWO_FA_ENABLED, 'false', false);
    $session->setInfo(UserColumns::TWO_FA_KEY, '', false);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$two_factor_disable,
        CloudFlareRealIP::getRealIP()
    );

    header('location: /?href=api');

    exit;
});
