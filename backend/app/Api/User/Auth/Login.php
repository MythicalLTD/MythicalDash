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

namespace MythicalDash\Api\User\Auth;

use MythicalDash\App;
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Middleware\Firewall;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\PermissionUtils;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Chat\IPRelationships\IPRelationship;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\Turnstile;

$router->add('/api/user/auth/login', function (): void {
    global $eventManager;
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $router;

    $appInstance->allowOnlyPOST();

    // Check login field
    if (!isset($_POST['login']) || $_POST['login'] == '') {
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => 'UNKNOWN', 'error_code' => 'MISSING_LOGIN']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_LOGIN']);
    }

    // Check password field
    if (!isset($_POST['password']) || $_POST['password'] == '') {
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $_POST['login'], 'error_code' => 'MISSING_PASSWORD']);
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PASSWORD']);
    }

    // Process turnstile if enabled
    if ($appInstance->getConfig()->getDBSetting(ConfigInterface::TURNSTILE_ENABLED, 'false') == 'true') {
        if (!isset($_POST['turnstileResponse']) || $_POST['turnstileResponse'] == '') {
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $_POST['login'], 'error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Bad Request', ['error_code' => 'TURNSTILE_FAILED']);
        }

        $cfTurnstileResponse = $_POST['turnstileResponse'];
        if (!Turnstile::validate($cfTurnstileResponse, CloudFlareRealIP::getRealIP(), $config->getDBSetting(ConfigInterface::TURNSTILE_KEY_PRIV, 'XXXX'))) {
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $_POST['login'], 'error_code' => 'TURNSTILE_FAILED']);
            $appInstance->BadRequest('Invalid TurnStile Key', ['error_code' => 'TURNSTILE_FAILED']);
        }
    }

    $login = $_POST['login'];
    $password = $_POST['password'];

    Firewall::handle($appInstance, CloudFlareRealIP::getRealIP());

    $loginResult = User::login($login, $password);
    if ($loginResult == 'false') {
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'INVALID_CREDENTIALS']);
        $appInstance->BadRequest('Invalid login credentials', ['error_code' => 'INVALID_CREDENTIALS']);
    }

    if ($config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, '') == '') {
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'PTERODACTYL_NOT_ENABLED']);
        $appInstance->BadRequest('Pterodactyl is not enabled', ['error_code' => 'PTERODACTYL_NOT_ENABLED']);
    }

    try {
        $userInfoArray = User::getInfoArray($loginResult, [
            UserColumns::PTERODACTYL_USER_ID,
            UserColumns::VERIFIED,
            UserColumns::BANNED,
            UserColumns::DELETED,
            UserColumns::USERNAME,
            UserColumns::TWO_FA_ENABLED,
            UserColumns::TWO_FA_BLOCKED,
            UserColumns::EMAIL,
            UserColumns::PASSWORD,
            UserColumns::UUID,
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::CREDITS,
            UserColumns::DISCORD_ID,
            UserColumns::GITHUB_ID,
            UserColumns::AVATAR,
            UserColumns::IMAGE_HOSTING_UPLOAD_KEY,
        ], [
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::PASSWORD,
        ]);

        // Validate critical user data - if any critical field is null, block login
        $criticalFields = [
            UserColumns::USERNAME => 'username',
            UserColumns::EMAIL => 'email',
            UserColumns::UUID => 'UUID',
            UserColumns::PTERODACTYL_USER_ID => 'Pterodactyl user ID',
        ];

        foreach ($criticalFields as $field => $fieldName) {
            if (!isset($userInfoArray[$field]) || $userInfoArray[$field] === null || $userInfoArray[$field] === '') {
                $appInstance->getLogger()->error("Critical user data missing: {$fieldName} for user {$login}");
                $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'INVALID_USER_DATA']);
                $appInstance->BadRequest('Invalid user data', ['error_code' => 'INVALID_USER_DATA']);
            }
        }

    } catch (\Exception $e) {
        $appInstance->getLogger()->error('Failed to get user info: ' . $e->getMessage());
        $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'DATABASE_ERROR']);
    }

    if ($userInfoArray[UserColumns::PTERODACTYL_USER_ID] == 0) {
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'PTERODACTYL_USER_NOT_FOUND']);
        $appInstance->BadRequest('Pterodactyl user not found', ['error_code' => 'PTERODACTYL_USER_NOT_FOUND']);
    }

    // Check account verification if mail is enabled
    if (($userInfoArray[UserColumns::VERIFIED] ?? 'false') == 'false' && Mail::isEnabled()) {
        User::logout();
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'ACCOUNT_NOT_VERIFIED']);
        $appInstance->BadRequest('Account not verified', ['error_code' => 'ACCOUNT_NOT_VERIFIED']);
    }

    $appInstance->getLogger()->debug('User info array: ' . json_encode($userInfoArray));

    // Check if account is banned
    if (($userInfoArray[UserColumns::BANNED] ?? 'NO') !== 'NO') {
        User::logout();
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'ACCOUNT_BANNED']);
        $appInstance->BadRequest('Account is banned', ['error_code' => 'ACCOUNT_BANNED']);
    }

    // Check if account is deleted
    if (($userInfoArray[UserColumns::DELETED] ?? 'false') == 'true') {
        User::logout();
        $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $login, 'error_code' => 'ACCOUNT_DELETED']);
        $appInstance->BadRequest('Account is deleted', ['error_code' => 'ACCOUNT_DELETED']);
    }

    // Handle 2FA if enabled
    if (($userInfoArray[UserColumns::TWO_FA_ENABLED] ?? 'false') == 'true') {
        User::updateInfo($login, UserColumns::TWO_FA_BLOCKED, 'true', false);
    }

    // Set cookie based on debug mode
    if (APP_DEBUG) {
        // Set the cookie to expire in 1 year if the app is in debug mode
        setcookie('user_token', $loginResult, time() + 3600 * 31 * 3600, '/');
    } else {
        setcookie('user_token', $loginResult, time() + 3600, '/');
    }
    /**
     * Login user in Pterodactyl.
     */
    try {
        \MythicalDash\Hooks\Pterodactyl\Admin\User::performLogin(
            $userInfoArray[UserColumns::PTERODACTYL_USER_ID],
            $userInfoArray[UserColumns::EMAIL],
            $userInfoArray[UserColumns::USERNAME],
            $userInfoArray[UserColumns::FIRST_NAME] ?? '',
            $userInfoArray[UserColumns::LAST_NAME] ?? '',
            $userInfoArray[UserColumns::PASSWORD] ?? '',
        );
    } catch (\Exception $e) {
        $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:1] Failed to login user in Pterodactyl: ' . $e->getMessage());
        $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'PTERODACTYL_ERROR']);
    }
    /**
     * Import servers from Pterodactyl to MythicalDash.
     */
    try {
        $pterodactylServers = Servers::getUserServersList($userInfoArray[UserColumns::PTERODACTYL_USER_ID]);

        foreach ($pterodactylServers as $pterodactylServer) {
            if (!Server::doesServerExistByPterodactylId($pterodactylServer['id'])) {
                Server::create($pterodactylServer['id'], null, $userInfoArray[UserColumns::UUID]);
            }
        }
    } catch (\Exception $e) {
        $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:1] Failed to create servers in MythicalDash: ' . $e->getMessage());
        $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'PTERODACTYL_ERROR']);
    }
    $userUuid = $userInfoArray[UserColumns::UUID];
    $currentIP = CloudFlareRealIP::getRealIP();

    // Check if user has alt bypass permission
    $hasAltBypassPermission = PermissionUtils::userHasPermission($loginResult, \MythicalDash\Permissions::USER_PERMISSION_BYPASS_ALTING);

    if ($config->getDBSetting(ConfigInterface::FIREWALL_BLOCK_ALTS, 'false') == 'true' && !$hasAltBypassPermission) {
        $processedUsers = []; // Initialize the array

        // Create the IP relationship
        IPRelationship::create($userUuid, $currentIP);

        // Process multiple accounts
        $multipleAccounts = IPRelationship::processMultipleAccounts($userUuid);

        if ($multipleAccounts['has_multiple_accounts']) {
            // Log the warning
            $appInstance->getLogger()->warning(
                sprintf(
                    'User %s logged in from %s. Found %d shared accounts and %d shared IPs (Alt bypass permission: %s)',
                    $userUuid,
                    $currentIP,
                    count($multipleAccounts['shared_users']),
                    count($multipleAccounts['shared_ips']),
                    $hasAltBypassPermission ? 'true' : 'false'
                )
            );

            // Ban the current user first
            try {
                User::updateInfo($loginResult, UserColumns::BANNED, 'User banned for multiple accounts on ' . $currentIP, false);
                $appInstance->getLogger()->info(
                    sprintf(
                        'Banned current user %s (UUID: %s) for having multiple accounts',
                        $userInfoArray[UserColumns::USERNAME],
                        $userUuid
                    )
                );
                $processedUsers[] = [
                    'uuid' => $userUuid,
                    'username' => $userInfoArray[UserColumns::USERNAME],
                    'avatar' => $userInfoArray[UserColumns::AVATAR],
                ];
            } catch (\Exception $e) {
                $appInstance->getLogger()->error(
                    sprintf(
                        'Failed to ban current user %s: %s',
                        $userUuid,
                        $e->getMessage()
                    )
                );
            }

            // You can now process each relationship
            foreach ($multipleAccounts['relationships'] as $relationship) {
                // Get the user info for this relationship
                try {
                    $token = User::getTokenFromUUID($relationship['user']);
                    $relatedUserInfo = User::getInfoArray($token, [
                        UserColumns::USERNAME,
                        UserColumns::AVATAR,
                    ], [UserColumns::PASSWORD]);

                    // Ban the related user account
                    User::updateInfo($token, UserColumns::BANNED, 'User banned for multiple accounts on ' . $currentIP, false);
                    $appInstance->getLogger()->info(
                        sprintf(
                            'Banned user %s (UUID: %s) for having multiple accounts',
                            $relatedUserInfo[UserColumns::USERNAME],
                            $relationship['user']
                        )
                    );
                    $processedUsers[] = [
                        'uuid' => $relationship['user'],
                        'username' => $relatedUserInfo[UserColumns::USERNAME],
                        'avatar' => $relatedUserInfo[UserColumns::AVATAR],
                    ];
                } catch (\Exception $e) {
                    $appInstance->getLogger()->error(
                        sprintf(
                            'Failed to ban user %s: %s',
                            $relationship['user'],
                            $e->getMessage()
                        )
                    );
                }
            }
        }
        // Check if any users were actually banned in this process
        if (!empty($processedUsers)) {
            $appInstance->BadRequest('Multiple accounts detected and banned', ['error_code' => 'MULTIPLE_ACCOUNTS', 'info' => $processedUsers]);
        }
    } elseif ($hasAltBypassPermission) {
        // Log that user has alt bypass permission
        $appInstance->getLogger()->info(
            sprintf(
                'User %s (UUID: %s) has alt bypass permission - skipping alt detection',
                $userInfoArray[UserColumns::USERNAME],
                $userUuid
            )
        );
    }
    $login = $userInfoArray[UserColumns::EMAIL];

    // Optimize image hosting API key generation
    if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, 'false') === 'true') {
        $api_key = $userInfoArray[UserColumns::IMAGE_HOSTING_UPLOAD_KEY] ?? '';

        if (empty($api_key)) {
            $api_key = UUIDManager::generateUUID();
            User::updateInfo($loginResult, UserColumns::IMAGE_HOSTING_UPLOAD_KEY, $api_key, false);
            $appInstance->getLogger()->debug('Generated new image hosting API key for user: ' . $userInfoArray[UserColumns::USERNAME]);
        }
    }

    $eventManager->emit(AuthEvent::onAuthLoginSuccess(), ['login' => $login]);
    $appInstance->OK('Successfully logged in', []);
});
