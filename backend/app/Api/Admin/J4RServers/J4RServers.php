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

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\J4RServers\J4RServers;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\J4REvent;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get all J4R servers
$router->get('/api/admin/j4r/servers', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_LIST, $session);

    $servers = J4RServers::getList();
    $appInstance->OK('J4R servers retrieved successfully.', [
        'servers' => $servers,
        'count' => J4RServers::getCount(),
        'available_count' => J4RServers::getAvailableCount(),
    ]);
});

// Get available J4R servers only
$router->get('/api/admin/j4r/servers/available', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_LIST, $session);

    $servers = J4RServers::getAvailableList();
    $appInstance->OK('Available J4R servers retrieved successfully.', [
        'servers' => $servers,
        'count' => count($servers),
    ]);
});

// Get J4R servers with pagination
$router->get('/api/admin/j4r/servers/paginated', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_LIST, $session);

    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    // Validate limit and offset
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    if ($offset < 0) {
        $offset = 0;
    }

    $servers = J4RServers::getPaginated($limit, $offset);
    $totalCount = J4RServers::getCount();

    $appInstance->OK('Paginated J4R servers retrieved successfully.', [
        'servers' => $servers,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $totalCount,
            'has_more' => ($offset + $limit) < $totalCount,
        ],
    ]);
});

// Get specific J4R server by ID
$router->get('/api/admin/j4r/servers/(.*)', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_LIST, $session);

    $serverId = (int) $id;
    if ($serverId <= 0) {
        $appInstance->BadRequest('Invalid server ID', ['error_code' => 'INVALID_SERVER_ID']);
    }

    $server = J4RServers::getById($serverId);
    if (!$server) {
        $appInstance->BadRequest('J4R server not found', ['error_code' => 'J4R_SERVER_NOT_FOUND']);
    }

    $appInstance->OK('J4R server retrieved successfully.', [
        'server' => $server,
    ]);
});

// Create new J4R server
$router->post('/api/admin/j4r/servers/create', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_CREATE, $session);

    $name = $appInstance->getPostOrNull('name');
    $inviteCode = $appInstance->getPostOrNull('invite_code');
    $coins = $appInstance->getPostOrNull('coins');
    $serverId = $appInstance->getPostOrNull('server_id');
    $description = $appInstance->getPostOrNull('description');
    $iconUrl = $appInstance->getPostOrNull('icon_url');

    // Validate required fields
    if ($name === null || $inviteCode === null || $coins === null || $serverId === null) {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }

    // Sanitize and validate input
    $name = trim($name);
    $inviteCode = trim($inviteCode);
    $coins = (int) $coins;
    $serverId = $serverId ? trim($serverId) : null;
    $description = $description ? trim($description) : null;
    $iconUrl = $iconUrl ? trim($iconUrl) : null;

    if (empty($name)) {
        $appInstance->BadRequest('Server name cannot be empty', ['error_code' => 'INVALID_NAME']);
    }

    if (empty($inviteCode)) {
        $appInstance->BadRequest('Invite code cannot be empty', ['error_code' => 'INVALID_INVITE_CODE']);
    }

    if (empty($serverId)) {
        $appInstance->BadRequest('Discord server ID cannot be empty', ['error_code' => 'INVALID_SERVER_ID']);
    }

    if ($coins < 0) {
        $appInstance->BadRequest('Coins must be a positive number', ['error_code' => 'INVALID_COINS']);
    }

    // Check if invite code already exists
    if (J4RServers::existsByInviteCode($inviteCode)) {
        $appInstance->BadRequest('Invite code already exists', ['error_code' => 'INVITE_CODE_EXISTS']);
    }

    // Check if server ID already exists (if provided)
    if ($serverId && J4RServers::existsByServerId($serverId)) {
        $appInstance->BadRequest('Server ID already exists', ['error_code' => 'SERVER_ID_EXISTS']);
    }

    $newServerId = J4RServers::create($name, $inviteCode, $coins, $serverId, $description, $iconUrl);

    if ($newServerId === false) {
        $appInstance->InternalServerError('Failed to create J4R server', ['error_code' => 'FAILED_TO_CREATE_SERVER']);
    }

    // Emit event for server creation
    global $eventManager;
    $eventManager->emit(J4REvent::onJ4RServerCreated(), [
        'server_id' => $newServerId,
        'name' => $name,
        'invite_code' => $inviteCode,
        'coins' => $coins,
        'discord_server_id' => $serverId,
        'description' => $description,
        'icon_url' => $iconUrl,
        'created_by' => $session->getInfo(UserColumns::USERNAME, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$j4r_server_create,
        CloudFlareRealIP::getRealIP(),
        "Created J4R server: $name (ID: $newServerId)"
    );

    $server = J4RServers::getById($newServerId);
    $appInstance->OK('J4R server created successfully.', [
        'server' => $server,
    ]);
});

// Update existing J4R server
$router->post('/api/admin/j4r/servers/(.*)/update', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_EDIT, $session);

    $serverId = (int) $id;
    if ($serverId <= 0) {
        $appInstance->BadRequest('Invalid server ID', ['error_code' => 'INVALID_SERVER_ID']);
    }

    // Check if server exists
    if (!J4RServers::exists($serverId)) {
        $appInstance->BadRequest('J4R server not found', ['error_code' => 'J4R_SERVER_NOT_FOUND']);
    }

    $name = $appInstance->getPostOrNull('name');
    $inviteCode = $appInstance->getPostOrNull('invite_code');
    $coins = $appInstance->getPostOrNull('coins');
    $discordServerId = $appInstance->getPostOrNull('server_id');
    $description = $appInstance->getPostOrNull('description');
    $iconUrl = $appInstance->getPostOrNull('icon_url');

    // Validate required fields
    if ($name === null || $inviteCode === null || $coins === null || $discordServerId === null) {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }

    // Sanitize and validate input
    $name = trim($name);
    $inviteCode = trim($inviteCode);
    $coins = (int) $coins;
    $discordServerId = $discordServerId ? trim($discordServerId) : null;
    $description = $description ? trim($description) : null;
    $iconUrl = $iconUrl ? trim($iconUrl) : null;

    if (empty($name)) {
        $appInstance->BadRequest('Server name cannot be empty', ['error_code' => 'INVALID_NAME']);
    }

    if (empty($inviteCode)) {
        $appInstance->BadRequest('Invite code cannot be empty', ['error_code' => 'INVALID_INVITE_CODE']);
    }

    if (empty($discordServerId)) {
        $appInstance->BadRequest('Discord server ID cannot be empty', ['error_code' => 'INVALID_SERVER_ID']);
    }

    if ($coins < 0) {
        $appInstance->BadRequest('Coins must be a positive number', ['error_code' => 'INVALID_COINS']);
    }

    // Check if invite code already exists for a different server
    $existingServer = J4RServers::getByInviteCode($inviteCode);
    if ($existingServer && (int) $existingServer['id'] !== $serverId) {
        $appInstance->BadRequest('Invite code already exists', ['error_code' => 'INVITE_CODE_EXISTS']);
    }

    // Check if server ID already exists for a different server (if provided)
    if ($discordServerId) {
        $existingServerByDiscordId = J4RServers::getByServerId($discordServerId);
        if ($existingServerByDiscordId && (int) $existingServerByDiscordId['id'] !== $serverId) {
            $appInstance->BadRequest('Server ID already exists', ['error_code' => 'SERVER_ID_EXISTS']);
        }
    }

    $result = J4RServers::update($serverId, $name, $inviteCode, $coins, $discordServerId, $description, $iconUrl);

    if (!$result) {
        $appInstance->InternalServerError('Failed to update J4R server', ['error_code' => 'FAILED_TO_UPDATE_SERVER']);
    }

    // Emit event for server update
    global $eventManager;
    $eventManager->emit(J4REvent::onJ4RServerUpdated(), [
        'server_id' => $serverId,
        'name' => $name,
        'invite_code' => $inviteCode,
        'coins' => $coins,
        'discord_server_id' => $discordServerId,
        'description' => $description,
        'icon_url' => $iconUrl,
        'updated_by' => $session->getInfo(UserColumns::USERNAME, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$j4r_server_update,
        CloudFlareRealIP::getRealIP(),
        "Updated J4R server: $name (ID: $serverId)"
    );

    $server = J4RServers::getById($serverId);
    $appInstance->OK('J4R server updated successfully.', [
        'server' => $server,
    ]);
});

// Delete J4R server
$router->post('/api/admin/j4r/servers/(.*)/delete', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_DELETE, $session);

    $serverId = (int) $id;
    if ($serverId <= 0) {
        $appInstance->BadRequest('Invalid server ID', ['error_code' => 'INVALID_SERVER_ID']);
    }

    // Check if server exists
    if (!J4RServers::exists($serverId)) {
        $appInstance->BadRequest('J4R server not found', ['error_code' => 'J4R_SERVER_NOT_FOUND']);
    }

    // Get server details before deletion for logging
    $server = J4RServers::getById($serverId);
    $serverName = $server['name'] ?? 'Unknown';

    $result = J4RServers::delete($serverId);

    if (!$result) {
        $appInstance->InternalServerError('Failed to delete J4R server', ['error_code' => 'FAILED_TO_DELETE_SERVER']);
    }

    // Emit event for server deletion
    global $eventManager;
    $eventManager->emit(J4REvent::onJ4RServerDeleted(), [
        'server_id' => $serverId,
        'name' => $serverName,
        'invite_code' => $server['invite_code'] ?? '',
        'coins' => $server['coins'] ?? 0,
        'discord_server_id' => $server['server_id'] ?? '',
        'description' => $server['description'] ?? '',
        'icon_url' => $server['icon_url'] ?? '',
        'deleted_by' => $session->getInfo(UserColumns::USERNAME, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$j4r_server_delete,
        CloudFlareRealIP::getRealIP(),
        "Deleted J4R server: $serverName (ID: $serverId)"
    );

    $appInstance->OK('J4R server deleted successfully.', [
        'id' => $serverId,
    ]);
});

// Lock J4R server
$router->post('/api/admin/j4r/servers/(.*)/lock', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_EDIT, $session);

    $serverId = (int) $id;
    if ($serverId <= 0) {
        $appInstance->BadRequest('Invalid server ID', ['error_code' => 'INVALID_SERVER_ID']);
    }

    // Check if server exists
    if (!J4RServers::exists($serverId)) {
        $appInstance->BadRequest('J4R server not found', ['error_code' => 'J4R_SERVER_NOT_FOUND']);
    }

    // Get server details for logging
    $server = J4RServers::getById($serverId);
    $serverName = $server['name'] ?? 'Unknown';

    $result = J4RServers::lock($serverId);

    if (!$result) {
        $appInstance->InternalServerError('Failed to lock J4R server', ['error_code' => 'FAILED_TO_LOCK_SERVER']);
    }

    // Emit event for server lock
    global $eventManager;
    $eventManager->emit(J4REvent::onJ4RServerLocked(), [
        'server_id' => $serverId,
        'name' => $serverName,
        'locked_by' => $session->getInfo(UserColumns::USERNAME, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$j4r_server_lock,
        CloudFlareRealIP::getRealIP(),
        "Locked J4R server: $serverName (ID: $serverId)"
    );

    $server = J4RServers::getById($serverId);
    $appInstance->OK('J4R server locked successfully.', [
        'server' => $server,
    ]);
});

// Unlock J4R server
$router->post('/api/admin/j4r/servers/(.*)/unlock', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_EDIT, $session);

    $serverId = (int) $id;
    if ($serverId <= 0) {
        $appInstance->BadRequest('Invalid server ID', ['error_code' => 'INVALID_SERVER_ID']);
    }

    // Check if server exists
    if (!J4RServers::exists($serverId)) {
        $appInstance->BadRequest('J4R server not found', ['error_code' => 'J4R_SERVER_NOT_FOUND']);
    }

    // Get server details for logging
    $server = J4RServers::getById($serverId);
    $serverName = $server['name'] ?? 'Unknown';

    $result = J4RServers::unlock($serverId);

    if (!$result) {
        $appInstance->InternalServerError('Failed to unlock J4R server', ['error_code' => 'FAILED_TO_UNLOCK_SERVER']);
    }

    // Emit event for server unlock
    global $eventManager;
    $eventManager->emit(J4REvent::onJ4RServerUnlocked(), [
        'server_id' => $serverId,
        'name' => $serverName,
        'unlocked_by' => $session->getInfo(UserColumns::USERNAME, false),
    ]);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$j4r_server_unlock,
        CloudFlareRealIP::getRealIP(),
        "Unlocked J4R server: $serverName (ID: $serverId)"
    );

    $server = J4RServers::getById($serverId);
    $appInstance->OK('J4R server unlocked successfully.', [
        'server' => $server,
    ]);
});

// Get J4R server statistics
$router->get('/api/admin/j4r/servers/stats', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_J4R_SERVERS_LIST, $session);

    $totalCount = J4RServers::getCount();
    $availableCount = J4RServers::getAvailableCount();
    $lockedCount = $totalCount - $availableCount;

    $appInstance->OK('J4R server statistics retrieved successfully.', [
        'stats' => [
            'total' => $totalCount,
            'available' => $availableCount,
            'locked' => $lockedCount,
        ],
    ]);
});
