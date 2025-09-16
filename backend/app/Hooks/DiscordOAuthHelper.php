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

namespace MythicalDash\Hooks;

use MythicalDash\App;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Services\DiscordUtils;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\J4RServers\J4RServers;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\J4REvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

/**
 * Discord OAuth Helper Class.
 */
class DiscordOAuthHelper
{
    private App $app;
    private $config;
    private DiscordUtils $discordUtils;
    private string $appId;
    private string $appSecret;
    private string $baseUrl;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = $app->getConfig();
        $this->discordUtils = new DiscordUtils($app);
        $this->appId = $this->config->getDBSetting(ConfigInterface::DISCORD_CLIENT_ID, '');
        $this->appSecret = $this->config->getDBSetting(ConfigInterface::DISCORD_CLIENT_SECRET, '');
        $this->baseUrl = $this->getSecureBaseUrl();
    }

    /**
     * Get secure base URL with HTTPS enforcement.
     */
    public function getSecureBaseUrl(): string
    {
        $url = 'https://' . $this->config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');

        // Always enforce HTTPS
        if (strpos($url, 'https://') !== 0) {
            $url = preg_replace('/^http:\/\//i', '', $url);
            $url = 'https://' . ltrim($url, '/');
        }

        return rtrim($url, '/');
    }

    /**
     * Validate Discord configuration.
     */
    public function validateConfig(): bool
    {
        return $this->config->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false') === 'true'
               && !empty($this->appId)
               && !empty($this->appSecret);
    }

    /**
     * Get Discord authorization URL with required scopes.
     */
    public function getAuthUrl(string $redirectUri): string
    {
        $requiredScopes = ['identify', 'guilds', 'email', 'guilds.join'];
        $scope = implode(' ', $requiredScopes);

        return 'https://discord.com/api/oauth2/authorize?' . http_build_query([
            'client_id' => $this->appId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
        ]);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): ?string
    {
        $tokenUrl = 'https://discord.com/api/oauth2/token';
        $data = [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $this->app->getLogger()->error('Discord token exchange failed: HTTP ' . $httpCode . ' - ' . $response);

            // Log additional details for debugging
            if ($httpCode === 400) {
                $this->app->getLogger()->error('Discord OAuth: Bad request - invalid authorization code or redirect URI');
            } elseif ($httpCode === 401) {
                $this->app->getLogger()->error('Discord OAuth: Unauthorized - invalid client credentials');
            } elseif ($httpCode === 403) {
                $this->app->getLogger()->error('Discord OAuth: Forbidden - insufficient permissions');
            }

            return null;
        }

        $tokenData = json_decode($response, true);

        return $tokenData['access_token'] ?? null;
    }

    /**
     * Get user information from Discord API.
     */
    public function getUserInfo(string $accessToken): ?array
    {
        $userUrl = 'https://discord.com/api/users/@me';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $userUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $this->app->getLogger()->error('Discord user info failed: HTTP ' . $httpCode . ' - ' . $response);

            // Log additional details for debugging
            if ($httpCode === 401) {
                $this->app->getLogger()->error('Discord OAuth: Unauthorized - invalid or expired access token');
            } elseif ($httpCode === 403) {
                $this->app->getLogger()->error('Discord OAuth: Forbidden - insufficient permissions to access user info');
            } elseif ($httpCode === 429) {
                $this->app->getLogger()->error('Discord OAuth: Rate limited - too many requests');
            }

            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Force join a Discord server if enabled.
     */
    public function forceJoinServer(string $discordId, string $accessToken): void
    {
        $forceJoinServerId = $this->config->getDBSetting(ConfigInterface::DISCORD_FORCE_JOIN_SERVER, 'false');

        if ($forceJoinServerId === 'false' || empty($forceJoinServerId)) {
            return;
        }

        try {
            $this->discordUtils->addUserToGuild($discordId, $accessToken, $forceJoinServerId);
            $this->app->getLogger()->info('Forced Discord server join for user: ' . $discordId);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to force Discord server join: ' . $e->getMessage());
        }
    }

    /**
     * Check J4R server joins and update user's joined servers list.
     */
    public function checkJ4RServerJoins(string $discordId, string $accessToken, Session $session): void
    {
        try {
            // Get available J4R servers
            $j4rServers = J4RServers::getAvailableList();
            if (empty($j4rServers)) {
                return;
            }

            // Get user's current Discord guilds
            $guildsUrl = 'https://discord.com/api/users/@me/guilds';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $guildsUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                $this->app->getLogger()->error('Failed to get Discord guilds: HTTP ' . $httpCode);

                return;
            }

            $userGuilds = json_decode($response, true);
            if (!is_array($userGuilds)) {
                return;
            }

            // Create a map of user's guild IDs for quick lookup
            $userGuildIds = array_column($userGuilds, 'id');

            // Get user's current joined J4R servers
            $currentJoinedServers = $session->getInfo(UserColumns::J4R_JOINED_SERVERS, false);
            $joinedServersArray = !empty($currentJoinedServers) ? json_decode($currentJoinedServers, true) : [];
            if (!is_array($joinedServersArray)) {
                $joinedServersArray = [];
            }

            $newlyJoinedServers = [];
            $totalCoinsEarned = 0;

            // Check each J4R server
            foreach ($j4rServers as $j4rServer) {
                $serverId = $j4rServer['discord_server_id'];
                $coins = (int) $j4rServer['coins'];

                // Check if user is in this server
                if (in_array($serverId, $userGuildIds)) {
                    // Check if user hasn't already joined this server
                    if (!in_array($serverId, $joinedServersArray)) {
                        $newlyJoinedServers[] = $serverId;
                        $joinedServersArray[] = $serverId;
                        $totalCoinsEarned += $coins;

                        // Emit event for server join
                        global $eventManager;
                        $eventManager->emit(J4REvent::onJ4RServerJoined(), [
                            'user_uuid' => $session->getInfo(UserColumns::UUID, false),
                            'username' => $session->getInfo(UserColumns::USERNAME, false),
                            'server_id' => $serverId,
                            'server_name' => $j4rServer['name'],
                            'coins_earned' => $coins,
                        ]);

                        // Log activity
                        UserActivities::add(
                            $session->getInfo(UserColumns::UUID, false),
                            UserActivitiesTypes::$j4r_server_joined,
                            CloudFlareRealIP::getRealIP(),
                            'Joined J4R server: ' . $j4rServer['name'] . ' (+' . $coins . ' coins)'
                        );
                    }
                }
            }

            // Update user's joined servers list
            if (!empty($newlyJoinedServers)) {
                $session->setInfo(UserColumns::J4R_JOINED_SERVERS, json_encode($joinedServersArray), false);

                // Add coins to user's balance atomically to prevent race conditions
                if (!$session->addCreditsAtomic($totalCoinsEarned)) {
                    // If adding credits failed, log this critical error
                    $this->app->getLogger()->error('Failed to add J4R rewards atomically for user: ' . $session->getInfo(UserColumns::USERNAME, false) . ' for coins: ' . $totalCoinsEarned);
                    // Continue with the request but don't show the new total
                    $this->app->getLogger()->warning('J4R rewards failed to add but servers were marked as joined for user: ' . $session->getInfo(UserColumns::USERNAME, false));
                } else {
                    // Emit event for rewards claimed
                    global $eventManager;
                    $eventManager->emit(J4REvent::onJ4RRewardsClaimed(), [
                        'user_uuid' => $session->getInfo(UserColumns::UUID, false),
                        'username' => $session->getInfo(UserColumns::USERNAME, false),
                        'coins_earned' => $totalCoinsEarned,
                        'servers_joined' => $newlyJoinedServers,
                    ]);

                    $this->app->getLogger()->info('J4R rewards claimed for user: ' . $session->getInfo(UserColumns::USERNAME, false) . ' (+' . $totalCoinsEarned . ' coins)');
                }
            }

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Error checking J4R server joins: ' . $e->getMessage());
        }
    }

    /**
     * Store user's Discord guilds in session.
     */
    public function storeUserGuilds(Session $session, string $accessToken): void
    {
        try {
            $guildsUrl = 'https://discord.com/api/users/@me/guilds';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $guildsUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $guilds = json_decode($response, true);
                if (is_array($guilds)) {
                    $session->setInfo(UserColumns::DISCORD_SERVERS, json_encode($guilds), false);
                }
            }
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Error storing Discord guilds: ' . $e->getMessage());
        }
    }

    /**
     * Store Discord user data in session.
     */
    public function storeDiscordData(Session $session, array $userInfo): void
    {
        $session->setInfo(UserColumns::DISCORD_ID, $userInfo['id'], false);
        $session->setInfo(UserColumns::DISCORD_USERNAME, $userInfo['username'] ?? '', false);
        $session->setInfo(UserColumns::DISCORD_GLOBAL_NAME, $userInfo['global_name'] ?? '', false);
        $session->setInfo(UserColumns::DISCORD_EMAIL, $userInfo['email'] ?? '', false);
        $session->setInfo(UserColumns::DISCORD_LINKED, 'true', false);
    }

    /**
     * Clear Discord data from session.
     */
    public function clearDiscordData(Session $session): void
    {
        $session->setInfo(UserColumns::DISCORD_ID, '', false);
        $session->setInfo(UserColumns::DISCORD_USERNAME, '', false);
        $session->setInfo(UserColumns::DISCORD_GLOBAL_NAME, '', false);
        $session->setInfo(UserColumns::DISCORD_EMAIL, '', false);
        $session->setInfo(UserColumns::DISCORD_LINKED, 'false', false);
        $session->setInfo(UserColumns::DISCORD_SERVERS, '', false);
    }
}
