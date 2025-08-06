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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Services\GitHubOAuthHelper;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\GitHubEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// GitHub Link Callback
$router->get('/api/user/auth/callback/github/link', function () {
    header('Location: /api/user/auth/callback/github');
    exit;
});

// GitHub Login Callback
$router->get('/api/user/auth/callback/github/login', function () {
    header('Location: /api/user/auth/callback/github');
    exit;
});

// Main GitHub OAuth Callback
$router->get('/api/user/auth/callback/github', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new GitHubOAuthHelper($appInstance);
    global $eventManager;

    $appInstance->getLogger()->debug('Starting GitHub authentication process');

    if (!$helper->validateConfig()) {
        // GitHub integration is not properly configured
        $appInstance->getLogger()->debug('GitHub integration is not properly configured');
        header('Location: /auth/login?error=github_not_enabled');
        exit;
    }

    if (!isset($_GET['code'])) {
        $appInstance->getLogger()->debug('No authorization code found, redirecting to GitHub for authorization');
        header('Location: ' . $helper->getAuthUrl());
        exit;
    }

    try {
        $appInstance->getLogger()->debug('Received authorization code, attempting to get access token');

        $token = $helper->exchangeCodeForToken($_GET['code']);
        if (!$token) {
            header('Location: /auth/login?error=github_token_failed');
            exit;
        }

        $appInstance->getLogger()->debug('Successfully obtained access token');

        $userData = $helper->getUserInfo($token);
        if (!$userData) {
            header('Location: /auth/login?error=github_user_failed');
            exit;
        }

        $appInstance->getLogger()->debug('Retrieved user data for GitHub ID: ' . $userData['id']);

        // Check if user is logged in (linking account)
        if (isset($_COOKIE['user_token']) && $_COOKIE['user_token'] != '' && User::exists(UserColumns::ACCOUNT_TOKEN, $_COOKIE['user_token'])) {
            $appInstance->getLogger()->debug('User is logged in, linking GitHub account');

            $session = new Session($appInstance);
            $helper->storeGitHubData($session, $userData);

            $appInstance->getLogger()->debug('Successfully linked GitHub account for user: ' . $session->getInfo(UserColumns::UUID, false));

            // Emit events and log activity
            $eventManager->emit(GitHubEvent::onGitHubLink(), [
                'user' => $session->getInfo(UserColumns::UUID, false),
            ]);

            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$github_link,
                CloudFlareRealIP::getRealIP(),
                "Linked GitHub account: {$userData['id']}"
            );

            // Perform optional repository actions
            $helper->performRepositoryActions($token);

            header('Location: /account?success=github_linked_success');
            exit;
        }

        // Check if user exists for login
        if (User::exists(UserColumns::GITHUB_ID, $userData['id'])) {
            $appInstance->getLogger()->debug('Found existing user with GitHub ID: ' . $userData['id']);

            $uuid = User::getUUIDFromGitHubID($userData['id']);
            if (empty($uuid)) {
                $appInstance->getLogger()->debug('Failed to find valid UUID for GitHub ID: ' . $userData['id']);
                header('Location: /auth/login?error=github_user_not_found');
                exit;
            }

            $appInstance->getLogger()->debug('Attempting login for existing user: ' . $uuid);

            $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
            $password = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::PASSWORD, true);

            // Emit events and log activity
            $eventManager->emit(GitHubEvent::onGitHubLogin(), [
                'user' => $uuid,
            ]);

            UserActivities::add(
                $uuid,
                UserActivitiesTypes::$github_login,
                CloudFlareRealIP::getRealIP(),
                'Logged in with GitHub'
            );

            $appInstance->getLogger()->debug('Successfully initiated login for user: ' . $uuid);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?email=' . urlencode(base64_encode($email)) . '&password=' . urlencode(base64_encode($password)) . '&performLogin=true');
            exit;
        }

        $appInstance->getLogger()->debug('No existing user found for GitHub ID: ' . $userData['id']);
        header('Location: /auth/login?error=github_user_not_found');
        exit;

    } catch (Exception $e) {
        $appInstance->getLogger()->error('GitHub OAuth Error: ' . $e->getMessage());
        $appInstance->getLogger()->error('Stack trace: ' . $e->getTraceAsString());
        header('Location: /auth/login?error=github_auth_failed&message=' . urlencode($e->getMessage()));
    }
});

// GitHub Unlink Callback
$router->get('/api/user/auth/callback/github/unlink', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new GitHubOAuthHelper($appInstance);
    $session = new Session($appInstance);
    global $eventManager;

    $uuid = $session->getInfo(UserColumns::UUID, false);
    $appInstance->getLogger()->debug('Unlinking GitHub account for user: ' . $uuid);

    // Clear GitHub data
    $helper->clearGitHubData($session);

    $appInstance->getLogger()->debug('Successfully unlinked GitHub account for user: ' . $uuid);

    // Emit events and log activity
    $eventManager->emit(GitHubEvent::onGitHubUnlink(), [
        'user' => $uuid,
    ]);

    UserActivities::add(
        $uuid,
        UserActivitiesTypes::$github_unlink,
        CloudFlareRealIP::getRealIP(),
        'Unlinked GitHub account'
    );

    header('Location: /account');
    exit;
});
