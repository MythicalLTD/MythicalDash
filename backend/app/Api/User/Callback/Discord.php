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
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Hooks\DiscordOAuthHelper;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DiscordEvent;

// Discord Link Callback
$router->get('/api/user/auth/callback/discord/link', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);

    if (!$helper->validateConfig()) {
        // Discord integration is not enabled
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
            // Failed to exchange code for token
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            // Failed to fetch user info from Discord
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_failed');
            exit;
        }

        // Check if user is already linked
        $isLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
        if ($isLinked === 'true') {
            // User already linked Discord
            header('Location: ' . $helper->getSecureBaseUrl() . '/account?error=discord_already_linked');
            exit;
        }

        // Store Discord data
        $helper->storeDiscordData($session, $userInfo);

        // Force join server if enabled
        $helper->forceJoinServer($userInfo['id'], $accessToken);

        // Store user guilds
        $helper->storeUserGuilds($session, $accessToken);

        // Emit events and log activity
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

    // Redirect to Discord authorization
    header('Location: ' . $helper->getAuthUrl($redirectUri));
});

// Discord Unlink Callback
$router->get('/api/user/auth/callback/discord/unlink', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);
    $session = new Session($appInstance);
    global $eventManager;

    // Check if user is currently linked
    $isLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
    if ($isLinked !== 'true') {
        // User is not linked
        header('Location: /account?error=discord_not_linked');
        exit;
    }

    // Clear Discord data
    $helper->clearDiscordData($session);

    // Emit events and log activity
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

    if (!$helper->validateConfig()) {
        // Discord integration is not enabled
        header('Location: /auth/login?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/login';
    global $eventManager;

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            // Failed to exchange code for token
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            // Failed to fetch user info from Discord
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_failed');
            exit;
        }

        // Check if user exists
        if (!User::exists(UserColumns::DISCORD_ID, $userInfo['id'])) {
            // No user found for this Discord ID
            $appInstance->getLogger()->error('Discord login failed for user: ' . $userInfo['id']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_not_found');
            exit;
        }

        $uuid = User::getUUIDFromDiscordID($userInfo['id']);

        // Force join server if enabled
        $helper->forceJoinServer($userInfo['id'], $accessToken);

        // Perform login
        $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
        $password = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::PASSWORD, true);
        if (!$email || !$password) {
            // User record missing email or password
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord_user_not_found');
            exit;
        }

        header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?email=' . urlencode(base64_encode($email)) . '&password=' . urlencode(base64_encode($password)) . '&performLogin=true');

        // Emit events and log activity
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
        // Discord integration is not enabled
        header('Location: /earn/j4r?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/j4r';

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            // Failed to exchange code for token
            header('Location: /earn/j4r?error=discord_token_failed');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            // Failed to fetch user info from Discord
            header('Location: /earn/j4r?error=discord_user_failed');
            exit;
        }

        // Verify this is the same user
        $sessionDiscordId = $session->getInfo(UserColumns::DISCORD_ID, false);
        if ($sessionDiscordId !== $userInfo['id']) {
            // Discord user mismatch
            header('Location: /earn/j4r?error=discord_user_mismatch');
            exit;
        }

        // Check J4R server joins
        $helper->checkJ4RServerJoins($userInfo['id'], $accessToken, $session);

        // Log J4R check activity
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$j4r_check,
            CloudFlareRealIP::getRealIP(),
            'Performed J4R check via dedicated endpoint'
        );

        // Redirect back to j4r with success message
        header('Location: /earn/j4r?success=j4r_check_completed');
        exit;
    }

    // Redirect to Discord authorization
    header('Location: ' . $helper->getAuthUrl($redirectUri));
});
