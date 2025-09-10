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

use Exception;
use MythicalDash\App;
use GuzzleHttp\Client;
use MythicalDash\Config\ConfigInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

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

/**
 * Discord API Utilities Class.
 *
 * Handles Discord API operations with proper type safety and error handling
 */
class DiscordUtils
{
    /**
     * Discord API Response Types.
     */
    public const STATUS_SUCCESS = 200;
    public const STATUS_CREATED = 201;
    public const STATUS_NO_CONTENT = 204;
    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_UNAUTHORIZED = 401;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_CONFLICT = 409;

    /**
     * Discord API Error Messages.
     */
    private const ERROR_MESSAGES = [
        self::STATUS_BAD_REQUEST => 'Invalid request to Discord API',
        self::STATUS_UNAUTHORIZED => 'Invalid or expired access token',
        self::STATUS_FORBIDDEN => 'Bot lacks required permissions',
        self::STATUS_NOT_FOUND => 'Resource not found',
        self::STATUS_CONFLICT => 'Resource already exists',
    ];
    private App $app;
    private Client $client;
    private string $botToken;
    private string $apiBaseUrl = 'https://discord.com/api';

    /**
     * Constructor.
     *
     * @param App $app The application instance
     *
     * @throws \InvalidArgumentException If bot token is not configured
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->botToken = $this->getBotToken();
        $this->initializeClient();
    }

    /**
     * Add user to Discord guild (matches Cindr.org logic).
     *
     * @param string $discordId The Discord user ID
     * @param string $accessToken The user's access token
     * @param string $guildId The Discord guild ID
     *
     * @return bool True if successful, false otherwise
     */
    public function addUserToGuild(string $discordId, string $accessToken, string $guildId): bool
    {
        try {
            // Validate inputs
            if (!self::isValidDiscordId($discordId)) {
                $this->app->getLogger()->error("Invalid Discord ID format: {$discordId}");

                return false;
            }

            if (!self::isValidGuildId($guildId)) {
                $this->app->getLogger()->error("Invalid guild ID format: {$guildId}");

                return false;
            }

            if (!self::isValidAccessToken($accessToken)) {
                $this->app->getLogger()->error('Invalid access token format');

                return false;
            }

            $this->app->getLogger()->debug("Attempting to add user {$discordId} to guild {$guildId}");

            // Payload must be JSON encoded as per Discord API requirements
            $payload = [
                'access_token' => $accessToken,
            ];

            // Use the exact URL format from Cindr.org guide
            $url = "{$this->apiBaseUrl}/guilds/{$guildId}/members/{$discordId}";

            $response = $this->client->put($url, [
                'json' => $payload, // Guzzle automatically JSON encodes this
                'headers' => [
                    'Authorization' => "Bot {$this->botToken}",
                    'Content-Type' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            // Discord API returns 201 for successful creation, 204 for no content
            if ($statusCode === self::STATUS_CREATED || $statusCode === self::STATUS_NO_CONTENT) {
                $this->app->getLogger()->debug("Successfully added user {$discordId} to guild {$guildId}");

                return true;
            }

            $this->app->getLogger()->warning("Unexpected status code when adding user to guild: {$statusCode}");

            return false;

        } catch (ClientException $e) {
            return $this->handleClientException($e, 'add user to guild');
        } catch (ConnectException $e) {
            $this->app->getLogger()->error("Connection error when adding user to guild: {$e->getMessage()}");

            return false;
        } catch (\Exception $e) {
            $this->app->getLogger()->error("Unexpected error when adding user to guild: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Get user's Discord guilds (matches Cindr.org logic).
     *
     * @param string $accessToken The user's access token
     *
     * @return array<string, mixed> Array of guilds or empty array on error
     */
    public function getUserGuilds(string $accessToken): array
    {
        try {
            // Validate access token
            if (!self::isValidAccessToken($accessToken)) {
                $this->app->getLogger()->error('Invalid access token format');

                return [];
            }

            $this->app->getLogger()->debug('Fetching user guilds from Discord API');

            // Use the exact URL format from Cindr.org guide
            $url = "{$this->apiBaseUrl}/users/@me/guilds";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === self::STATUS_SUCCESS) {
                $guilds = json_decode($response->getBody()->getContents(), true);
                $guildCount = is_array($guilds) ? count($guilds) : 0;
                $this->app->getLogger()->debug("Successfully fetched user guilds. Count: {$guildCount}");

                return is_array($guilds) ? $guilds : [];
            }

            $this->app->getLogger()->error("Failed to fetch user guilds. Status: {$statusCode}");

            return [];

        } catch (ClientException $e) {
            $this->handleClientException($e, 'fetch user guilds');

            return [];
        } catch (ConnectException $e) {
            $this->app->getLogger()->error("Connection error when fetching user guilds: {$e->getMessage()}");

            return [];
        } catch (\Exception $e) {
            $this->app->getLogger()->error("Unexpected error when fetching user guilds: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Get Discord user information.
     *
     * @param string $accessToken The user's access token
     *
     * @return array<string, mixed> User information or empty array on error
     */
    public function getUserInfo(string $accessToken): array
    {
        try {
            $this->app->getLogger()->debug('Fetching user information from Discord API');

            $url = "{$this->apiBaseUrl}/users/@me";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === self::STATUS_SUCCESS) {
                $userInfo = json_decode($response->getBody()->getContents(), true);
                $this->app->getLogger()->debug('Successfully fetched user information');

                return is_array($userInfo) ? $userInfo : [];
            }

            $this->app->getLogger()->error("Failed to fetch user information. Status: {$statusCode}");

            return [];

        } catch (ClientException $e) {
            $this->handleClientException($e, 'fetch user information');

            return [];
        } catch (ConnectException $e) {
            $this->app->getLogger()->error("Connection error when fetching user information: {$e->getMessage()}");

            return [];
        } catch (\Exception $e) {
            $this->app->getLogger()->error("Unexpected error when fetching user information: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Validate Discord user ID format.
     *
     * @param string $discordId The Discord user ID
     *
     * @return bool True if valid, false otherwise
     */
    public static function isValidDiscordId(string $discordId): bool
    {
        return preg_match('/^\d{17,19}$/', $discordId) === 1;
    }

    /**
     * Validate Discord guild ID format.
     *
     * @param string $guildId The Discord guild ID
     *
     * @return bool True if valid, false otherwise
     */
    public static function isValidGuildId(string $guildId): bool
    {
        return preg_match('/^\d{17,19}$/', $guildId) === 1;
    }

    /**
     * Validate Discord access token format.
     *
     * @param string $accessToken The Discord access token
     *
     * @return bool True if valid, false otherwise
     */
    public static function isValidAccessToken(string $accessToken): bool
    {
        return !empty($accessToken) && strlen($accessToken) > 10;
    }

    /**
     * Get Discord bot token from configuration.
     *
     * @throws \InvalidArgumentException If bot token is not configured
     *
     * @return string The bot token
     */
    private function getBotToken(): string
    {
        $config = $this->app->getConfig();
        $botToken = $config->getDBSetting(ConfigInterface::DISCORD_BOT_TOKEN, '');

        if (empty($botToken)) {
            throw new \InvalidArgumentException('Discord bot token not configured');
        }

        return $botToken;
    }

    /**
     * Initialize Guzzle HTTP client.
     */
    private function initializeClient(): void
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'MythicalDash/1.0',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Handle Guzzle ClientException with proper error logging.
     *
     * @param ClientException $e The exception
     * @param string $operation Description of the operation
     *
     * @return bool False for most errors, true for specific success cases
     */
    private function handleClientException(ClientException $e, string $operation): bool
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $responseBody = $e->getResponse()->getBody()->getContents();

        $this->app->getLogger()->error("Failed to {$operation}. Status: {$statusCode}, Response: {$responseBody}");

        $errorMessage = self::ERROR_MESSAGES[$statusCode] ?? "Unknown Discord API error: {$statusCode}";
        $this->app->getLogger()->error($errorMessage);

        // Handle specific status codes
        switch ($statusCode) {
            case self::STATUS_CONFLICT:
                // 409 for addUserToGuild means user is already a member (success case)
                if ($operation === 'add user to guild') {
                    $this->app->getLogger()->info('User is already a member of the guild');

                    return true;
                }
                break;
            case self::STATUS_UNAUTHORIZED:
                $this->app->getLogger()->error('Invalid access token');
                break;
            case self::STATUS_FORBIDDEN:
                $this->app->getLogger()->error('Bot lacks required permissions');
                break;
            case self::STATUS_NOT_FOUND:
                $this->app->getLogger()->error('Resource not found');
                break;
        }

        return false;
    }
}
