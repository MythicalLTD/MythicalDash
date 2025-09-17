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
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\Servers\ServerQueueLogs;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\ServerQueueEvent;

$router->get('/api/admin/server-queue', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_LIST, $session);

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

    $paginated = ServerQueue::getPaginated($page, $limit);
    // Normalize possible structures: {items,total} or {data, meta: {total, per_page, current_page}}
    $items = [];
    $total = 0;
    if (is_array($paginated)) {
        $items = $paginated['items'] ?? $paginated['data'] ?? [];
        if (isset($paginated['total'])) {
            $total = (int) $paginated['total'];
        } elseif (isset($paginated['meta']) && is_array($paginated['meta'])) {
            $total = (int) ($paginated['meta']['total'] ?? 0);
            // Prefer backend-provided paging info if present
            if (isset($paginated['meta']['per_page']) && (int) $paginated['meta']['per_page'] > 0) {
                $limit = (int) $paginated['meta']['per_page'];
            }
            if (isset($paginated['meta']['current_page']) && (int) $paginated['meta']['current_page'] > 0) {
                $page = (int) $paginated['meta']['current_page'];
            }
        }
    }

    $serverQueue = is_array($items) ? $items : [];

    foreach ($serverQueue as $key => $value) {
        $serverQueue[$key]['location'] = Locations::get($value['location']);
        $serverQueue[$key]['nest'] = EggCategories::get($value['nest']);
        $serverQueue[$key]['egg'] = Eggs::getById($value['egg']);
        $serverQueue[$key]['user'] = User::getInfoArray(
            User::getTokenFromUUID($value['user']),
            [
                UserColumns::UUID,
                UserColumns::USERNAME,
                UserColumns::EMAIL,
                UserColumns::ROLE_ID,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::AVATAR,
            ],
            [
                UserColumns::PASSWORD,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
            ]
        );
    }

    $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

    $appInstance->OK('Server queue retrieved successfully.', [
        'server_queue' => $serverQueue,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $totalPages,
            'has_more' => $page < $totalPages,
        ],
    ]);
});

$router->post('/api/admin/server-queue/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_CREATE, $session);
    if (
        isset($_POST['name']) && !empty($_POST['name'])
        && isset($_POST['description']) && !empty($_POST['description'])
        && isset($_POST['ram']) && !empty($_POST['ram'])
        && isset($_POST['disk']) && !empty($_POST['disk'])
        && isset($_POST['cpu']) && !empty($_POST['cpu'])
        && isset($_POST['ports']) && !empty($_POST['ports'])
        && isset($_POST['databases']) && !empty($_POST['databases'])
        && isset($_POST['backups']) && !empty($_POST['backups'])
        && isset($_POST['location']) && !empty($_POST['location'])
        && isset($_POST['user']) && !empty($_POST['user'])
        && isset($_POST['nest']) && !empty($_POST['nest'])
        && isset($_POST['egg']) && !empty($_POST['egg'])
    ) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $ram = (int) $_POST['ram'];
        $disk = (int) $_POST['disk'];
        $cpu = (int) $_POST['cpu'];
        $ports = (int) $_POST['ports'];

        // Validate and sanitize numeric inputs
        if (!is_numeric($_POST['ram']) || ($ram = (int) $_POST['ram']) < 0) {
            $appInstance->BadRequest('RAM cannot be a negative number', ['error_code' => 'RAM_NEGATIVE']);

            return;
        }

        // Validate that disk is not negative
        if (!is_numeric($_POST['disk']) || ($disk = (int) $_POST['disk']) < 0) {
            $appInstance->BadRequest('Disk cannot be a negative number', ['error_code' => 'DISK_NEGATIVE']);

            return;
        }

        // Validate that CPU is not negative
        if (!is_numeric($_POST['cpu']) || ($cpu = (int) $_POST['cpu']) < 0) {
            $appInstance->BadRequest('CPU cannot be a negative number', ['error_code' => 'CPU_NEGATIVE']);

            return;
        }

        // Validate that ports is not negative
        if (!is_numeric($_POST['ports']) || ($ports = (int) $_POST['ports']) < 0) {
            $appInstance->BadRequest('Ports cannot be a negative number', ['error_code' => 'PORTS_NEGATIVE']);

            return;
        }
        $databases = (int) $_POST['databases'];
        $backups = (int) $_POST['backups'];
        $location = (int) $_POST['location'];

        if (!is_numeric($_POST['databases']) || ($databases = (int) $_POST['databases']) < 0) {
            $appInstance->BadRequest('Databases cannot be a negative number', ['error_code' => 'DATABASES_NEGATIVE']);

            return;
        }

        if (!is_numeric($_POST['backups']) || ($backups = (int) $_POST['backups']) < 0) {
            $appInstance->BadRequest('Backups cannot be a negative number', ['error_code' => 'BACKUPS_NEGATIVE']);

            return;
        }

        $location = (int) $_POST['location'];
        $user = $_POST['user'];
        $nest = (int) $_POST['nest'];
        $egg = (int) $_POST['egg'];

        if (User::exists(UserColumns::UUID, $user)) {
            if (EggCategories::exists($nest)) {
                if (Eggs::exists($egg)) {
                    if (Locations::exists($location)) {
                        $sv = ServerQueue::create($name, $description, $ram, $disk, $cpu, $ports, $databases, $backups, $location, $user, $nest, $egg);
                        if ($sv == false) {
                            $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
                        }

                        if ($sv == 0) {
                            $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
                        }

                        global $eventManager;
                        $eventManager->emit(ServerQueueEvent::onServerQueueCreated(), [
                            'id' => $sv,
                            'name' => $name,
                            'description' => $description,
                            'ram' => $ram,
                            'disk' => $disk,
                            'cpu' => $cpu,
                            'ports' => $ports,
                            'databases' => $databases,
                            'backups' => $backups,
                            'location' => $location,
                            'user' => $user,
                            'nest' => $nest,
                            'egg' => $egg,
                            'status' => 'pending',
                        ]);
                        UserActivities::add(
                            $session->getInfo(UserColumns::UUID, false),
                            UserActivitiesTypes::$admin_server_queue_create,
                            CloudFlareRealIP::getRealIP(),
                            "Created server queue item $sv"
                        );

                        $appInstance->OK('Server queue item created successfully.', ['error_code' => 'SERVER_QUEUE_ITEM_CREATED', 'server_queue_item' => $sv]);
                    } else {
                        $appInstance->BadRequest('Invalid location', ['error_code' => 'INVALID_LOCATION']);
                    }
                } else {
                    $appInstance->BadRequest('Invalid egg', ['error_code' => 'INVALID_EGG']);
                }
            } else {
                $appInstance->BadRequest('Invalid nest', ['error_code' => 'INVALID_NEST']);
            }
        } else {
            $appInstance->BadRequest('Invalid user', ['error_code' => 'INVALID_USER']);
        }
    } else {
        $appInstance->BadRequest('Invalid request parameters', ['error_code' => 'INVALID_REQUEST_PARAMETERS']);
    }

});
$router->get('/api/admin/server-queue/logs', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_LOGS_VIEW, $session);
    $logs = ServerQueueLogs::getAll();
    $appInstance->OK('Server queue logs retrieved successfully.', ['logs' => $logs]);
});

$router->post('/api/admin/server-queue/(.*)/update-status', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVERS_EDIT, $session);
    $serverQueueExists = ServerQueue::exists($id);
    if ($serverQueueExists) {
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $status = $_POST['status'];
            if ($status == 'pending' || $status == 'building' || $status == 'failed') {
                ServerQueue::updateStatus($id, $status);
                global $eventManager;
                $eventManager->emit(ServerQueueEvent::onServerQueueUpdated(), [
                    'id' => $id,
                    'status' => $status,
                ]);
                UserActivities::add(
                    $session->getInfo(UserColumns::UUID, false),
                    UserActivitiesTypes::$admin_server_queue_update,
                    CloudFlareRealIP::getRealIP(),
                    "Updated server queue item $id"
                );
                $appInstance->OK('Server queue status updated successfully.', ['error_code' => 'SERVER_QUEUE_STATUS_UPDATED']);
            } else {
                $appInstance->BadRequest('Invalid status', ['error_code' => 'INVALID_STATUS']);
            }
        } else {
            $appInstance->BadRequest('Invalid status', ['error_code' => 'INVALID_STATUS']);
        }
    } else {
        $appInstance->NotFound('Server queue not found', ['error_code' => 'SERVER_QUEUE_NOT_FOUND']);
    }

});

$router->post('/api/admin/server-queue/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_DELETE, $session);
    $serverQueueExists = ServerQueue::exists($id);
    if ($serverQueueExists) {
        // Block deletion for completed items
        $item = ServerQueue::getById((int) $id);
        if (isset($item['status']) && $item['status'] === 'completed') {
            $appInstance->BadRequest('Cannot delete a completed queue item.', ['error_code' => 'SERVER_QUEUE_COMPLETED_CANNOT_DELETE']);
        }
        global $eventManager;
        $eventManager->emit(ServerQueueEvent::onServerQueueDeleted(), [
            'id' => $id,
        ]);
        ServerQueue::delete($id);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_server_queue_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted server queue item $id"
        );
        $appInstance->OK('Server queue deleted successfully.', ['error_code' => 'SERVER_QUEUE_DELETED']);
    } else {
        $appInstance->NotFound('Server queue not found', ['error_code' => 'SERVER_QUEUE_NOT_FOUND']);
    }

});

$router->get('/api/admin/server-queue/stats', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_LIST, $session);
    $stats = ServerQueue::getStats();
    $appInstance->OK('Server queue stats retrieved successfully.', ['stats' => $stats]);
});

$router->get('/api/admin/server-queue/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SERVER_QUEUE_LIST, $session);
    $serverQueueExists = ServerQueue::exists((int) $id);
    if ($serverQueueExists) {
        $serverQueue = ServerQueue::getById((int) $id);
        $serverQueue['location'] = Locations::get($serverQueue['location']);
        $serverQueue['nest'] = EggCategories::get($serverQueue['nest']);
        $serverQueue['egg'] = Eggs::getById($serverQueue['egg']);
        $serverQueue['user'] = User::getInfoArray(
            User::getTokenFromUUID($serverQueue['user']),
            [
                UserColumns::UUID,
                UserColumns::USERNAME,
                UserColumns::EMAIL,
                UserColumns::ROLE_ID,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::AVATAR,
            ],
            [
                UserColumns::PASSWORD,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
            ]
        );
        $appInstance->OK('Server queue retrieved successfully.', [
            'server_queue' => $serverQueue,
        ]);
    } else {
        $appInstance->NotFound('Server queue not found', ['error_code' => 'SERVER_QUEUE_NOT_FOUND']);
    }
});
