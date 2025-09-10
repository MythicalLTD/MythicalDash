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
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\LocationEvent;

$router->get('/api/admin/locations/pterodactyl', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_LOCATIONS_LIST, $session);
    $locations = MythicalDash\Hooks\Pterodactyl\Admin\Locations::getLocations();

    $appInstance->OK('Pterodactyl api locations', [
        'locations' => $locations,
    ]);
});

$router->get('/api/admin/locations', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_LOCATIONS_LIST, $session);
    $locations = Locations::getLocations();

    $appInstance->OK('Locations', [
        'locations' => $locations,
    ]);
});

$router->post('/api/admin/locations/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_LOCATIONS_CREATE, $session);
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_location_create,
        CloudFlareRealIP::getRealIP()
    );

    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['pterodactyl_location_id']) && isset($_POST['node_ip']) && isset($_POST['status']) && isset($_POST['slots']) && isset($_POST['image_id'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $pterodactyl_location_id = $_POST['pterodactyl_location_id'];
        $node_ip = $_POST['node_ip'];
        $status = $_POST['status'];
        $slots = (int) $_POST['slots'];
        $image_id = $_POST['image_id'];
        $vipOnly = $_POST['vip_only'] ?? 'false';

        $status_list = ['online', 'offline', 'maintenance'];
        if (!in_array($status, $status_list)) {
            $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

            return;
        }

        if ($name == '' || $description == '' || $pterodactyl_location_id == '' || $node_ip == '' || $status == '' || $slots == '' || $image_id == '') {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

            return;
        }

        if ($image_id != 'null') {
            $image_id = (int) $image_id;
        } else {
            $image_id = null;
        }

        // Validate vip_only value
        if ($vipOnly !== 'true' && $vipOnly !== 'false') {
            $vipOnly = 'false';
        }

        $pterodactyl_location_id = intval($pterodactyl_location_id);
        $node_ip = strval($node_ip);

        if (Locations::existsByPterodactylLocationId($pterodactyl_location_id)) {
            $appInstance->BadRequest('Location already exists', ['error_code' => 'ERROR_LOCATION_ALREADY_EXISTS']);

            return;
        }

        if (!MythicalDash\Hooks\Pterodactyl\Admin\Locations::doesLocationExist($pterodactyl_location_id)) {
            $appInstance->BadRequest('Invalid Pterodactyl location ID', ['error_code' => 'ERROR_INVALID_PTERODACTYL_LOCATION_ID']);

            return;
        }

        $id = Locations::create($name, $description, $pterodactyl_location_id, $node_ip, $status, $slots, $image_id, $vipOnly);
        if ($id == 0) {
            $appInstance->BadRequest('Failed to create location', ['error_code' => 'ERROR_FAILED_TO_CREATE_LOCATION']);

            return;
        }

        $eventManager->emit(LocationEvent::onLocationCreated(), [$id]);

        $appInstance->OK('Location created', [
            'location' => [
                'name' => $name,
                'description' => $description,
                'pterodactyl_location_id' => $pterodactyl_location_id,
                'node_ip' => $node_ip,
                'status' => $status,
                'slots' => $slots,
                'image_id' => $image_id,
                'vip_only' => $vipOnly,
                'id' => $id,
            ],
        ]);
    } else {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }
});

$router->post('/api/admin/locations/(.*)/update', function ($id): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_LOCATIONS_EDIT, $session);
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['node_ip']) && isset($_POST['status']) && isset($_POST['slots']) && isset($_POST['image_id'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $node_ip = $_POST['node_ip'];
        $status = $_POST['status'];
        $slots = (int) $_POST['slots'];
        $image_id = $_POST['image_id'];
        $vipOnly = $_POST['vip_only'] ?? 'false';

        $status_list = ['online', 'offline', 'maintenance'];
        if (!in_array($status, $status_list)) {
            $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

            return;
        }

        if ($name == '' || $description == '' || $node_ip == '' || $status == '' || $slots == '' || $image_id == '') {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

            return;
        }

        if ($image_id != 'null') {
            $image_id = (int) $image_id;
        } else {
            $image_id = null;
        }

        // Validate vip_only value
        if ($vipOnly !== 'true' && $vipOnly !== 'false') {
            $vipOnly = 'false';
        }

        if (!Locations::exists($id)) {
            $appInstance->BadRequest('Location not found', ['error_code' => 'ERROR_LOCATION_NOT_FOUND']);

            return;
        }

        $updated = Locations::update($id, $name, $description, $node_ip, $status, $slots, $image_id, $vipOnly);
        if (!$updated) {
            $appInstance->BadRequest('Failed to update location', ['error_code' => 'ERROR_FAILED_TO_UPDATE_LOCATION']);

            return;
        }

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_location_update,
            CloudFlareRealIP::getRealIP()
        );

        $eventManager->emit(LocationEvent::onLocationUpdated(), [$id]);

        $appInstance->OK('Location updated', [
            'location' => [
                'name' => $name,
                'description' => $description,
                'node_ip' => $node_ip,
                'status' => $status,
                'slots' => $slots,
                'image_id' => $image_id,
                'vip_only' => $vipOnly,
                'id' => $id,
            ],
        ]);
    } else {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }
});

$router->post('/api/admin/locations/(.*)/delete', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    global $eventManager;

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_LOCATIONS_DELETE, $session);
    if (!Locations::exists($id)) {
        $appInstance->BadRequest('Location not found', ['error_code' => 'ERROR_LOCATION_NOT_FOUND']);
    }

    $eventManager->emit(LocationEvent::onLocationDeleted(), [$id]);

    $deleted = Locations::delete($id);
    if (!$deleted) {
        $appInstance->BadRequest('Failed to delete location', ['error_code' => 'ERROR_FAILED_TO_DELETE_LOCATION']);
    }

    // TODO: Make sure there are not servers on this location before you delete it!

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_location_delete,
        CloudFlareRealIP::getRealIP()
    );

    $appInstance->OK('Location deleted', [
        'location' => [
            'id' => $id,
        ],
    ]);
});
