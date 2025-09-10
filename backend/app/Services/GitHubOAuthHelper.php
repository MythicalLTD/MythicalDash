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

namespace MythicalDash\Services;

use MythicalDash\App;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;

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
    private \League\OAuth2\Client\Provider\Github $provider;

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
    public function exchangeCodeForToken(string $code): ?\League\OAuth2\Client\Token\AccessToken
    {
        try {
            return $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to exchange code for token: ' . $e->getMessage());

            // Log additional details for debugging
            if (strpos($e->getMessage(), 'invalid_grant') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Invalid authorization code or redirect URI');
            } elseif (strpos($e->getMessage(), 'invalid_client') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Invalid client credentials');
            } elseif (strpos($e->getMessage(), 'unauthorized_client') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Unauthorized client');
            }

            return null;
        }
    }

    /**
     * Get GitHub user information.
     */
    public function getUserInfo(\League\OAuth2\Client\Token\AccessToken $token): ?array
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
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get GitHub user info: ' . $e->getMessage());

            // Log additional details for debugging
            if (strpos($e->getMessage(), '401') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Unauthorized - invalid or expired access token');
            } elseif (strpos($e->getMessage(), '403') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Forbidden - insufficient permissions to access user info');
            } elseif (strpos($e->getMessage(), '404') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: User not found');
            } elseif (strpos($e->getMessage(), '429') !== false) {
                $this->app->getLogger()->error('GitHub OAuth: Rate limited - too many requests');
            }

            return null;
        }
    }

    /**
     * Perform GitHub repository actions (star repo, follow org).
     */
    public function performRepositoryActions(\League\OAuth2\Client\Token\AccessToken $token): void
    {
        try {
            $client = new \GuzzleHttp\Client([
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

        } catch (\Exception $e) {
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

        $this->provider = new \League\OAuth2\Client\Provider\Github([
            'clientId' => $this->appId,
            'clientSecret' => $this->appSecret,
            'redirectUri' => $redirectUri,
        ]);
    }
}
