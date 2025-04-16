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
            $rolesCount = Database::getTableRowCount('mythicaldash_roles');
            $locationsCount = Database::getTableRowCount('mythicaldash_locations');
            $ticketsCount = Database::getTableRowCount('mythicaldash_tickets');
            $eggsCount = Database::getTableRowCount('mythicaldash_eggs');
            $departmentsCount = Database::getTableRowCount('mythicaldash_departments');
            $announcementsCount = Database::getTableRowCount('mythicaldash_announcements');
            $serverQueueCount = Database::getTableRowCount('mythicaldash_servers_queue');
            $mailTemplatesCount = Database::getTableRowCount('mythicaldash_mail_templates');
            $settingsCount = Database::getTableRowCount('mythicaldash_settings', false);
            $redeemCodesCount = Database::getTableRowCount('mythicaldash_redeem_codes');
            $serversCount = Database::getTableRowCount('mythicaldash_servers');
            $pluginsCount = Database::getTableRowCount('mythicaldash_addons');

            $appInstance->OK('Dashboard data retrieved successfully.', [
                'core' => [
                    'github_data' => $github_data,
                ],
                'count' => [
                    'user_count' => $userCount,
                    'addons_count' => $addonsCount,
                    'locations_count' => $locationsCount,
                    'roles_count' => $rolesCount,
                    'tickets_count' => $ticketsCount,
                    'eggs_count' => $eggsCount,
                    'departments_count' => $departmentsCount,
                    'announcements_count' => $announcementsCount,
                    'server_queue_count' => $serverQueueCount,
                    'mail_templates_count' => $mailTemplatesCount,
                    'settings_count' => $settingsCount,
                    'redeem_codes_count' => $redeemCodesCount,
                    'servers_count' => $serversCount,
                    'plugins_count' => $pluginsCount,
                ],
                'etc' => [
                    'activity' => $activity,
                ],
            ]);

        } catch (Exception $e) {
            $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'SERVICE_UNAVAILABLE']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }

});
