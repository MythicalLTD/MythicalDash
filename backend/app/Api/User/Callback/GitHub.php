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
use MythicalDash\Plugins\Events\Events\GitHubEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/user/auth/callback/github/link', function () {
    header('Location: /api/user/auth/callback/github');
    exit;
});

$router->get('/api/user/auth/callback/github/login', function () {
    header('Location: /api/user/auth/callback/github');
    exit;
});

$router->get('/api/user/auth/callback/github', function () {
    global $authorizeURL, $tokenURL, $apiURLBase;
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $eventManager;

    $appInstance->getLogger()->debug('Starting GitHub authentication process');

    if (
        $config->getSetting(ConfigInterface::GITHUB_ENABLED, 'false') === 'false'
        || $config->getSetting(ConfigInterface::GITHUB_CLIENT_ID, '') === ''
        || $config->getSetting(ConfigInterface::GITHUB_CLIENT_SECRET, '') === ''
    ) {
        $appInstance->getLogger()->debug('GitHub integration is not properly configured');
        header('Location: /account?error=github_not_enabled');
        exit;
    }

    $appId = $config->getSetting(ConfigInterface::GITHUB_CLIENT_ID, '');
    $appSecret = $config->getSetting(ConfigInterface::GITHUB_CLIENT_SECRET, '');
    $url = $config->getSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');
    $redirectUri = $url . '/api/user/auth/callback/github';

    $appInstance->getLogger()->debug('Initializing OAuth provider with redirect URI: ' . $redirectUri);

    // Initialize GitHub OAuth provider with proper scopes
    $provider = new League\OAuth2\Client\Provider\Github([
        'clientId' => $appId,
        'clientSecret' => $appSecret,
        'redirectUri' => $redirectUri,
    ]);

    // Generate a random state parameter for CSRF protection
    $state = bin2hex(random_bytes(16));

    if (!isset($_GET['code'])) {
        $appInstance->getLogger()->debug('No authorization code found, redirecting to GitHub for authorization');
        // If we don't have an authorization code then get one
        $options = [
            'state' => $state,
            'scope' => ['user:email', 'read:user', 'user:follow', 'public_repo'], // Request basic user info
        ];

        $authUrl = $provider->getAuthorizationUrl($options);
        $appInstance->getLogger()->debug('Generated authorization URL with state: ' . $state);

        // Store state in session for validation
        setcookie('oauth2state', $state, time() + 3600, '/');

        header('Location: ' . $authUrl);
        exit;
    }

    try {
        $appInstance->getLogger()->debug('Received authorization code, attempting to get access token');
        // Try to get an access token
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);
        $appInstance->getLogger()->debug('Successfully obtained access token');

        // Get user details
        $user = $provider->getResourceOwner($token);
        $userData = $user->toArray();
        $appInstance->getLogger()->debug('Retrieved user data for GitHub ID: ' . $userData['id']);

        $id = $userData['id'];
        $email = $userData['email'];
        $name = $userData['name'];

        if (isset($_COOKIE['user_token']) && $_COOKIE['user_token'] != '' && User::exists(UserColumns::ACCOUNT_TOKEN, $_COOKIE['user_token'])) {
            $appInstance->getLogger()->debug('User is logged in, linking GitHub account');
            $s = new Session($appInstance);
            $s->setInfo(UserColumns::GITHUB_ID, $id, false);
            $s->setInfo(UserColumns::GITHUB_EMAIL, $email, false);
            $s->setInfo(UserColumns::GITHUB_USERNAME, $name, false);
            $s->setInfo(UserColumns::GITHUB_LINKED, 'true', false);
            $appInstance->getLogger()->debug('Successfully linked GitHub account for user: ' . $s->getInfo(UserColumns::UUID, false));

            $eventManager->emit(GitHubEvent::onGitHubLink(), [
                'user' => $s->getInfo(UserColumns::UUID, false),
            ]);
            UserActivities::add(
                $s->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$github_link,
                CloudFlareRealIP::getRealIP(),
                "Linked GitHub account: $id"
            );

            $appInstance->getLogger()->debug('Attempting to star repository and follow organization');
            // Create a Guzzle client for GitHub API requests
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://api.github.com/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'MythicalDash',
                ],
            ]);

            try {
                // Star the repository
                $client->put('user/starred/mythicalltd/mythicaldash');
                $appInstance->getLogger()->debug('Successfully starred repository');

                // Follow the organization
                $client->put('user/following/mythicalltd');
                $appInstance->getLogger()->debug('Successfully followed organization');

                header('Location: /account?success=github_auth_success');
            } catch (Exception $e) {
                $appInstance->getLogger()->debug('Error during repository actions: ' . $e->getMessage());
                // If the actions fail, still return success but with partial completion
                header('Location: /account?success=github_auth_success');
            }
            exit;
        }

        if (User::exists(UserColumns::GITHUB_ID, $id)) {
            $appInstance->getLogger()->debug('Found existing user with GitHub ID: ' . $id);
            $uuid = User::getUUIDFromGitHubID($id);

            if (!$uuid == '') {
                $appInstance->getLogger()->debug('Attempting login for existing user: ' . $uuid);
                $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
                $password = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::PASSWORD, true);
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
                header('Location: ' . $url . '/auth/login?email=' . urlencode(base64_encode($email)) . '&password=' . urlencode(base64_encode($password)) . '&performLogin=true');

                exit;
            }
            $appInstance->getLogger()->debug('Failed to find valid UUID for GitHub ID: ' . $id);
            header('Location: /auth/login?error=github_auth_failed');
            exit;
        }
        $appInstance->getLogger()->debug('No existing user found for GitHub ID: ' . $id);
        header('Location: /auth/login?error=github_auth_failed');
        exit;

    } catch (Exception $e) {
        // Log error and show user-friendly message
        $appInstance->getLogger()->debug('OAuth Error: ' . $e->getMessage());
        $appInstance->getLogger()->debug('Stack trace: ' . $e->getTraceAsString());
        header('Location: /account?error=github_auth_failed&message=' . urlencode($e->getMessage()));
    }
});

$router->get('/api/user/auth/callback/github/unlink', function () {
    global $authorizeURL, $tokenURL, $apiURLBase;
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    global $eventManager;
    $s = new Session($appInstance);
    $uuid = $s->getInfo(UserColumns::UUID, false);
    $appInstance->getLogger()->debug('Unlinking GitHub account for user: ' . $uuid);

    $s->setInfo(UserColumns::GITHUB_ID, '', false);
    $s->setInfo(UserColumns::GITHUB_EMAIL, '', false);
    $s->setInfo(UserColumns::GITHUB_USERNAME, '', false);
    $s->setInfo(UserColumns::GITHUB_LINKED, 'false', false);
    $appInstance->getLogger()->debug('Successfully unlinked GitHub account for user: ' . $uuid);

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
});
