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
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\J4RServers\J4RServers;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\J4REvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Import DiscordOAuthHelper class
require_once __DIR__ . '/../Callback/Discord.php';

/**
 * J4R Check Endpoint.
 * Redirects user to Discord OAuth for fresh token, then checks server joins.
 */
$router->get('/api/user/j4r/check', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    // Check if Discord is enabled
    $discordEnabled = $appInstance->getConfig()->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false');
    if ($discordEnabled !== 'true') {
        $appInstance->BadRequest('Discord integration is not enabled', ['error_code' => 'DISCORD_NOT_ENABLED']);
    }

    // Check if user has Discord linked
    $discordLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
    if ($discordLinked !== 'true') {
        $appInstance->BadRequest('You must link your Discord account first', ['error_code' => 'DISCORD_NOT_LINKED']);
    }

    try {
        // Initialize Discord OAuth helper
        $discordHelper = new DiscordOAuthHelper($appInstance);

        // Validate Discord configuration
        if (!$discordHelper->validateConfig()) {
            $appInstance->BadRequest('Discord integration is not properly configured', ['error_code' => 'DISCORD_CONFIG_ERROR']);
        }

        // Generate Discord OAuth URL for J4R check
        $redirectUri = $discordHelper->getSecureBaseUrl() . '/api/user/auth/callback/discord/j4r';
        $authUrl = $discordHelper->getAuthUrl($redirectUri);

        // Emit event for J4R check initiation
        global $eventManager;
        $eventManager->emit(J4REvent::onJ4RCheckInitiated(), [
            'user_uuid' => $session->getInfo(UserColumns::UUID, false),
            'username' => $session->getInfo(UserColumns::USERNAME, false),
            'discord_linked' => true,
        ]);

        // Redirect to Discord OAuth
        header('Location: ' . $authUrl);
        exit;

    } catch (Exception $e) {
        $appInstance->getLogger()->error('Error during J4R check redirect: ' . $e->getMessage());
        $appInstance->InternalServerError('An error occurred while redirecting to Discord OAuth', ['error_code' => 'J4R_REDIRECT_ERROR']);
    }
});

/**
 * Get J4R status and available servers.
 */
$router->get('/api/user/j4r/status', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $discordEnabled = $appInstance->getConfig()->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false');
    if ($discordEnabled !== 'true') {
        $appInstance->BadRequest('Discord integration is not enabled', ['error_code' => 'DISCORD_NOT_ENABLED']);
    }

    // Check if user has Discord linked
    $discordLinked = $session->getInfo(UserColumns::DISCORD_LINKED, false);
    if ($discordLinked !== 'true') {
        $appInstance->BadRequest('You must link your Discord account first', ['error_code' => 'DISCORD_NOT_LINKED']);
    }

    try {
        // Get available J4R servers
        $j4rServers = J4RServers::getAvailableList();

        // Get user's joined servers
        $joinedServers = $session->getInfo(UserColumns::J4R_JOINED_SERVERS, false);
        $joinedServersArray = !empty($joinedServers) ? json_decode($joinedServers, true) : [];

        if (!is_array($joinedServersArray)) {
            $joinedServersArray = [];
        }

        // Calculate statistics
        $totalServers = count($j4rServers);
        $joinedCount = count($joinedServersArray);
        $availableCount = $totalServers - $joinedCount;
        $totalPossibleCoins = 0;
        $earnedCoins = 0;

        foreach ($j4rServers as $server) {
            $totalPossibleCoins += (int) $server['coins'];
            if (in_array($server['server_id'], $joinedServersArray)) {
                $earnedCoins += (int) $server['coins'];
            }
        }

        // Log user activity
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$j4r_check,
            CloudFlareRealIP::getRealIP(),
            'Viewed J4R status'
        );

        $appInstance->OK('J4R status retrieved successfully', [
            'discord_linked' => true,
            'total_coins' => (int) $session->getInfo(UserColumns::CREDITS, false),
            'total_servers' => $totalServers,
            'joined_servers' => $joinedCount,
            'available_servers' => $availableCount,
            'total_possible_coins' => $totalPossibleCoins,
            'earned_coins' => $earnedCoins,
            'remaining_coins' => $totalPossibleCoins - $earnedCoins,
            'servers' => array_map(function ($server) use ($joinedServersArray) {
                return [
                    'id' => $server['id'],
                    'name' => $server['name'],
                    'server_id' => $server['server_id'],
                    'description' => $server['description'] ?? '',
                    'icon_url' => $server['icon_url'] ?? '',
                    'coins' => (int) $server['coins'],
                    'joined' => in_array($server['server_id'], $joinedServersArray),
                    'invite_code' => $server['invite_code'],
                ];
            }, $j4rServers),
        ]);

    } catch (Exception $e) {
        $appInstance->getLogger()->error('Error getting J4R status: ' . $e->getMessage());
        $appInstance->InternalServerError('An error occurred while retrieving J4R status', ['error_code' => 'J4R_STATUS_ERROR']);
    }
});

/**
 * Get list of all joinable J4R servers.
 */
$router->get('/api/user/j4r/list', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    // Check if Discord is enabled
    $discordEnabled = $appInstance->getConfig()->getDBSetting(ConfigInterface::DISCORD_ENABLED, 'false');
    if ($discordEnabled !== 'true') {
        $appInstance->BadRequest('Discord integration is not enabled', ['error_code' => 'DISCORD_NOT_ENABLED']);
    }

    try {
        // Get all available J4R servers
        $j4rServers = J4RServers::getAvailableList();

        // Get user's joined servers
        $joinedServers = $session->getInfo(UserColumns::J4R_JOINED_SERVERS, false);
        $joinedServersArray = !empty($joinedServers) ? json_decode($joinedServers, true) : [];

        if (!is_array($joinedServersArray)) {
            $joinedServersArray = [];
        }

        // Format servers for response
        $formattedServers = array_map(function ($server) use ($joinedServersArray) {
            return [
                'id' => $server['id'],
                'name' => $server['name'],
                'server_id' => $server['server_id'],
                'description' => $server['description'] ?? '',
                'icon_url' => $server['icon_url'] ?? '',
                'coins' => (int) $server['coins'],
                'joined' => in_array($server['server_id'], $joinedServersArray),
                'invite_code' => $server['invite_code'],
                'invite_url' => 'https://discord.gg/' . $server['invite_code'],
            ];
        }, $j4rServers);

        // Log user activity
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$j4r_check,
            CloudFlareRealIP::getRealIP(),
            'Viewed J4R server list'
        );

        $appInstance->OK('J4R server list retrieved successfully', [
            'servers' => $formattedServers,
            'total_servers' => count($formattedServers),
            'joined_servers' => count($joinedServersArray),
            'available_servers' => count($formattedServers) - count($joinedServersArray),
        ]);

    } catch (Exception $e) {
        $appInstance->getLogger()->error('Error getting J4R server list: ' . $e->getMessage());
        $appInstance->InternalServerError('An error occurred while retrieving J4R server list', ['error_code' => 'J4R_LIST_ERROR']);
    }
});
