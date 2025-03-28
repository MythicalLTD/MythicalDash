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

$router->post('/api/admin/locations/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {

        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['pterodactyl_location_id']) && isset($_POST['node_ip']) && isset($_POST['status'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pterodactyl_location_id = $_POST['pterodactyl_location_id'];
            $node_ip = $_POST['node_ip'];
            $status = $_POST['status'];

            $status_list = ['online', 'offline', 'maintenance'];
            if (!in_array($status, $status_list)) {
                $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

                return;
            }

            if ($name == '' || $description == '' || $pterodactyl_location_id == '' || $node_ip == '' || $status == '') {
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

            $id = Locations::create($name, $description, $pterodactyl_location_id, $node_ip, $status);
            if ($id == 0) {
                $appInstance->BadRequest('Failed to create location', ['error_code' => 'ERROR_FAILED_TO_CREATE_LOCATION']);

                return;
            }

            $appInstance->OK('Location created', [
                'location' => [
                    'name' => $name,
                    'description' => $description,
                    'pterodactyl_location_id' => $pterodactyl_location_id,
                    'node_ip' => $node_ip,
                    'status' => $status,
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

$router->post('/api/admin/locations/update', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['node_ip']) && isset($_POST['status'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $node_ip = $_POST['node_ip'];
            $status = $_POST['status'];

            $status_list = ['online', 'offline', 'maintenance'];
            if (!in_array($status, $status_list)) {
                $appInstance->BadRequest('Invalid status', ['error_code' => 'ERROR_INVALID_STATUS']);

                return;
            }

            if ($name == '' || $description == '' || $node_ip == '' || $status == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

                return;
            }

            if (!Locations::exists($id)) {
                $appInstance->BadRequest('Location not found', ['error_code' => 'ERROR_LOCATION_NOT_FOUND']);

                return;
            }

            $updated = Locations::update($id, $name, $description, $node_ip, $status);
            if (!$updated) {
                $appInstance->BadRequest('Failed to update location', ['error_code' => 'ERROR_FAILED_TO_UPDATE_LOCATION']);

                return;
            }

            $appInstance->OK('Location updated', [
                'location' => [
                    'name' => $name,
                    'description' => $description,
                    'node_ip' => $node_ip,
                    'status' => $status,
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
