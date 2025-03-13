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
use MythicalDash\Hooks\GitHub;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;

global $eventManager;

$router->get('/api/admin', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        try {
            $github_data = new GitHub();
            $github_data = $github_data->getRepoData();
            $activity = UserActivities::getAll(150);
            $userCount = Database::getTableRowCount('mythicaldash_users');
            $addonsCount = Database::getTableRowCount('mythicaldash_addons');
            $departmentsCount = Database::getTableRowCount('mythicaldash_departments');
            $invoicesCount = Database::getTableRowCount('mythicaldash_invoices');
            $rolesCount = Database::getTableRowCount('mythicaldash_roles');
            $servicesCount = Database::getTableRowCount('mythicaldash_services');
            $ticketsCount = Database::getTableRowCount('mythicaldash_tickets');

            $appInstance->OK('Dashboard data retrieved successfully.', [
                'core' => [
                    'github_data' => $github_data,
                ],
                'count' => [
                    'user_count' => $userCount,
                    'addons_count' => $addonsCount,
                    'departments_count' => $departmentsCount,
                    'invoices_count' => $invoicesCount,
                    'roles_count' => $rolesCount,
                    'services_count' => $servicesCount,
                    'tickets_count' => $ticketsCount,
                ],
                'etc' => [
                    'activity' => $activity,
                ],
            ]);

        } catch (Exception $e) {
            $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'SERVICE_UNAVAILABLE']);
        }
    } else {
        $appInstance->Unauthorized('You do not have permission to access this endpoint.', ['error_code' => 'NO_PERMISSION']);
    }

});
