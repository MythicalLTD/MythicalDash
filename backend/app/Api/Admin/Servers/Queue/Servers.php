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
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\Servers\ServerQueueLogs;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\ServerQueueEvent;

$router->get('/api/admin/server-queue', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $serverQueue = ServerQueue::getAll();
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
        $appInstance->OK('Server queue retrieved successfully.', [
            'server_queue' => $serverQueue,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', extraContent: ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/server-queue/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
            $databases = (int) $_POST['databases'];
            $backups = (int) $_POST['backups'];
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
$router->get('/api/admin/server-queue/logs', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $logs = ServerQueueLogs::getAll();
        $appInstance->OK('Server queue logs retrieved successfully.', ['logs' => $logs]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/server-queue/(.*)/update-status', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }

});

$router->post('/api/admin/server-queue/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $serverQueueExists = ServerQueue::exists($id);
        if ($serverQueueExists) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/server-queue/stats', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $stats = ServerQueue::getStats();
        $appInstance->OK('Server queue stats retrieved successfully.', ['stats' => $stats]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/server-queue/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
