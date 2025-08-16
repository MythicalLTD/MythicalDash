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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\Eggs\EggCategories;
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

    $servers = MythicalDash\Hooks\Pterodactyl\Admin\Servers::getAllServers();
    $serversWithInfo = [];
    if (isset($servers['data'])) {
        foreach ($servers['data'] as $server) {
            // Add additional server information
            $serverData = $server;
            if (isset($server['attributes']['node'])) {
                $locationId = MythicalDash\Hooks\Pterodactyl\Admin\Nodes::getLocationIdFromNode((int) $server['attributes']['node']);
                $location = Locations::getLocationByPterodactylLocationId($locationId);
                if ($location) {
                    $serverData['location'] = $location;
                }
            }

            if (isset($server['attributes']['egg'])) {
                $eggId = $server['attributes']['egg'];
                $egg = Eggs::getByPterodactylEggId($eggId);
                if ($egg) {
                    $serverData['service'] = $egg;
                }
            }

            if (isset($server['attributes']['nest'])) {
                $nestId = $server['attributes']['nest'];
                $nest = EggCategories::getByPterodactylNestId($nestId);
                if ($nest) {
                    $serverData['category'] = $nest;
                }
            }

            $serversWithInfo[] = $serverData;
        }
        $servers['data'] = $serversWithInfo;

        $appInstance->OK('Servers fetched successfully', [
            'servers' => $servers,
        ]);
    } else {
        $appInstance->BadRequest('No servers found', ['error_code' => 'NO_SERVERS_FOUND']);
    }
});
