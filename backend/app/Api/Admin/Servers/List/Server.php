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
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\ServerEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->post('/api/admin/servers/toggle-suspend/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', extraContent: ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/servers/delete/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (MythicalDash\Hooks\Pterodactyl\Admin\Servers::serverExists($id)) {
            MythicalDash\Hooks\Pterodactyl\Admin\Servers::deletePterodactylServer($id);
            MythicalDash\Chat\Servers\Server::deleteServerByPterodactylId($id);
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
    } else {
        $appInstance->Unauthorized('Unauthorized', extraContent: ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/servers/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {

        $servers = MythicalDash\Hooks\Pterodactyl\Admin\Servers::getAllServers();
        $serversWithInfo = [];
        foreach ($servers['data'] as $server) {
            // Add additional server information
            $serverData = $server;

            $locationId = MythicalDash\Hooks\Pterodactyl\Admin\Nodes::getLocationIdFromNode((int) $server['attributes']['node']);
            $location = Locations::getLocationByPterodactylLocationId($locationId);
            $serverData['location'] = $location;

            $eggId = $server['attributes']['egg'];
            $egg = Eggs::getByPterodactylEggId($eggId);
            $serverData['service'] = $egg;

            $nestId = $server['attributes']['nest'];
            $nest = EggCategories::getByPterodactylNestId($nestId);
            $serverData['category'] = $nest;

            $serversWithInfo[] = $serverData;
        }
        $servers['data'] = $serversWithInfo;

        $appInstance->OK('Servers fetched successfully', [
            'servers' => $servers,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', extraContent: ['error_code' => 'INVALID_SESSION']);
    }
});
