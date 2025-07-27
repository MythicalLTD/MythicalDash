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
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DiscordEvent;

$router->get('/api/user/auth/callback/discord/link', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $s = new Session($appInstance);
    global $eventManager;
    if (
        $config->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false') === 'false'
        || $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_ID, '') === ''
        || $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_SECRET, '') === ''
    ) {
        header('Location: /account?error=discord_not_enabled');
        exit;
    }

    $appId = $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_ID, '');
    $appSecret = $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_SECRET, '');
    $url = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    $redirectUri = $url . '/api/user/auth/callback/discord/link';

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $tokenUrl = 'https://discord.com/api/oauth2/token';
        $data = [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'scope' => 'identify guilds email guilds.join',
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($tokenUrl, false, $context);

        if ($result === false) {
            $appInstance->getLogger()->error('Failed to make HTTP request to Discord token endpoint');
            header('Location: ' . $url . '/auth/login?error=discord_request_failed');
            exit;
        }

        $tokenData = json_decode($result, true);
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            $appInstance->getLogger()->error('Failed to get access token from Discord: ' . $result);
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;
        }

        $userUrl = 'https://discord.com/api/users/@me';

        $options = [
            'http' => [
                'header' => "Authorization: Bearer $accessToken\r\n",
                'method' => 'GET',
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($userUrl, false, $context);

        if ($result === false) {
            $appInstance->getLogger()->error('Failed to make HTTP request to Discord user endpoint');
            header('Location: ' . $url . '/auth/login?error=discord_request_failed');
            exit;
        }

        $userInfo = json_decode($result, true);
        if (!$userInfo || !isset($userInfo['id'])) {
            $appInstance->getLogger()->error('Failed to get user info from Discord: ' . $result);
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;
        }
        if (isset($userInfo['id']) && isset($userInfo['username']) && isset($userInfo['global_name']) && isset($userInfo['email'])) {
            $id = $userInfo['id'];
            $username = $userInfo['username'];
            $global_name = $userInfo['global_name'];
            $email = $userInfo['email'];
        } else {
            $appInstance->getLogger()->error('Failed to get user info from Discord: ' . $result);
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;
        }

        if (isset($userInfo)) {
            // Check if user is already linked to Discord
            $isLinked = $s->getInfo(UserColumns::DISCORD_LINKED, false);
            if ($isLinked === 'true') {
                header('Location: ' . $url . '/account?error=discord_already_linked');
                exit;
            }

            $s->setInfo(UserColumns::DISCORD_ID, $id, false);
            $s->setInfo(UserColumns::DISCORD_USERNAME, $username, false);
            $s->setInfo(UserColumns::DISCORD_GLOBAL_NAME, $global_name, false);
            $s->setInfo(UserColumns::DISCORD_EMAIL, $email, false);
            $s->setInfo(UserColumns::DISCORD_LINKED, 'true', false);
            $eventManager->emit(DiscordEvent::onDiscordLink(), [
                'user' => $s->getInfo(UserColumns::UUID, false),
            ]);
            UserActivities::add(
                $s->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$discord_link,
                CloudFlareRealIP::getRealIP(),
                "Linked Discord account: $id"
            );
            header('Location: ' . $url . '/');
            exit;
        }
        header('Location: ' . $url . '/api/user/auth/callback/discord/link');

    } else {
        $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . $appId . '&redirect_uri=' . urlencode($redirectUri) . '&response_type=code&scope=' . urlencode('identify guilds email guilds.join');
        header('Location: ' . $authorizeUrl);
    }
});

$router->get('/api/user/auth/callback/discord/unlink', function () {
    App::init();
    $appInstance = App::getInstance(true);
    global $eventManager;
    $config = $appInstance->getConfig();
    $s = new Session($appInstance);

    // Check if user is currently linked to Discord
    $isLinked = $s->getInfo(UserColumns::DISCORD_LINKED, false);
    if ($isLinked !== 'true') {
        header('Location: /account?error=discord_not_linked');
        exit;
    }

    $s->setInfo(UserColumns::DISCORD_ID, null, false);
    $s->setInfo(UserColumns::DISCORD_USERNAME, null, false);
    $s->setInfo(UserColumns::DISCORD_GLOBAL_NAME, null, false);
    $s->setInfo(UserColumns::DISCORD_EMAIL, null, false);
    $s->setInfo(UserColumns::DISCORD_LINKED, 'false', false);
    $eventManager->emit(DiscordEvent::onDiscordUnlink(), [
        'user' => $s->getInfo(UserColumns::UUID, false),
    ]);
    UserActivities::add(
        $s->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$discord_unlink,
        CloudFlareRealIP::getRealIP(),
        'Unlinked Discord account'
    );
    header('Location: /account');
    exit;
});

$router->get('/api/user/auth/callback/discord/login', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    if (
        $config->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false') === 'false'
        || $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_ID, '') === ''
        || $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_SECRET, '') === ''
    ) {
        header('Location: /account?error=discord_not_enabled');
        exit;
    }

    global $eventManager;
    $appId = $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_ID, '');
    $appSecret = $config->getDBSetting(ConfigInterface::DISCORD_CLIENT_SECRET, '');
    $url = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    $redirectUri = $url . '/api/user/auth/callback/discord/login';

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $tokenUrl = 'https://discord.com/api/oauth2/token';
        $data = [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'scope' => 'identify guilds email guilds.join',
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($tokenUrl, false, $context);

        if ($result === false) {
            $appInstance->getLogger()->error('Failed to make HTTP request to Discord token endpoint');
            header('Location: ' . $url . '/auth/login?error=discord_request_failed');
            exit;
        }

        $tokenData = json_decode($result, true);
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            $appInstance->getLogger()->error('Failed to get access token from Discord: ' . $result);
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;
        }

        $userUrl = 'https://discord.com/api/users/@me';

        $options = [
            'http' => [
                'header' => "Authorization: Bearer $accessToken\r\n",
                'method' => 'GET',
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($userUrl, false, $context);

        if ($result === false) {
            $appInstance->getLogger()->error('Failed to make HTTP request to Discord user endpoint');
            header('Location: ' . $url . '/auth/login?error=discord_request_failed');
            exit;
        }

        $userInfo = json_decode($result, true);

        if (isset($userInfo['id'])) {
            $id = $userInfo['id'];
        } else {
            $appInstance->getLogger()->error('Failed to get user info from Discord: ' . $result);
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;
        }

        if (isset($userInfo['id'])) {
            if (User::exists(UserColumns::DISCORD_ID, $id)) {
                $uuid = User::getUUIDFromDiscordID($id);
                $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
                $password = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::PASSWORD, true);
                header('Location: ' . $url . '/auth/login?email=' . urlencode(base64_encode($email)) . '&password=' . urlencode(base64_encode($password)) . '&performLogin=true');
                $eventManager->emit(DiscordEvent::onDiscordLogin(), [
                    'user' => $uuid,
                ]);
                UserActivities::add(
                    $uuid,
                    UserActivitiesTypes::$discord_login,
                    CloudFlareRealIP::getRealIP(),
                    'Logged in with Discord'
                );
                exit;
            }
            $appInstance->getLogger()->error('Discord login failed for user: ' . $id . ' with context: ' . json_encode($userInfo));
            header('Location: ' . $url . '/auth/login?error=discord');
            exit;

        }
        header('Location: ' . $url . '/api/user/auth/callback/discord/login');

    } else {
        $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . $appId . '&redirect_uri=' . urlencode($redirectUri) . '&response_type=code&scope=' . urlencode('identify guilds email guilds.join');
        header('Location: ' . $authorizeUrl);
    }

});
