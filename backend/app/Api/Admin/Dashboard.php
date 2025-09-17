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
use MythicalDash\Hooks\GitHub;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Middleware\PermissionMiddleware;

global $eventManager;

$router->get('/api/admin', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DASHBOARD_VIEW, $session);
    try {
        $github_dataInterface = new GitHub();
        $github_data = $github_dataInterface->getRepoData();
        $github_release = $github_dataInterface->getReleases();
        $activity = UserActivities::getAll(150);
        $userCount = Database::getTableRowCount('mythicaldash_users', true);
        $addonsCount = Database::getTableRowCount('mythicaldash_addons', true);
        $rolesCount = Database::getTableRowCount('mythicaldash_roles', true);
        $locationsCount = Database::getTableRowCount('mythicaldash_locations', true);
        $ticketsCount = Database::getTableRowCount('mythicaldash_tickets', true);
        $eggsCount = Database::getTableRowCount('mythicaldash_eggs', true);
        $departmentsCount = Database::getTableRowCount('mythicaldash_departments', true);
        $announcementsCount = Database::getTableRowCount('mythicaldash_announcements', true);
        $serverQueueCount = Database::getTableRowCount('mythicaldash_servers_queue', true);
        $mailTemplatesCount = Database::getTableRowCount('mythicaldash_mail_templates', true);
        $settingsCount = Database::getTableRowCount('mythicaldash_settings', true);
        $redeemCodesCount = Database::getTableRowCount('mythicaldash_redeem_codes', true);
        $serversCount = Database::getTableRowCount('mythicaldash_servers', true);
        $pluginsCount = Database::getTableRowCount('mythicaldash_addons', true);
        $imagesCount = Database::getTableRowCount('mythicaldash_image_db', true);
        $redirectLinksCount = Database::getTableRowCount('mythicaldash_redirect_links', true);
        $imageReportsCount = Database::getTableRowCount('mythicaldash_image_reports', true);
        $j4rServersCount = Database::getTableRowCount('mythicaldash_j4r_servers', true);
        $DashboardLogs = $appInstance->getLogger()->getLogs(false);
        $WebServerLogs = $appInstance->getWebServerLogger()->getLogs(true);
        // Limit logs to 250 lines (keep most recent)
        if (count($DashboardLogs) > 250) {
            $DashboardLogs = array_slice($DashboardLogs, -250);
        }
        if (count($WebServerLogs) > 250) {
            $WebServerLogs = array_slice($WebServerLogs, -250);
        }

        // Collect analytics data for at-a-glance insights
        $analytics = getAnalyticsData();

        $logs = array_merge($DashboardLogs, $WebServerLogs);
        $appInstance->OK('Dashboard data retrieved successfully.', [
            'core' => [
                'github_data' => $github_data,
                'logs' => $logs,
                'github_release' => $github_release,
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
                'images_count' => $imagesCount,
                'redirect_links_count' => $redirectLinksCount,
                'image_reports_count' => $imageReportsCount,
                'j4r_servers_count' => $j4rServersCount,
            ],
            'etc' => [
                'activity' => $activity,
            ],
            'analytics' => $analytics,
        ]);

    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'SERVICE_UNAVAILABLE']);
    }

});

/**
 * Collect analytics data for the dashboard.
 *
 * @return array The analytics data
 */
function getAnalyticsData(): array
{
    // Get new users registered in the last 24 hours
    $newUsersQuery = 'SELECT COUNT(*) as count FROM mythicaldash_users WHERE first_seen >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
    $newUsers = Database::rawQuery($newUsersQuery)[0]['count'] ?? 0;

    // Get new servers created in the last 24 hours
    $newServersQuery = 'SELECT COUNT(*) as count FROM mythicaldash_servers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
    $newServers = Database::rawQuery($newServersQuery)[0]['count'] ?? 0;

    // Get total server count
    $totalServersQuery = 'SELECT COUNT(*) as count FROM mythicaldash_servers';
    $totalServers = Database::rawQuery($totalServersQuery)[0]['count'] ?? 0;

    // Get new tickets in the last 24 hours
    $newTicketsQuery = 'SELECT COUNT(*) as count FROM mythicaldash_tickets WHERE date >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
    $newTickets = Database::rawQuery($newTicketsQuery)[0]['count'] ?? 0;

    // Get open tickets
    $openTicketsQuery = "SELECT COUNT(*) as count FROM mythicaldash_tickets WHERE status != 'closed'";
    $openTickets = Database::rawQuery($openTicketsQuery)[0]['count'] ?? 0;

    // Get usage by resource type
    $resourceQuery = 'SELECT 
        SUM(ram) as total_memory,
        SUM(disk) as total_disk,
        COUNT(*) as server_count
        FROM mythicaldash_servers_queue';
    $resourceStats = Database::rawQuery($resourceQuery)[0] ?? ['total_memory' => 0, 'total_disk' => 0, 'server_count' => 0];

    // Get user activity by hour (last 24 hours)
    $hourlyActivityQuery = 'SELECT 
        HOUR(date) as hour,
        COUNT(*) as count
        FROM mythicaldash_users_activities
        WHERE date >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        GROUP BY HOUR(date)
        ORDER BY hour ASC';
    $hourlyActivity = Database::rawQuery($hourlyActivityQuery);

    // Format hourly data for chart
    $activityByHour = array_fill(0, 24, 0);
    foreach ($hourlyActivity as $row) {
        $activityByHour[(int) $row['hour']] = (int) $row['count'];
    }

    // Get recent server deployment stats
    $serverQueuesQuery = "SELECT COUNT(*) as count FROM mythicaldash_servers_queue WHERE status = 'pending'";
    $pendingServers = Database::rawQuery($serverQueuesQuery)[0]['count'] ?? 0;

    return [
        'new_users_24h' => $newUsers,
        'new_servers_24h' => $newServers,
        'total_servers' => $totalServers,
        'pending_servers' => $pendingServers,
        'new_tickets_24h' => $newTickets,
        'open_tickets' => $openTickets,
        'resource_usage' => [
            'memory' => (int) $resourceStats['total_memory'],
            'disk' => (int) $resourceStats['total_disk'],
            'server_count' => (int) $resourceStats['server_count'],
        ],
        'hourly_activity' => $activityByHour,
    ];
}
