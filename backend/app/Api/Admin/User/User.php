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
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Mails\MailList;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Nodes;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\UserEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;

$router->get('/api/admin/users', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_LIST, $session);

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    $search = isset($_GET['search']) ? trim((string) $_GET['search']) : null;

    if ($page < 1) {
        $page = 1;
    }
    $maxLimit = 100;
    if ($limit < 1) {
        $limit = 20;
    } elseif ($limit > $maxLimit) {
        $limit = $maxLimit;
    }

    $result = User::getPaginatedWithSearch(['id', 'username', 'email', 'avatar', 'pterodactyl_user_id', 'role', 'last_seen', 'uuid'], [], $page, $limit, $search);

    $total = (int) ($result['total'] ?? 0);
    $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

    $appInstance->OK('Users data retrieved successfully.', [
        'users' => $result['items'] ?? [],
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $totalPages,
            'has_more' => $page < $totalPages,
        ],
    ]);

});

$router->get('/api/admin/user/(.*)/info', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_LIST, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId, true)) {
        $targetUser = User::getTokenFromUUID($userId);
        $userInfo = User::getInfoArray(
            $targetUser,
            [
                // Basic Info
                UserColumns::ID,
                UserColumns::USERNAME,
                UserColumns::PASSWORD,
                UserColumns::EMAIL,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::AVATAR,
                UserColumns::CREDITS,
                UserColumns::UUID,
                UserColumns::PTERODACTYL_USER_ID,
                UserColumns::ACCOUNT_TOKEN,
                UserColumns::ROLE_ID,
                UserColumns::FIRST_IP,
                UserColumns::LAST_IP,
                UserColumns::BANNED,
                UserColumns::SUPPORT_PIN,
                UserColumns::VERIFIED,
                UserColumns::TWO_FA_ENABLED,
                UserColumns::TWO_FA_KEY,
                UserColumns::TWO_FA_BLOCKED,
                UserColumns::DELETED,
                UserColumns::LAST_SEEN,
                UserColumns::FIRST_SEEN,
                UserColumns::BACKGROUND,

                // Resource Limits
                UserColumns::DISK_LIMIT,
                UserColumns::MEMORY_LIMIT,
                UserColumns::CPU_LIMIT,
                UserColumns::SERVER_LIMIT,
                UserColumns::BACKUP_LIMIT,
                UserColumns::DATABASE_LIMIT,
                UserColumns::ALLOCATION_LIMIT,

                // AFK
                UserColumns::MINUTES_AFK,
                UserColumns::LAST_SEEN_AFK,

                // Discord
                UserColumns::DISCORD_ID,
                UserColumns::DISCORD_USERNAME,
                UserColumns::DISCORD_GLOBAL_NAME,
                UserColumns::DISCORD_EMAIL,
                UserColumns::DISCORD_LINKED,
                UserColumns::DISCORD_SERVERS,
                UserColumns::J4R_JOINED_SERVERS,

                // GitHub
                UserColumns::GITHUB_ID,
                UserColumns::GITHUB_USERNAME,
                UserColumns::GITHUB_EMAIL,
                UserColumns::GITHUB_LINKED,

                // Image Hosting
                UserColumns::IMAGE_HOSTING_ENABLED,
                UserColumns::IMAGE_HOSTING_EMBED_ENABLED,
                UserColumns::IMAGE_HOSTING_EMBED_TITLE,
                UserColumns::IMAGE_HOSTING_EMBED_DESCRIPTION,
                UserColumns::IMAGE_HOSTING_EMBED_COLOR,
                UserColumns::IMAGE_HOSTING_EMBED_IMAGE,
                UserColumns::IMAGE_HOSTING_EMBED_THUMBNAIL,
                UserColumns::IMAGE_HOSTING_EMBED_URL,
                UserColumns::IMAGE_HOSTING_EMBED_AUTHOR_NAME,
                UserColumns::IMAGE_HOSTING_UPLOAD_KEY,
            ],
            [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::PASSWORD,
            ]
        );

        // Ensure discord_servers and j4r_joined_servers are decoded from JSON if present
        if (isset($userInfo[UserColumns::DISCORD_SERVERS]) && is_string($userInfo[UserColumns::DISCORD_SERVERS])) {
            $decoded = json_decode($userInfo[UserColumns::DISCORD_SERVERS], true);
            $userInfo[UserColumns::DISCORD_SERVERS] = is_array($decoded) ? $decoded : [];
        }
        if (isset($userInfo[UserColumns::J4R_JOINED_SERVERS]) && is_string($userInfo[UserColumns::J4R_JOINED_SERVERS])) {
            $decoded = json_decode($userInfo[UserColumns::J4R_JOINED_SERVERS], true);
            $userInfo[UserColumns::J4R_JOINED_SERVERS] = is_array($decoded) ? $decoded : [];
        }

        $activity = UserActivities::get($userInfo[UserColumns::UUID], 1000);
        $mails = MailList::getByUserUuid($userInfo[UserColumns::UUID]);

        // Get user servers with full information (same logic as Session.php)
        $pterodactylUserId = $userInfo[UserColumns::PTERODACTYL_USER_ID];
        $servers = Servers::getUserServersList($pterodactylUserId);
        foreach ($servers as &$server) {
            $nodeId = $server['node'] ?? 0;
            $locationId = Nodes::getLocationIdFromNode((int) $nodeId);
            if ($locationId != 0) {
                $location = Locations::getLocationByPterodactylLocationId((int) $locationId);
            } else {
                $location = [];
            }
            $server['location'] = $location;

            $eggId = $server['egg'] ?? 0;
            if ($eggId != 0) {
                $egg = Eggs::getByPterodactylEggId((int) $eggId);
            } else {
                $egg = [];
            }
            $server['service'] = $egg[0] ?? [];

            $nestId = $server['nest'] ?? 0;
            if ($nestId != 0) {
                $nest = MythicalDash\Chat\Eggs\EggCategories::getByPterodactylNestId((int) $nestId);
            } else {
                $nest = [];
            }
            $server['category'] = $nest[0] ?? [];
        }
        unset($server); // Unset the reference to avoid potential issues

        // Get servers in queue
        $serversInQ = ServerQueue::getByUser($userInfo[UserColumns::UUID], [], false);
        foreach ($serversInQ as &$server) {
            // Get location data
            $server['location'] = Locations::get((int) $server['location']);

            // Get egg data and set it as service to match active servers structure
            $egg = Eggs::getById((int) $server['egg']);
            $server['service'] = $egg[0] ?? [];

            // Get category data
            $server['category'] = MythicalDash\Chat\Eggs\EggCategories::get((int) $server['nest']);

            // Set limits to match active servers structure
            $server['limits'] = [
                'memory' => $server['ram'],
                'disk' => $server['disk'],
                'cpu' => $server['cpu'],
            ];

            // Set feature limits to match active servers structure
            $server['feature_limits'] = [
                'databases' => $server['databases'],
                'allocations' => $server['ports'],
                'backups' => $server['backups'],
            ];
        }
        unset($server); // Unset the reference to avoid potential issues

        $appInstance->OK('User info retrieved successfully.', [
            'user' => $userInfo,
            'activity' => $activity,
            'mails' => $mails,
            'servers' => $servers,
            'servers_queue' => $serversInQ,
        ]);
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->post('/api/admin/user/(.*)/update', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_EDIT, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId)) {
        if (isset($_POST['column']) && isset($_POST['value'])) {
            $column = $_POST['column'];
            $value = $_POST['value'];
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_user_update,
                CloudFlareRealIP::getRealIP(),
                "Updated user $userId"
            );

            global $eventManager;
            $eventManager->emit(UserEvent::onUserUpdate(), [
                'user' => $userId,
            ]);
            $token = User::getTokenFromUUID($userId);

            // Define which columns should be encrypted
            $encryptedColumns = [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::PASSWORD,
            ];
            /**
             * Inject the banned column to suspend/unsuspend all servers of the user.
             */
            if ($column == UserColumns::BANNED) {
                if ($value == 'YES') {
                    $serversQ = ServerQueue::getByUser($userId, [], false);
                    foreach ($serversQ as $server) {
                        ServerQueue::updateStatus((int) $server['id'], 'failed');
                    }
                    $servers = Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));
                    foreach ($servers as $server) {
                        Servers::performSuspendServer($server['id']);
                    }
                } else {
                    $servers = Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));
                    foreach ($servers as $server) {
                        Servers::performUnsuspendServer($server['id']);
                    }
                }
                $appInstance->OK('User updated successfully.', ['error_code' => 'USER_UPDATED']);
            }

            // Use the built-in is_encrypted flag instead of manual encryption
            $isEncrypted = in_array($column, $encryptedColumns);

            if (User::updateInfo($token, $column, $value, $isEncrypted)) {
                $appInstance->OK('User updated successfully.', ['error_code' => 'USER_UPDATED']);
            } else {
                $appInstance->InternalServerError('Failed to update user', ['error_code' => 'USER_UPDATE_FAILED']);
            }
        } else {
            $appInstance->BadRequest('Column, value and encrypted are required', ['error_code' => 'COLUMN_VALUE_ENCRYPTED_REQUIRED']);
        }
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->post('/api/admin/user/(.*)/ban', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_EDIT, $session);

    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }

    if (!isset($_POST['status'])) {
        $appInstance->BadRequest('Status is required', ['error_code' => 'STATUS_REQUIRED']);
    }

    $status = strtoupper((string) $_POST['status']);
    if ($status !== 'YES' && $status !== 'NO') {
        $appInstance->BadRequest('Status must be YES or NO', ['error_code' => 'STATUS_INVALID']);
    }

    if (User::exists(UserColumns::UUID, $userId)) {
        // Audit log
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_user_update,
            CloudFlareRealIP::getRealIP(),
            ($status === 'YES' ? 'Banned' : 'Unbanned') . " user $userId"
        );

        // Trigger event
        global $eventManager;
        $eventManager->emit(UserEvent::onUserUpdate(), [
            'user' => $userId,
        ]);

        $token = User::getTokenFromUUID($userId);

        // When banning, fail queued servers and suspend active ones. When unbanning, unsuspend active ones.
        if ($status === 'YES') {
            $serversQ = ServerQueue::getByUser($userId, [], false);
            foreach ($serversQ as $server) {
                ServerQueue::updateStatus((int) $server['id'], 'failed');
            }
            $servers = Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));
            foreach ($servers as $server) {
                Servers::performSuspendServer($server['id']);
            }
        } else {
            $servers = Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));
            foreach ($servers as $server) {
                Servers::performUnsuspendServer($server['id']);
            }
        }

        // Persist the banned flag
        if (User::updateInfo($token, UserColumns::BANNED, $status, false)) {
            $appInstance->OK('Ban status updated successfully.', [
                'error_code' => 'USER_BAN_STATUS_UPDATED',
                'status' => $status,
            ]);
        } else {
            $appInstance->InternalServerError('Failed to update ban status', ['error_code' => 'USER_BAN_STATUS_FAILED']);
        }
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }
});

$router->post('/api/admin/user/(.*)/delete', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_DELETE, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId)) {
        global $eventManager;
        $eventManager->emit(UserEvent::onUserDelete(), [
            'user' => $userId,
        ]);
        $token = User::getTokenFromUUID($userId);
        User::delete($token);
        foreach (Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false)) as $server) {
            Servers::deletePterodactylServer($server['id']);
        }
        $pteroUsers = new UsersResource(
            $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
            $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
        );
        $pteroUsers->deleteUser(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_user_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted user $userId"
        );

        $appInstance->OK('User deleted successfully.', ['error_code' => 'USER_DELETED']);
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->get('/api/admin/user/support-pin/(.*)', function ($supportPin): void {
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ANNOUNCEMENTS_LIST, $session);
    if (empty($supportPin)) {
        $appInstance->BadRequest('Support pin is required', ['error_code' => 'SUPPORT_PIN_REQUIRED']);
    }
    if (User::checkSupportPin($supportPin)) {
        $uuid = User::convertPinToUUID($supportPin);
        $appInstance->OK('Support pin entered successfully.', ['error_code' => 'SUPPORT_PIN_ENTERED', 'uuid' => $uuid]);
    } else {
        $appInstance->BadRequest('Support pin is incorrect', ['error_code' => 'SUPPORT_PIN_INCORRECT']);
    }

});
