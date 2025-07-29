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
use MythicalDash\Services\DiscordUtils;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\J4RServers\J4RServers;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\J4REvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DiscordEvent;

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
        $url = $this->config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems');

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
            $this->app->getLogger()->error('Failed to make HTTP request to Discord token endpoint');

            return null;
        }

        $tokenData = json_decode($result, true);

        return $tokenData['access_token'] ?? null;
    }

    /**
     * Get Discord user information.
     */
    public function getUserInfo(string $accessToken): ?array
    {
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
            $this->app->getLogger()->error('Failed to make HTTP request to Discord user endpoint');

            return null;
        }

        $userInfo = json_decode($result, true);

        // Validate required fields to prevent tampering
        if (!$userInfo || !isset($userInfo['id']) || !isset($userInfo['username'])) {
            $this->app->getLogger()->error('Invalid Discord user info response: ' . $result);

            return null;
        }

        return $userInfo;
    }

    /**
     * Force user to join Discord server if enabled.
     */
    public function forceJoinServer(string $discordId, string $accessToken): void
    {
        try {
            $forceJoinEnabled = $this->config->getDBSetting(ConfigInterface::DISCORD_FORCE_JOIN_SERVER, 'false');
            if ($forceJoinEnabled !== 'true') {
                $this->app->getLogger()->debug('Force join server is disabled, skipping server join');

                return;
            }

            $guildId = $this->config->getDBSetting(ConfigInterface::DISCORD_SERVER_ID, '');
            if (empty($guildId)) {
                $this->app->getLogger()->warning('Discord server ID not configured, skipping server join');

                return;
            }

            $this->app->getLogger()->debug("Attempting to force user {$discordId} to join Discord server {$guildId}");

            $joinResult = $this->discordUtils->addUserToGuild($discordId, $accessToken, $guildId);

            if ($joinResult) {
                $this->app->getLogger()->debug("Successfully added user {$discordId} to Discord server {$guildId}");
            } else {
                $this->app->getLogger()->warning("Failed to add user {$discordId} to Discord server {$guildId}");
            }
        } catch (Exception $e) {
            $this->app->getLogger()->error('Error adding user to Discord server: ' . $e->getMessage());
        }
    }

    /**
     * Check J4R server joins and process rewards.
     */
    public function checkJ4RServerJoins(string $discordId, string $accessToken, Session $session): void
    {
        try {
            // Get user's guilds from Discord
            $userGuilds = $this->discordUtils->getUserGuilds($accessToken);
            if (!is_array($userGuilds)) {
                $this->app->getLogger()->warning('Failed to get user guilds for J4R check');

                return;
            }

            // Get all available J4R servers
            $j4rServers = J4RServers::getAvailableList();
            if (empty($j4rServers)) {
                $this->app->getLogger()->debug('No J4R servers available for checking');

                return;
            }

            $userUuid = $session->getInfo(UserColumns::UUID, false);
            $joinedServers = $session->getInfo(UserColumns::J4R_JOINED_SERVERS, false);
            $joinedServersArray = !empty($joinedServers) ? json_decode($joinedServers, true) : [];

            if (!is_array($joinedServersArray)) {
                $joinedServersArray = [];
            }

            $rewardsGiven = 0;
            $newJoins = [];

            foreach ($j4rServers as $j4rServer) {
                $serverId = $j4rServer['server_id'];

                // Skip if user already joined this server
                if (in_array($serverId, $joinedServersArray)) {
                    continue;
                }

                // Check if user is in this Discord server
                $isInServer = false;
                foreach ($userGuilds as $guild) {
                    if (isset($guild['id']) && $guild['id'] === $serverId) {
                        $isInServer = true;
                        break;
                    }
                }

                if ($isInServer) {
                    // User joined this server, give rewards
                    $coins = (int) $j4rServer['coins'];
                    $currentCoins = (int) $session->getInfo(UserColumns::CREDITS, false);
                    $newCoins = $currentCoins + $coins;

                    // Update user's coins
                    $session->setInfo(UserColumns::CREDITS, (string) $newCoins, false);

                    // Mark server as joined
                    $joinedServersArray[] = $serverId;
                    $newJoins[] = $serverId;

                    ++$rewardsGiven;

                    // Emit event for server joined
                    global $eventManager;
                    $eventManager->emit(J4REvent::onJ4RServerJoined(), [
                        'user_uuid' => $userUuid,
                        'username' => $session->getInfo(UserColumns::USERNAME, false),
                        'server_id' => $serverId,
                        'server_name' => $j4rServer['name'],
                        'coins_earned' => $coins,
                        'discord_id' => $discordId,
                    ]);

                    // Emit event for reward claimed
                    $eventManager->emit(J4REvent::onJ4RRewardsClaimed(), [
                        'user_uuid' => $userUuid,
                        'username' => $session->getInfo(UserColumns::USERNAME, false),
                        'server_id' => $serverId,
                        'server_name' => $j4rServer['name'],
                        'coins_earned' => $coins,
                        'old_balance' => $currentCoins,
                        'new_balance' => $newCoins,
                        'discord_id' => $discordId,
                    ]);

                    // Log the reward
                    UserActivities::add(
                        $userUuid,
                        UserActivitiesTypes::$j4r_reward,
                        CloudFlareRealIP::getRealIP(),
                        "J4R Reward: +{$coins} coins for joining server '{$j4rServer['name']}' (ID: {$serverId})"
                    );

                    $this->app->getLogger()->info("J4R Reward: User {$discordId} received {$coins} coins for joining server '{$j4rServer['name']}'");
                }
            }

            // Update joined servers list if there were new joins
            if (!empty($newJoins)) {
                $session->setInfo(UserColumns::J4R_JOINED_SERVERS, json_encode($joinedServersArray), false);

                // Log overall J4R activity
                UserActivities::add(
                    $userUuid,
                    UserActivitiesTypes::$j4r_check,
                    CloudFlareRealIP::getRealIP(),
                    "J4R Check: {$rewardsGiven} new server joins, +{$rewardsGiven} total rewards given"
                );

                $this->app->getLogger()->info("J4R Check: User {$discordId} joined {$rewardsGiven} new servers and received rewards");
            }

        } catch (Exception $e) {
            $this->app->getLogger()->error('Error checking J4R server joins: ' . $e->getMessage());
        }
    }

    /**
     * Store user's Discord guilds.
     */
    public function storeUserGuilds(Session $session, string $accessToken): void
    {
        try {
            $guilds = $this->discordUtils->getUserGuilds($accessToken);
            $session->setInfo(UserColumns::DISCORD_SERVERS, json_encode(is_array($guilds) ? $guilds : []), false);
        } catch (Exception $e) {
            $this->app->getLogger()->error('Exception while fetching Discord guilds: ' . $e->getMessage());
            $session->setInfo(UserColumns::DISCORD_SERVERS, json_encode([]), false);
        }
    }

    /**
     * Store Discord user data.
     */
    public function storeDiscordData(Session $session, array $userInfo): void
    {
        $session->setInfo(UserColumns::DISCORD_ID, $userInfo['id'], false);
        $session->setInfo(UserColumns::DISCORD_USERNAME, $userInfo['username'], false);
        $session->setInfo(UserColumns::DISCORD_GLOBAL_NAME, $userInfo['global_name'] ?? '', false);
        $session->setInfo(UserColumns::DISCORD_EMAIL, $userInfo['email'] ?? '', false);
        $session->setInfo(UserColumns::DISCORD_LINKED, 'true', false);
    }

    /**
     * Clear Discord user data.
     */
    public function clearDiscordData(Session $session): void
    {
        $session->setInfo(UserColumns::DISCORD_ID, null, false);
        $session->setInfo(UserColumns::DISCORD_USERNAME, null, false);
        $session->setInfo(UserColumns::DISCORD_GLOBAL_NAME, null, false);
        $session->setInfo(UserColumns::DISCORD_EMAIL, null, false);
        $session->setInfo(UserColumns::DISCORD_LINKED, 'false', false);
    }
}

// Discord Link Callback
$router->get('/api/user/auth/callback/discord/link', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $helper = new DiscordOAuthHelper($appInstance);

    if (!$helper->validateConfig()) {
        header('Location: /account?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/link';
    $session = new Session($appInstance);
    global $eventManager;

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord');
            exit;
        }

        // Check if user is already linked
        $isLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
        if ($isLinked === 'true') {
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
        header('Location: /account?error=discord_not_enabled');
        exit;
    }

    $redirectUri = $helper->getSecureBaseUrl() . '/api/user/auth/callback/discord/login';
    global $eventManager;

    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $accessToken = $helper->exchangeCodeForToken($code, $redirectUri);

        if (!$accessToken) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord');
            exit;
        }

        $userInfo = $helper->getUserInfo($accessToken);
        if (!$userInfo) {
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord');
            exit;
        }

        // Check if user exists
        if (!User::exists(UserColumns::DISCORD_ID, $userInfo['id'])) {
            $appInstance->getLogger()->error('Discord login failed for user: ' . $userInfo['id']);
            header('Location: ' . $helper->getSecureBaseUrl() . '/auth/login?error=discord');
            exit;
        }

        $uuid = User::getUUIDFromDiscordID($userInfo['id']);

        // Force join server if enabled
        $helper->forceJoinServer($userInfo['id'], $accessToken);

        // Check J4R server joins for existing user
        $session = new Session($appInstance);
        $helper->checkJ4RServerJoins($userInfo['id'], $accessToken, $session);

        // Perform login
        $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
        $password = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::PASSWORD, true);

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

        // Verify this is the same user
        $sessionDiscordId = $session->getInfo(UserColumns::DISCORD_ID, false);
        if ($sessionDiscordId !== $userInfo['id']) {
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
