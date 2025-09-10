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
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Chat\columns\EmailVerificationColumns;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->get('/api/user/auth/reset', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $router;
    $appInstance->allowOnlyGET();

    if (isset($_GET['code']) && $_GET['code'] != '') {
        $code = $_GET['code'];

        if (Verification::verify($code, EmailVerificationColumns::$type_password)) {
            $eventManager->emit(AuthEvent::onAuthResetPasswordSuccess(), ['code' => $code]);
            $appInstance->OK('Code is valid', ['reset_code' => $code]);
        } else {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => $code]);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_CODE']);
        }
    } else {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_CODE']);
    }
});

$router->post('/api/user/auth/reset', function (): void {
    App::init();
    global $eventManager;
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $router;
    $appInstance->allowOnlyPOST();

    if (!isset($_POST['email_code']) || $_POST['email_code'] == '') {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_CODE']);
    }

    if (!isset($_POST['password']) || $_POST['password'] == '') {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PASSWORD']);
    }

    if (!isset($_POST['confirmPassword']) || $_POST['confirmPassword'] == '') {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PASSWORD_CONFIRM']);
    }

    if ($_POST['password'] != $_POST['confirmPassword']) {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'PASSWORDS_DO_NOT_MATCH']);
    }

    $code = $_POST['email_code'];
    $password = $_POST['password'];

    Firewall::handle($appInstance, CloudFlareRealIP::getRealIP());

    /**
     * Process the turnstile response.
     *
     * IF the turnstile is enabled
     */
    if ($appInstance->getConfig()->getDBSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }
        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, CloudFlareRealIP::getRealIP(), $config->getDBSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }

    if (Verification::verify($code, EmailVerificationColumns::$type_password)) {
        $uuid = Verification::getUserUUID($code);
        if ($uuid == null || $uuid == '') {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_CODE']);
        }
        $userToken = User::getTokenFromUUID($uuid);
        if ($userToken == null || $userToken == '') {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_CODE']);
        }
        try {
            $userInfoArray = User::getInfoArray($userToken, [
                UserColumns::PTERODACTYL_USER_ID,
                UserColumns::VERIFIED,
                UserColumns::BANNED,
                UserColumns::DELETED,
                UserColumns::USERNAME,
                UserColumns::TWO_FA_ENABLED,
                UserColumns::TWO_FA_BLOCKED,
                UserColumns::EMAIL,
                UserColumns::PASSWORD,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::UUID,
            ], [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::PASSWORD,
            ]);
        } catch (Exception $e) {
            $appInstance->getLogger()->error('Failed to get user info: ' . $e->getMessage());
            $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'DATABASE_ERROR']);
        }

        $appInstance->getLogger()->debug('User info array: ' . json_encode($userInfoArray));

        if (User::updateInfo($userToken, UserColumns::PASSWORD, $password, true) == true) {
            Verification::delete($code);
            $token = App::getInstance(true)->encrypt(date('Y-m-d H:i:s') . $uuid . random_bytes(16) . base64_encode($code));
            User::updateInfo($userToken, UserColumns::ACCOUNT_TOKEN, $token, true);
            try {
                MythicalDash\Hooks\Pterodactyl\Admin\User::performLogin(
                    $userInfoArray[UserColumns::PTERODACTYL_USER_ID],
                    $userInfoArray[UserColumns::EMAIL],
                    $userInfoArray[UserColumns::USERNAME],
                    $userInfoArray[UserColumns::FIRST_NAME],
                    $userInfoArray[UserColumns::LAST_NAME],
                    $userInfoArray[UserColumns::PASSWORD]
                );
                UserActivities::add(
                    $userInfoArray[UserColumns::UUID],
                    UserActivitiesTypes::$change_password,
                    CloudFlareRealIP::getRealIP()
                );
            } catch (Exception $e) {
                $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:1] Failed to login user in Pterodactyl: ' . $e->getMessage());
                $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'PTERODACTYL_ERROR']);
            }
            $eventManager->emit(AuthEvent::onAuthResetPasswordSuccess(), ['code' => $code]);
            $appInstance->OK('Password has been reset', []);
        } else {
            $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
            $appInstance->BadRequest('Failed to reset password', ['error_code' => 'FAILED_TO_RESET_PASSWORD']);
        }
    } else {
        $eventManager->emit(AuthEvent::onAuthResetPasswordFailed(), ['code' => 'UNKNOWN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_CODE']);
    }
});
