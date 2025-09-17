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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\ServerEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->post('/api/admin/servers/toggle-suspend/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVERS_EDIT, $session);
    if (MythicalDash\Hooks\Pterodactyl\Admin\Servers::serverExists($id)) {
        $serverInfo = MythicalDash\Hooks\Pterodactyl\Admin\Servers::getServerPterodactylDetails($id);
        $suspended = $serverInfo['attributes']['suspended'];
        if ($suspended) {
            MythicalDash\Hooks\Pterodactyl\Admin\Servers::performUnsuspendServer($id);
            $eventManager->emit(ServerEvent::onServerRemoveSuspend(), [
                'server' => $serverInfo,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$server_remove_suspend,
                CloudFlareRealIP::getRealIP(),
                "Unsuspended server $id"
            );
            $appInstance->OK('Server unsuspended successfully', [
                'server' => $serverInfo,
            ]);
        } else {
            MythicalDash\Hooks\Pterodactyl\Admin\Servers::performSuspendServer($id);
            $eventManager->emit(ServerEvent::onServerSuspend(), [
                'server' => $serverInfo,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$server_suspend,
                CloudFlareRealIP::getRealIP(),
                "Suspended server $id"
            );
            $appInstance->OK('Server suspended successfully', [
                'server' => $serverInfo,
            ]);
        }
    } else {
        $appInstance->BadRequest('Server not found', ['error_code' => 'SERVER_NOT_FOUND']);
    }

});

$router->post('/api/admin/servers/delete/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVERS_DELETE, $session);
    if (MythicalDash\Hooks\Pterodactyl\Admin\Servers::serverExists((int) $id)) {
        MythicalDash\Hooks\Pterodactyl\Admin\Servers::deletePterodactylServer((int) $id);
        MythicalDash\Chat\Servers\Server::deleteServerByPterodactylId((int) $id);
        global $eventManager;
        $eventManager->emit(ServerEvent::onServerDeleted(), [
            'server' => $id,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$server_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted server $id"
        );
        $appInstance->OK('Server deleted successfully', []);
    } else {
        $appInstance->BadRequest('Server not found', ['error_code' => 'SERVER_NOT_FOUND']);
    }

});

$router->get('/api/admin/servers/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVERS_LIST, $session);

    // Pagination params
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;

    if ($page < 1) {
        $page = 1;
    }

    $maxLimit = 100;
    if ($limit < 1) {
        $limit = 20;
    } elseif ($limit > $maxLimit) {
        $limit = $maxLimit;
    }

    $servers = MythicalDash\Hooks\Pterodactyl\Admin\Servers::getAllServers($page, $limit);
    $serversWithInfo = [];
    if (isset($servers['data'])) {
        $allServers = $servers['data'];
        foreach ($allServers as $server) {
            // Only include essential data for the frontend
            $serverData = [
                'object' => $server['object'],
                'attributes' => [
                    'id' => $server['attributes']['id'],
                    'name' => $server['attributes']['name'],
                    'suspended' => $server['attributes']['suspended'],
                    'created_at' => $server['attributes']['created_at'],
                    'limits' => [
                        'memory' => $server['attributes']['limits']['memory'],
                        'cpu' => $server['attributes']['limits']['cpu'],
                        'disk' => $server['attributes']['limits']['disk'],
                    ],
                ],
            ];

            // Add location info (only name needed)
            if (isset($server['attributes']['node'])) {
                $locationId = MythicalDash\Hooks\Pterodactyl\Admin\Nodes::getLocationIdFromNode((int) $server['attributes']['node']);
                $location = Locations::getLocationByPterodactylLocationId($locationId);
                if ($location && isset($location['id']) && isset($location['name'])) {
                    $serverData['location'] = [
                        'id' => $location['id'],
                        'name' => $location['name'],
                    ];
                }
            }

            // Add service info (only name needed)
            if (isset($server['attributes']['egg'])) {
                $eggId = $server['attributes']['egg'];
                $egg = Eggs::getByPterodactylEggId($eggId);
                if ($egg && isset($egg['id']) && isset($egg['name'])) {
                    $serverData['service'] = [
                        'id' => $egg['id'],
                        'name' => $egg['name'],
                    ];
                }
            }

            $serversWithInfo[] = $serverData;
        }
        $servers['data'] = $serversWithInfo;

        // Build pagination info from Pterodactyl API response if available
        $paginationMeta = $servers['meta']['pagination'] ?? null;
        if (is_array($paginationMeta)) {
            $currentPage = (int) ($paginationMeta['current_page'] ?? $page);
            $perPage = (int) ($paginationMeta['per_page'] ?? $limit);
            $totalCount = (int) ($paginationMeta['total'] ?? 0);
            $totalPages = (int) ($paginationMeta['total_pages'] ?? (($perPage > 0) ? (int) ceil($totalCount / $perPage) : 0));
        } else {
            $currentPage = $page;
            $perPage = $limit;
            $totalCount = is_array($serversWithInfo) ? count($serversWithInfo) : 0;
            $totalPages = ($perPage > 0) ? (int) ceil($totalCount / $perPage) : 0;
        }

        $appInstance->OK('Servers fetched successfully', [
            'servers' => $servers,
            'pagination' => [
                'page' => $currentPage,
                'limit' => $perPage,
                'total' => $totalCount,
                'pages' => $totalPages,
                'has_more' => $currentPage < $totalPages,
            ],
        ]);
    } else {
        $appInstance->BadRequest('No servers found', ['error_code' => 'NO_SERVERS_FOUND']);
    }
});
