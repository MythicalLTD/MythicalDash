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

/**
 * GitHub OAuth Helper Class.
 */
class GitHubOAuthHelper
{
    private App $app;
    private $config;
    private string $appId;
    private string $appSecret;
    private string $baseUrl;
    private League\OAuth2\Client\Provider\Github $provider;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = $app->getConfig();
        $this->appId = $this->config->getDBSetting(ConfigInterface::GITHUB_CLIENT_ID, '');
        $this->appSecret = $this->config->getDBSetting(ConfigInterface::GITHUB_CLIENT_SECRET, '');
        $this->baseUrl = $this->getSecureBaseUrl();
        $this->initializeProvider();
    }

    /**
     * Get secure base URL with HTTPS enforcement.
     */
    public function getSecureBaseUrl(): string
    {
        $url = $this->config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');

        // Always enforce HTTPS
        if (strpos($url, 'https://') !== 0) {
            $url = preg_replace('/^http:\/\//i', '', $url);
            $url = 'https://' . ltrim($url, '/');
        }

        return rtrim($url, '/');
    }

    /**
     * Validate GitHub configuration.
     */
    public function validateConfig(): bool
    {
        return $this->config->getDBSetting(ConfigInterface::GITHUB_ENABLED, 'false') === 'true'
               && !empty($this->appId)
               && !empty($this->appSecret);
    }

    /**
     * Get GitHub authorization URL with required scopes.
     */
    public function getAuthUrl(): string
    {
        $requiredScopes = ['user:email', 'read:user', 'user:follow', 'public_repo'];
        $state = bin2hex(random_bytes(16));

        $options = [
            'state' => $state,
            'scope' => $requiredScopes,
        ];

        // Store state in session for validation
        setcookie('oauth2state', $state, time() + 3600, '/');

        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code): ?League\OAuth2\Client\Token\AccessToken
    {
        try {
            return $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (Exception $e) {
            $this->app->getLogger()->error('Failed to exchange code for token: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get GitHub user information.
     */
    public function getUserInfo(League\OAuth2\Client\Token\AccessToken $token): ?array
    {
        try {
            $user = $this->provider->getResourceOwner($token);
            $userData = $user->toArray();

            // Validate required fields to prevent tampering
            if (!isset($userData['id']) || !isset($userData['email']) || !isset($userData['name'])) {
                $this->app->getLogger()->error('Invalid GitHub user info response: missing required fields');

                return null;
            }

            return $userData;
        } catch (Exception $e) {
            $this->app->getLogger()->error('Failed to get GitHub user info: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Perform GitHub repository actions (star repo, follow org).
     */
    public function performRepositoryActions(League\OAuth2\Client\Token\AccessToken $token): void
    {
        try {
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://api.github.com/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'MythicalDash',
                ],
            ]);

            // Star the repository
            $client->put('user/starred/mythicalltd/mythicaldash');
            $this->app->getLogger()->debug('Successfully starred repository');

            // Follow the organization
            $client->put('user/following/mythicalltd');
            $this->app->getLogger()->debug('Successfully followed organization');

        } catch (Exception $e) {
            $this->app->getLogger()->warning('Error during repository actions: ' . $e->getMessage());
            // Don't throw - these are optional actions
        }
    }

    /**
     * Store GitHub user data.
     */
    public function storeGitHubData(Session $session, array $userData): void
    {
        $session->setInfo(UserColumns::GITHUB_ID, $userData['id'], false);
        $session->setInfo(UserColumns::GITHUB_EMAIL, $userData['email'], false);
        $session->setInfo(UserColumns::GITHUB_USERNAME, $userData['name'], false);
        $session->setInfo(UserColumns::GITHUB_LINKED, 'true', false);
    }

    /**
     * Clear GitHub user data.
     */
    public function clearGitHubData(Session $session): void
    {
        $session->setInfo(UserColumns::GITHUB_ID, '', false);
        $session->setInfo(UserColumns::GITHUB_EMAIL, '', false);
        $session->setInfo(UserColumns::GITHUB_USERNAME, '', false);
        $session->setInfo(UserColumns::GITHUB_LINKED, 'false', false);
    }

    /**
     * Initialize GitHub OAuth provider.
     */
    private function initializeProvider(): void
    {
        $redirectUri = $this->baseUrl . '/api/user/auth/callback/github';

        $this->provider = new League\OAuth2\Client\Provider\Github([
            'clientId' => $this->appId,
            'clientSecret' => $this->appSecret,
            'redirectUri' => $redirectUri,
        ]);
    }
}

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
        $appInstance->getLogger()->debug('GitHub integration is not properly configured');
        header('Location: /account?error=github_not_enabled');
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
            header('Location: /account?error=github_auth_failed');
            exit;
        }

        $appInstance->getLogger()->debug('Successfully obtained access token');

        $userData = $helper->getUserInfo($token);
        if (!$userData) {
            header('Location: /account?error=github_auth_failed');
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

            header('Location: /account?success=github_auth_success');
            exit;
        }

        // Check if user exists for login
        if (User::exists(UserColumns::GITHUB_ID, $userData['id'])) {
            $appInstance->getLogger()->debug('Found existing user with GitHub ID: ' . $userData['id']);

            $uuid = User::getUUIDFromGitHubID($userData['id']);
            if (empty($uuid)) {
                $appInstance->getLogger()->debug('Failed to find valid UUID for GitHub ID: ' . $userData['id']);
                header('Location: /auth/login?error=github_auth_failed');
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
        header('Location: /auth/login?error=github_auth_failed');
        exit;

    } catch (Exception $e) {
        $appInstance->getLogger()->error('GitHub OAuth Error: ' . $e->getMessage());
        $appInstance->getLogger()->error('Stack trace: ' . $e->getTraceAsString());
        header('Location: /account?error=github_auth_failed&message=' . urlencode($e->getMessage()));
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
