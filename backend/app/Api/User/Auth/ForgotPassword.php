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

use MythicalDash\App;
use MythicalDash\Chat\User\User;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->add('/api/user/auth/forgot', function (): void {
    global $eventManager;
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

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
    if ($appInstance->getConfig()->getSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'MISSING_TURNSTILE_RESPONSE']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }
        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, MythicalDash\CloudFlare\CloudFlareRealIP::getRealIP(), $config->getSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuthForgotPasswordFailed(), ['email' => $_POST['email'], 'error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }
    $email = $_POST['email'];

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
