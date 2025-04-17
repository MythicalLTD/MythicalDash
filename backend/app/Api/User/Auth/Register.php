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
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Plugins\Events\Events\ReferralsEvent;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->add('/api/user/auth/register', function (): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $appInstance->allowOnlyPOST();
    /**
     * Check if the required fields are set.
     *
     * @var string
     */
    if (!isset($_POST['firstName']) || $_POST['firstName'] == '') {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['firstName' => 'UNKNOWN', 'error_code' => 'MISSING_FIRST_NAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_FIRST_NAME']);
    }
    if (!isset($_POST['lastName']) || $_POST['lastName'] == '') {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['lastName' => 'UNKNOWN', 'error_code' => 'MISSING_LAST_NAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_LAST_NAME']);
    }
    if (!isset($_POST['email']) || $_POST['email'] == '') {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['email' => 'UNKNOWN', 'error_code' => 'MISSING_EMAIL']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_EMAIL']);
    }
    if (!isset($_POST['password']) || $_POST['password'] == '') {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['password' => 'UNKNOWN', 'error_code' => 'MISSING_PASSWORD']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PASSWORD']);
    }
    if (!isset($_POST['username']) || $_POST['username'] == '') {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['username' => 'UNKNOWN', 'error_code' => 'MISSING_USERNAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_USERNAME']);
    }

    // Add validation for first name (only letters)
    if (!preg_match('/^[a-zA-Z]+$/', $_POST['firstName'])) {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['firstName' => $_POST['firstName'], 'error_code' => 'INVALID_FIRST_NAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_FIRST_NAME']);
    }

    // Add validation for last name (only letters)
    if (!preg_match('/^[a-zA-Z]+$/', $_POST['lastName'])) {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['lastName' => $_POST['lastName'], 'error_code' => 'INVALID_LAST_NAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_LAST_NAME']);
    }

    // Add validation for username (alphanumeric, no spaces or special chars)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['username'])) {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['username' => $_POST['username'], 'error_code' => 'INVALID_USERNAME']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_USERNAME']);
    }

    // Add validation for email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['email' => $_POST['email'], 'error_code' => 'INVALID_EMAIL']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'INVALID_EMAIL']);
    }

    /**
     * Process the turnstile response.
     *
     * IF the turnstile is enabled
     */
    if ($appInstance->getConfig()->getSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'MISSING_TURNSTILE_RESPONSE']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }
        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, CloudFlareRealIP::getRealIP(), $config->getSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $username = $_POST['username'];

    /**
     * Check if the email is already in use.
     *
     * @var bool
     */
    try {
        if ($config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, '') == '') {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'PTERODACTYL_NOT_ENABLED']);
            $appInstance->BadRequest('Pterodactyl is not enabled', ['error_code' => 'PTERODACTYL_NOT_ENABLED']);
        }

        if (User::exists(UserColumns::USERNAME, $username)) {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'USERNAME_ALREADY_IN_USE']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'USERNAME_ALREADY_IN_USE']);
        }
        if (User::exists(UserColumns::EMAIL, $email)) {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'EMAIL_ALREADY_IN_USE']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'EMAIL_ALREADY_IN_USE']);
        }

        try {
            $pterodactylUserId = MythicalDash\Hooks\Pterodactyl\Admin\User::performRegister($firstName, $lastName, $username, $email, $password);
            if ($pterodactylUserId == 0 && $pterodactylUserId != null) {
                $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'PTERODACTYL_ERROR']);
                $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'PTERODACTYL_ERROR']);
            }

            MythicalDash\Hooks\Pterodactyl\Admin\User::performUpdateUser($pterodactylUserId, $username, $firstName, $lastName, $email, $password);
        } catch (Exception $e) {
            $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'PTERODACTYL_ERROR']);
            $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'PTERODACTYL_ERROR']);
        }

        User::register($username, $password, $email, $firstName, $lastName, CloudFlareRealIP::getRealIP(), $pterodactylUserId);
        $newUserUuid = User::convertEmailToUUID($email);
        $newUserToken = User::getTokenFromEmail($email);
        if ($config->getSetting(ConfigInterface::REFERRALS_ENABLED, false)) {
            if ($newUserUuid) {
                // Generate a referral code
                $referralCode = $username . '_' . $appInstance->generatePin();
                ReferralCodes::create($newUserUuid, $referralCode);
                $eventManager->emit(ReferralsEvent::onReferralCreated(), [
                    'user' => $newUserUuid,
                    'referral_code' => $referralCode,
                ]);

                if (isset($_GET['ref']) && $_GET['ref'] != '') {
                    $referrerCode = ReferralCodes::getByCode($_GET['ref']);

                    $referrerUuid = $referrerCode['user'];
                    $referrerToken = User::getTokenFromUUID($referrerUuid);

                    if ($referrerCode) {
                        ReferralUses::create($referrerCode['id'], $newUserUuid);
                        $eventManager->emit(ReferralsEvent::onReferralRedeemed(), [
                            'user' => $referrerUuid,
                            'referral_code' => $_GET['ref'],
                        ]);
                        $newUserBonus = intval($appInstance->getConfig()->getSetting(ConfigInterface::REFERRALS_COINS_PER_REFERRAL_REDEEMER, 15));
                        User::updateInfo($newUserToken, UserColumns::CREDITS, $newUserBonus, false);

                        $referrerBonus = intval($appInstance->getConfig()->getSetting(ConfigInterface::REFERRALS_COINS_PER_REFERRAL, 35)) + intval(User::getInfo($referrerToken, UserColumns::CREDITS, false));
                        User::updateInfo($referrerToken, UserColumns::CREDITS, $referrerBonus, false);
                    }
                }
            }
        }

        /**
         * Default Resources.
         */
        $defaultRam = $config->getSetting(ConfigInterface::DEFAULT_RAM, 1024);
        $defaultDisk = $config->getSetting(ConfigInterface::DEFAULT_DISK, 1024);
        $defaultCpu = $config->getSetting(ConfigInterface::DEFAULT_CPU, 100);
        $defaultPorts = $config->getSetting(ConfigInterface::DEFAULT_PORTS, 2);
        $defaultDatabases = $config->getSetting(ConfigInterface::DEFAULT_DATABASES, 1);
        $defaultServerSlots = $config->getSetting(ConfigInterface::DEFAULT_SERVER_SLOTS, 1);
        $defaultBackups = $config->getSetting(ConfigInterface::DEFAULT_BACKUPS, 5);

        User::updateInfo($newUserToken, UserColumns::MEMORY_LIMIT, $defaultRam, false);
        User::updateInfo($newUserToken, UserColumns::DISK_LIMIT, $defaultDisk, false);
        User::updateInfo($newUserToken, UserColumns::CPU_LIMIT, $defaultCpu, false);
        User::updateInfo($newUserToken, UserColumns::ALLOCATION_LIMIT, $defaultPorts, false);
        User::updateInfo($newUserToken, UserColumns::DATABASE_LIMIT, $defaultDatabases, false);
        User::updateInfo($newUserToken, UserColumns::SERVER_LIMIT, $defaultServerSlots, false);
        User::updateInfo($newUserToken, UserColumns::BACKUP_LIMIT, $defaultBackups, false);

        $eventManager->emit(AuthEvent::onAuthRegisterSuccess(), ['username' => $username, 'email' => $email]);

        /**
         * Zero Trust.
         */
        $appInstance->getTelemetry()->sendRegister($username, $firstName, $lastName, $email, CloudFlareRealIP::getRealIP());
        App::OK('User registered', []);

    } catch (Exception $e) {
        $eventManager->emit(AuthEvent::onAuthRegisterFailed(), ['error_code' => 'DATABASE_ERROR']);
        $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'DATABASE_ERROR']);
    }

});
