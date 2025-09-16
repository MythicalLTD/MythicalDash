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
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Middleware\Firewall;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Hooks\DiscordOAuthHelper;
use MythicalDash\Chat\User\PermissionUtils;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DiscordEvent;
use MythicalDash\Chat\IPRelationships\IPRelationship;
use MythicalDash\Hooks\MythicalSystems\User\UUIDManager;

// Discord Link Callback
$router->get('/api/user/auth/callback/discord/link', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);

    if (!$helper->validateConfig()) {
        header('Location: /auth/login?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/link';
    $session = new Session($appInstance);
    global $eventManager;

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_failed');
            exit;
        }

        $isLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
        if ($isLinked === 'true') {
            header('Location: ' . $helper->getSecureBaseUrl() . '/account?error=discord_already_linked');
            exit;
        }

        $helper->storeDiscordData($session, $userInfo);
        $helper->forceJoinServer($userInfo['id'], $accessToken);
        $helper->storeUserGuilds($session, $accessToken);

        $eventManager->emit(DiscordEvent::onDiscordLink(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
        ]);

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$discord_link,
            CloudFlareRealIP::getRealIP(),
            "Linked Discord account: {$userInfo['id']}"
        );

        header('Location: ' . $helper->getSecureBaseUrl() . '/');
        exit;
    }

    header('Location: ' . $helper->getAuthUrl($redirectUri));
});

// Discord Unlink Callback
$router->get('/api/user/auth/callback/discord/unlink', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);
    $session = new Session($appInstance);
    global $eventManager;

    $isLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
    if ($isLinked !== 'true') {
        header('Location: /account?error=discord_not_linked');
        exit;
    }

    $helper->clearDiscordData($session);

    $eventManager->emit(DiscordEvent::onDiscordUnlink(), [
        'user' => $session->getInfo(UserColumns::UUID, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$discord_unlink,
        CloudFlareRealIP::getRealIP(),
        'Unlinked Discord account'
    );

    header('Location: /account');
    exit;
});

// Discord Login Callback
$router->get('/api/user/auth/callback/discord/login', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);
    $config = $appInstance->getConfig();

    if (!$helper->validateConfig()) {
        header('Location: /auth/login?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/login';
    global $eventManager;

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_failed');
            exit;
        }

        if (!User::exists(UserColumns::DISCORD_ID, $userInfo['id'])) {
            $appInstance->getLogger()->error('Discord login failed for user: ' . $userInfo['id']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_not_found');
            exit;
        }

        $uuid = User::getUUIDFromDiscordID($userInfo['id']);
        $userToken = User::getTokenFromUUID($uuid);
        $email = User::getInfo($userToken, UserColumns::EMAIL, false);
        $password = User::getInfo($userToken, UserColumns::PASSWORD, true);

        if (!$email || !$password) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_data_missing');
            exit;
        }

        // --- COMPLETE LOGIN LOGIC STARTS HERE ---
        $loginResult = User::login($email, $password);
        if ($loginResult == 'false') {
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'INVALID_CREDENTIALS_DISCORD']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_login_failed');
            exit;
        }

        // Check if Pterodactyl is enabled
        if ($config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, '') == '') {
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'PTERODACTYL_NOT_ENABLED']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=pterodactyl_not_enabled');
            exit;
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
                    $appInstance->getLogger()->error("Critical user data missing: {$fieldName} for Discord user {$email}");
                    $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'INVALID_USER_DATA']);
                    header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=invalid_user_data');
                    exit;
                }
            }

        } catch (Exception $e) {
            $appInstance->getLogger()->error('Failed to get user info for Discord login: ' . $e->getMessage());
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=database_error');
            exit;
        }

        if ($userInfoArray[UserColumns::PTERODACTYL_USER_ID] == 0) {
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'PTERODACTYL_USER_NOT_FOUND']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=pterodactyl_user_not_found');
            exit;
        }

        // Check account verification if mail is enabled
        if (($userInfoArray[UserColumns::VERIFIED] ?? 'false') == 'false' && Mail::isEnabled()) {
            User::logout();
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'ACCOUNT_NOT_VERIFIED']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=account_not_verified');
            exit;
        }

        // Check if account is banned
        if (($userInfoArray[UserColumns::BANNED] ?? 'NO') !== 'NO') {
            User::logout();
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'ACCOUNT_BANNED']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=account_banned');
            exit;
        }

        // Check if account is deleted
        if (($userInfoArray[UserColumns::DELETED] ?? 'false') == 'true') {
            User::logout();
            $eventManager->emit(AuthEvent::onAuthLoginFailed(), ['login' => $email, 'error_code' => 'ACCOUNT_DELETED']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=account_deleted');
            exit;
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
            MythicalDash\Hooks\Pterodactyl\Admin\User::performLogin(
                $userInfoArray[UserColumns::PTERODACTYL_USER_ID],
                $userInfoArray[UserColumns::EMAIL],
                $userInfoArray[UserColumns::USERNAME],
                $userInfoArray[UserColumns::FIRST_NAME] ?? '',
                $userInfoArray[UserColumns::LAST_NAME] ?? '',
                $userInfoArray[UserColumns::PASSWORD] ?? '',
            );
        } catch (Exception $e) {
            $appInstance->getLogger()->error('[Discord Login] Failed to login user in Pterodactyl: ' . $e->getMessage());
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=pterodactyl_error');
            exit;
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
        } catch (Exception $e) {
            $appInstance->getLogger()->error('[Discord Login] Failed to create servers in MythicalDash: ' . $e->getMessage());
            // Don't exit here, just log the error as server import is not critical for login
        }

        $userUuid = $userInfoArray[UserColumns::UUID];
        $currentIP = CloudFlareRealIP::getRealIP();

        // Handle firewall
        Firewall::handle($appInstance, $currentIP);

        // Check if user has alt bypass permission
        $hasAltBypassPermission = PermissionUtils::userHasPermission($loginResult, MythicalDash\Permissions::USER_PERMISSION_BYPASS_ALTING);

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
                        '[Discord Login] User %s logged in from %s. Found %d shared accounts and %d shared IPs (Alt bypass permission: %s)',
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
                            '[Discord Login] Banned current user %s (UUID: %s) for having multiple accounts',
                            $userInfoArray[UserColumns::USERNAME],
                            $userUuid
                        )
                    );
                    $processedUsers[] = [
                        'uuid' => $userUuid,
                        'username' => $userInfoArray[UserColumns::USERNAME],
                        'avatar' => $userInfoArray[UserColumns::AVATAR],
                    ];
                } catch (Exception $e) {
                    $appInstance->getLogger()->error(
                        sprintf(
                            '[Discord Login] Failed to ban current user %s: %s',
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
                                '[Discord Login] Banned user %s (UUID: %s) for having multiple accounts',
                                $relatedUserInfo[UserColumns::USERNAME],
                                $relationship['user']
                            )
                        );
                        $processedUsers[] = [
                            'uuid' => $relationship['user'],
                            'username' => $relatedUserInfo[UserColumns::USERNAME],
                            'avatar' => $relatedUserInfo[UserColumns::AVATAR],
                        ];
                    } catch (Exception $e) {
                        $appInstance->getLogger()->error(
                            sprintf(
                                '[Discord Login] Failed to ban user %s: %s',
                                $relationship['user'],
                                $e->getMessage()
                            )
                        );
                    }
                }
            }
            // Check if any users were actually banned in this process
            if (!empty($processedUsers)) {
                header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=multiple_accounts');
                exit;
            }
        } elseif ($hasAltBypassPermission) {
            // Log that user has alt bypass permission
            $appInstance->getLogger()->info(
                sprintf(
                    '[Discord Login] User %s (UUID: %s) has alt bypass permission - skipping alt detection',
                    $userInfoArray[UserColumns::USERNAME],
                    $userUuid
                )
            );
        }

        // Optimize image hosting API key generation
        if ($config->getDBSetting(ConfigInterface::IMAGE_HOSTING_ENABLED, 'false') === 'true') {
            $api_key = $userInfoArray[UserColumns::IMAGE_HOSTING_UPLOAD_KEY] ?? '';

            if (empty($api_key)) {
                $api_key = UUIDManager::generateUUID();
                User::updateInfo($loginResult, UserColumns::IMAGE_HOSTING_UPLOAD_KEY, $api_key, false);
                $appInstance->getLogger()->debug('[Discord Login] Generated new image hosting API key for user: ' . $userInfoArray[UserColumns::USERNAME]);
            }
        }

        // Force join server if enabled
        $helper->forceJoinServer($userInfo['id'], $accessToken);

        // Emit events and log activity
        $eventManager->emit(DiscordEvent::onDiscordLogin(), ['user' => $userUuid]);
        $eventManager->emit(AuthEvent::onAuthLoginSuccess(), ['login' => $email]);

        UserActivities::add(
            $userUuid,
            UserActivitiesTypes::$discord_login,
            CloudFlareRealIP::getRealIP(),
            'Logged in with Discord'
        );

        // Redirect to dashboard
        header('Location: ' . $helper->getSecureBaseUrl() . '/');
        exit;
    }

    // Redirect to Discord authorization
    header('Location: ' . $helper->getAuthUrl($redirectUri));
});

// Discord J4R Check Callback
$router->get('/api/user/auth/callback/discord/j4r', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);
    $session = new Session($appInstance);

    if (!$helper->validateConfig()) {
        header('Location: /earn/j4r?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/j4r';

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            header('Location: /earn/j4r?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            header('Location: /earn/j4r?error=discord_user_failed');
            exit;
        }

        $sessionDiscordId = $session->getInfo(UserColumns::DISCORD_ID, false);
        if ($sessionDiscordId !== $userInfo['id']) {
            header('Location: /earn/j4r?error=discord_user_mismatch');
            exit;
        }

        $helper->checkJ4RServerJoins($userInfo['id'], $accessToken, $session);

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$j4r_check,
            CloudFlareRealIP::getRealIP(),
            'Performed J4R check via dedicated endpoint'
        );

        header('Location: /earn/j4r?success=j4r_check_completed');
        exit;
    }

    header('Location: ' . $helper->getAuthUrl($redirectUri));
});
