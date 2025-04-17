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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\LocationEvent;

$router->get('/api/admin/locations/pterodactyl', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $locations = MythicalDash\Hooks\Pterodactyl\Admin\Locations::getLocations();

        $appInstance->OK('Pterodactyl api locations', [
            'locations' => $locations,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/locations', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $locations = Locations::getLocations();

        $appInstance->OK('Locations', [
            'locations' => $locations,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/locations/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_location_create,
        CloudFlareRealIP::getRealIP()
    );

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {

        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['pterodactyl_location_id']) && isset($_POST['node_ip']) && isset($_POST['status']) && isset($_POST['slots'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pterodactyl_location_id = $_POST['pterodactyl_location_id'];
            $node_ip = $_POST['node_ip'];
            $status = $_POST['status'];
            $slots = (int) $_POST['slots'];

            $status_list = ['online', 'offline', 'maintenance'];
            if (!in_array($status, $status_list)) {
                $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

                return;
            }

            if ($name == '' || $description == '' || $pterodactyl_location_id == '' || $node_ip == '' || $status == '' || $slots == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

                return;
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

            $id = Locations::create($name, $description, $pterodactyl_location_id, $node_ip, $status, $slots);
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
                    'id' => $id,
                ],
            ]);
        } else {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/locations/(.*)/update', function ($id): void {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['node_ip']) && isset($_POST['status']) && isset($_POST['slots'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $node_ip = $_POST['node_ip'];
            $status = $_POST['status'];
            $slots = (int) $_POST['slots'];
            $status_list = ['online', 'offline', 'maintenance'];
            if (!in_array($status, $status_list)) {
                $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

                return;
            }

            if ($name == '' || $description == '' || $node_ip == '' || $status == '' || $slots == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

                return;
            }

            if (!Locations::exists($id)) {
                $appInstance->BadRequest('Location not found', ['error_code' => 'ERROR_LOCATION_NOT_FOUND']);

                return;
            }

            $updated = Locations::update($id, $name, $description, $node_ip, $status, $slots);
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
                    'id' => $id,
                ],
            ]);
        } else {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/locations/(.*)/delete', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    global $eventManager;

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    }
});
