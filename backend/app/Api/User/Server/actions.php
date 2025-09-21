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
use MythicalDash\Chat\Images\Image;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Plugins\Events\Events\ServerEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\ServerQueueEvent;

// Update server
$router->post('/api/user/server/(.*)/update', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);

    // Get the server first to check ownership
    // Get server details
    $server = Servers::getServerPterodactylDetails((int) $id);

    if (!$server) {
        $appInstance->Forbidden('Server not found or you do not have permission to access it', ['error_code' => 'SERVER_NOT_FOUND']);

        return;
    }

    $pterodactylUserId = $session->getInfo(UserColumns::PTERODACTYL_USER_ID, false);
    $owner = $server['attributes']['user'];
    if ($owner != $pterodactylUserId) {
        $appInstance->Forbidden('You do not have permission to access this server', ['error_code' => 'FORBIDDEN']);

        return;
    }

    // Add additional server information
    $locationId = $server['attributes']['relationships']['location']['attributes']['id'];
    $location = Locations::getLocationByPterodactylLocationId($locationId);
    $server['location'] = $location;

    $eggId = $server['attributes']['relationships']['egg']['attributes']['id'];
    $egg = Eggs::getByPterodactylEggId($eggId);
    $server['service'] = $egg;

    $nestId = $server['attributes']['relationships']['nest']['attributes']['id'];
    $nest = EggCategories::getByPterodactylNestId($nestId);
    $server['category'] = $nest;
    $allocation = $server['attributes']['allocation'];

    // Validate input data
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = $_POST['name'];
    } else {
        $appInstance->BadRequest('Name is required', ['error_code' => 'NAME_REQUIRED']);

        return;
    }
    if (isset($_POST['description']) && !empty($_POST['description'])) {
        $description = $_POST['description'];
    } else {
        $appInstance->BadRequest('Description is required', ['error_code' => 'DESCRIPTION_REQUIRED']);

        return;
    }
    // Validate memory field
    if (!isset($_POST['memory'])) {
        $appInstance->BadRequest('Memory is required', ['error_code' => 'MEMORY_REQUIRED']);

        return;
    }

    if (!is_numeric($_POST['memory'])) {
        $appInstance->BadRequest('Memory must be a numeric value', ['error_code' => 'MEMORY_INVALID_TYPE']);

        return;
    }

    $memory = (int) $_POST['memory'];

    // Validate that memory is not negative
    if ($memory < 0) {
        $appInstance->BadRequest('Memory cannot be a negative number', ['error_code' => 'MEMORY_NEGATIVE']);

        return;
    }

    if (isset($_POST['cpu']) && !empty($_POST['cpu'])) {
        $cpu = (int) $_POST['cpu'];
    } else {
        $appInstance->BadRequest('CPU is required', ['error_code' => 'CPU_REQUIRED']);

        return;
    }

    // Validate that CPU is not negative
    if ($cpu < 0) {
        $appInstance->BadRequest('CPU cannot be a negative number', ['error_code' => 'CPU_NEGATIVE']);

        return;
    }
    if (isset($_POST['disk']) && !empty($_POST['disk'])) {
        $disk = (int) $_POST['disk'];
    } else {
        $appInstance->BadRequest('Disk is required', ['error_code' => 'DISK_REQUIRED']);

        return;
    }

    // Validate that disk is not negative
    if ($disk < 0) {
        $appInstance->BadRequest('Disk cannot be a negative number', ['error_code' => 'DISK_NEGATIVE']);

        return;
    }
    if (isset($_POST['databases']) && !empty($_POST['databases'])) {
        $databases = max(0, intval($_POST['databases']));
    } else {
        // Servers may not require databases
        $databases = 0;
    }
    // Validate that databases is not negative
    if ($databases < 0) {
        $appInstance->BadRequest('Databases cannot be a negative number', ['error_code' => 'DATABASES_NEGATIVE']);

        return;
    }
    // Validate backups field
    if (!isset($_POST['backups'])) {
        $appInstance->BadRequest('Backups is required', ['error_code' => 'BACKUPS_REQUIRED']);

        return;
    }

    if (!is_numeric($_POST['backups'])) {
        $appInstance->BadRequest('Backups must be a numeric value', ['error_code' => 'BACKUPS_INVALID_TYPE']);

        return;
    }

    $backups = (int) $_POST['backups'];

    // Validate that backups is not negative
    if ($backups < 0) {
        $appInstance->BadRequest('Backups cannot be a negative number', ['error_code' => 'BACKUPS_NEGATIVE']);

        return;
    }
    if (isset($_POST['allocations']) && !empty($_POST['allocations'])) {
        $allocations = $_POST['allocations'];
    } else {
        $appInstance->BadRequest('Allocations is required', ['error_code' => 'ALLOCATIONS_REQUIRED']);

        return;
    }

    // Validate that allocations is not negative
    if ($allocations < 0) {
        $appInstance->BadRequest('Allocations cannot be a negative number', ['error_code' => 'ALLOCATIONS_NEGATIVE']);

        return;
    }

    // Validate required fields
    if (empty($name)) {
        $appInstance->BadRequest('Name is required', ['error_code' => 'NAME_REQUIRED']);

        return;
    }

    // Validate resource limits
    if ($memory < 256) {
        $appInstance->BadRequest('Memory must be at least 256MB', ['error_code' => 'MEMORY_MINIMUM']);

        return;
    }

    if ($cpu < 5) {
        $appInstance->BadRequest('CPU must be at least 5%', ['error_code' => 'CPU_MINIMUM']);

        return;
    }

    if ($disk < 256) {
        $appInstance->BadRequest('Disk must be at least 256MB', ['error_code' => 'DISK_MINIMUM']);

        return;
    }

    if ($allocations < 1) {
        $appInstance->BadRequest('Allocations must be at least 1', ['error_code' => 'ALLOCATIONS_MINIMUM']);

        return;
    }

    $resources = Servers::getUserTotalResourcesUsage($pterodactylUserId);
    $available_resources = User::getInfoArray($accountToken, [
        UserColumns::MEMORY_LIMIT,
        UserColumns::DISK_LIMIT,
        UserColumns::CPU_LIMIT,
        UserColumns::DATABASE_LIMIT,
        UserColumns::BACKUP_LIMIT,
        UserColumns::ALLOCATION_LIMIT,
        UserColumns::SERVER_LIMIT,
    ], []);

    // Get current server resources to calculate the difference
    $currentServerResources = [
        'memory' => $server['attributes']['limits']['memory'],
        'disk' => $server['attributes']['limits']['disk'],
        'cpu' => $server['attributes']['limits']['cpu'],
        'databases' => $server['attributes']['feature_limits']['databases'],
        'backups' => $server['attributes']['feature_limits']['backups'],
        'allocations' => $server['attributes']['feature_limits']['allocations'],
    ];

    // Calculate resource difference (new - current)
    $resourceDifference = [
        'memory' => $memory - $currentServerResources['memory'],
        'disk' => $disk - $currentServerResources['disk'],
        'cpu' => $cpu - $currentServerResources['cpu'],
        'databases' => $databases - $currentServerResources['databases'],
        'backups' => $backups - $currentServerResources['backups'],
        'allocations' => $allocations - $currentServerResources['allocations'],
    ];

    // Check if the update would exceed available resources
    if ($resourceDifference['memory'] > 0) {
        $freeMemory = $available_resources[UserColumns::MEMORY_LIMIT] - $resources['memory'];
        if ($resourceDifference['memory'] > $freeMemory) {
            $appInstance->BadRequest('This update would exceed your maximum memory limit', [
                'error_code' => 'MAX_MEMORY_LIMIT',
                'required' => $available_resources[UserColumns::MEMORY_LIMIT],
                'current_usage' => $resources['memory'],
                'attempted_to_add' => $resourceDifference['memory'],
            ]);

            return;
        }
    }

    if ($resourceDifference['disk'] > 0) {
        $freeDisk = $available_resources[UserColumns::DISK_LIMIT] - $resources['disk'];
        if ($resourceDifference['disk'] > $freeDisk) {
            $appInstance->BadRequest('This update would exceed your maximum disk limit', [
                'error_code' => 'MAX_DISK_LIMIT',
                'required' => $available_resources[UserColumns::DISK_LIMIT],
                'current_usage' => $resources['disk'],
                'attempted_to_add' => $resourceDifference['disk'],
            ]);

            return;
        }
    }

    if ($resourceDifference['cpu'] > 0) {
        $freeCpu = $available_resources[UserColumns::CPU_LIMIT] - $resources['cpu'];
        if ($resourceDifference['cpu'] > $freeCpu) {
            $appInstance->BadRequest('This update would exceed your maximum CPU limit', [
                'error_code' => 'MAX_CPU_LIMIT',
                'required' => $available_resources[UserColumns::CPU_LIMIT],
                'current_usage' => $resources['cpu'],
                'attempted_to_add' => $resourceDifference['cpu'],
            ]);

            return;
        }
    }

    if ($resourceDifference['databases'] > 0 && $available_resources[UserColumns::DATABASE_LIMIT] >= 0) {
        $freeDatabases = $available_resources[UserColumns::DATABASE_LIMIT] - $resources['databases'];
        if ($resourceDifference['databases'] > $freeDatabases) {
            $appInstance->BadRequest('This update would exceed your maximum databases limit', [
                'error_code' => 'MAX_DATABASES_LIMIT',
                'required' => $available_resources[UserColumns::DATABASE_LIMIT],
                'current_usage' => $resources['databases'],
                'attempted_to_add' => $resourceDifference['databases'],
            ]);

            return;
        }
    }

    if ($resourceDifference['backups'] > 0 && $available_resources[UserColumns::BACKUP_LIMIT] >= 0) {
        $freeBackups = $available_resources[UserColumns::BACKUP_LIMIT] - $resources['backups'];
        if ($resourceDifference['backups'] > $freeBackups) {
            $appInstance->BadRequest('This update would exceed your maximum backups limit', [
                'error_code' => 'MAX_BACKUPS_LIMIT',
                'required' => $available_resources[UserColumns::BACKUP_LIMIT],
                'current_usage' => $resources['backups'],
                'attempted_to_add' => $resourceDifference['backups'],
            ]);

            return;
        }
    }

    if ($resourceDifference['allocations'] > 0) {
        $freeAllocations = $available_resources[UserColumns::ALLOCATION_LIMIT] - $resources['allocations'];
        if ($resourceDifference['allocations'] > $freeAllocations) {
            $appInstance->BadRequest('This update would exceed your maximum allocations limit', [
                'error_code' => 'MAX_ALLOCATIONS_LIMIT',
                'required' => $available_resources[UserColumns::ALLOCATION_LIMIT],
                'current_usage' => $resources['allocations'],
                'attempted_to_add' => $resourceDifference['allocations'],
            ]);

            return;
        }
    }

    // Update server details
    try {
        $updateData = [
            'allocation' => $allocation,
            'memory' => $memory,
            'cpu' => $cpu,
            'swap' => 0,
            'io' => 500,
            'disk' => $disk,
            'feature_limits' => [
                'databases' => $databases,
                'backups' => $backups,
                'allocations' => $allocations,
            ],
        ];

        $details = [
            'name' => $name,
            'user' => $pterodactylUserId,
            'description' => $description,
            'external_id' => '',
        ];
        if (!isset($server['attributes']['id'])) {
            $appInstance->BadRequest('Server not found', ['error_code' => 'SERVER_NOT_FOUND']);

            return;
        }
        $serverId = $server['attributes']['id'];
        $svAw1 = Servers::updatePterodactylServer($serverId, $updateData);
        $svAw2 = Servers::updatePterodactylServerDetails($serverId, $details);
        global $eventManager;
        $eventManager->emit(ServerEvent::onServerUpdated(), [
            'server' => $server,
            'updateData' => $updateData,
            'details' => $details,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$server_update,
            CloudFlareRealIP::getRealIP(),
            "Updated server $serverId"
        );
        $appInstance->OK('Server updated successfully', [
            'build' => $updateData,
            'details' => $details,
            'server' => $server,
            'rsp' => [
                'svAw1' => $svAw1,
                'svAw2' => $svAw2,
            ],
        ]);

    } catch (Exception $e) {
        $appInstance->ServiceUnavailable('Error updating server: ' . $e->getMessage(), ['error_code' => 'FAILED_TO_UPDATE_SERVER']);
    }
});

// Renew server
$router->post('/api/user/server/(.*)/renew', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();

    // Check if server renewal is enabled
    if ($config->getDBSetting(ConfigInterface::SERVER_RENEW_ENABLED, 'false') == 'false') {
        $appInstance->BadRequest('Server renewal is not enabled', ['error_code' => 'SERVER_RENEWAL_NOT_ENABLED']);

        return;
    }

    // Get server details
    $server = Servers::getServerPterodactylDetails((int) $id);
    if (!$server) {
        $appInstance->Forbidden('Server not found or you do not have permission to access it', ['error_code' => 'SERVER_NOT_FOUND']);

        return;
    }

    // Verify ownership
    $pterodactylUserId = $session->getInfo(UserColumns::PTERODACTYL_USER_ID, false);
    $owner = $server['attributes']['user'];
    if ($owner != $pterodactylUserId) {
        $appInstance->Forbidden('You do not have permission to access this server', ['error_code' => 'FORBIDDEN']);

        return;
    }

    // Get server info from database
    if (isset($server['attributes']['id'])) {
        $serverId = $server['attributes']['id'];
        $serverInfoDb = MythicalDash\Chat\Servers\Server::getByPterodactylId((int) $serverId);
        if (!$serverInfoDb) {
            $appInstance->BadRequest('Server not found in database', ['error_code' => 'SERVER_NOT_FOUND_IN_DB']);

            return;
        }
    }

    // Get renewal settings
    $server_renew_cost = (int) $config->getDBSetting(ConfigInterface::SERVER_RENEW_COST, 120);
    $server_renew_days = (int) $config->getDBSetting(ConfigInterface::SERVER_RENEW_DAYS, 30);

    // Validate renewal settings
    if ($server_renew_cost <= 0) {
        $appInstance->BadRequest('Invalid renewal cost configuration', ['error_code' => 'INVALID_RENEWAL_COST']);

        return;
    }

    if ($server_renew_days <= 0) {
        $appInstance->BadRequest('Invalid renewal days configuration', ['error_code' => 'INVALID_RENEWAL_DAYS']);

        return;
    }

    // Check user balance atomically to prevent race conditions
    $creditCheck = $session->checkCreditsAtomic($server_renew_cost);
    if (!$creditCheck['has_sufficient']) {
        $appInstance->BadRequest('You do not have enough credits to renew this server', [
            'error_code' => 'INSUFFICIENT_CREDITS',
            'required' => $server_renew_cost,
            'available' => $creditCheck['current_credits'],
        ]);

        return;
    }

    // Calculate new expiration date
    $currentExpiresAt = strtotime($serverInfoDb['expires_at']);
    if ($currentExpiresAt === false) {
        $appInstance->BadRequest('Invalid server expiration date', ['error_code' => 'INVALID_EXPIRATION_DATE']);

        return;
    }

    $newExpiresAt = $currentExpiresAt + ($server_renew_days * 86400); // Convert days to seconds
    $newExpiresAtFormatted = date('Y-m-d H:i:s', $newExpiresAt);

    try {
        // Update server expiration
        if (!MythicalDash\Chat\Servers\Server::update($serverInfoDb['id'], $newExpiresAt)) {
            throw new Exception('Failed to update server expiration');
        }

        // Remove credits atomically to prevent race conditions
        if (!$session->removeCreditsAtomic($server_renew_cost)) {
            // If removing credits failed, we need to rollback the server expiration update
            // This is a critical error that should be logged
            $appInstance->getLogger()->error('Failed to remove renewal credits atomically for user: ' . $session->getInfo(UserColumns::UUID, false) . ' for server: ' . $serverId);
            $appInstance->BadRequest('Failed to process renewal - credit deduction failed', ['error_code' => 'CREDIT_DEDUCTION_FAILED']);

            return;
        }

        // Log activity
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$server_renew,
            CloudFlareRealIP::getRealIP(),
            "Renewed server $serverId for $server_renew_days days"
        );

        // Emit event
        global $eventManager;
        $eventManager->emit(ServerEvent::onServerRenewed(), [
            'server' => $server,
            'renewal_days' => $server_renew_days,
            'cost' => $server_renew_cost,
            'new_expires_at' => $newExpiresAtFormatted,
        ]);

        // Return success response
        $appInstance->OK('Server renewed successfully', [
            'server' => $serverInfoDb,
            'renewal_details' => [
                'days_added' => $server_renew_days,
                'cost' => $server_renew_cost,
                'new_expires_at' => $newExpiresAtFormatted,
            ],
        ]);

    } catch (Exception $e) {
        $appInstance->ServiceUnavailable('Error renewing server: ' . $e->getMessage(), ['error_code' => 'FAILED_TO_RENEW_SERVER']);
    }
});

// Delete server
$router->post('/api/user/server/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    // Get the server first to check ownership
    $server = Servers::getServerPterodactylDetails((int) $id);

    if (!$server) {
        $appInstance->Forbidden('Server not found or you do not have permission to access it', ['error_code' => 'SERVER_NOT_FOUND']);

        return;
    }

    $pterodactylUserId = $session->getInfo(UserColumns::PTERODACTYL_USER_ID, false);
    $owner = $server['attributes']['user'];
    if ($owner != $pterodactylUserId) {
        $appInstance->Forbidden('You do not have permission to access this server', ['error_code' => 'FORBIDDEN']);

        return;
    }
    $serverId = $server['attributes']['id'];

    try {
        // Delete from Pterodactyl first
        Servers::deletePterodactylServer($serverId, false);

        // Only delete from database after successful Pterodactyl deletion
        if (MythicalDash\Chat\Servers\Server::doesServerExistByPterodactylId($serverId)) {
            MythicalDash\Chat\Servers\Server::deleteServerByPterodactylId($serverId);
        }

        global $eventManager;
        $eventManager->emit(ServerEvent::onServerDeleted(), [
            'server' => $serverId,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$server_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted server $serverId"
        );
        $appInstance->OK('Server deleted successfully', []);
    } catch (Exception $e) {
        $appInstance->ServiceUnavailable('Error deleting server: ' . $e->getMessage(), ['error_code' => 'FAILED_TO_DELETE_SERVER']);
    }
});

$router->get('/api/user/server/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    $locations = Locations::getLocations();
    $categories = EggCategories::getCategories();
    $eggs = Eggs::getAll();

    // Filter locations based on VIP permission
    $hasVipPermission = $session->hasPermission(Permissions::USER_PERMISSION_VIP);
    $locations = array_filter($locations, function ($location) use ($hasVipPermission) {
        // If location is VIP only and user doesn't have VIP permission, exclude it
        if ($location['vip_only'] === 'true' && !$hasVipPermission) {
            return false;
        }

        return true;
    });

    // Filter eggs based on VIP permission
    $eggs = array_filter($eggs, function ($egg) use ($hasVipPermission) {
        // If egg is VIP only and user doesn't have VIP permission, exclude it
        if ($egg['vip_only'] === 'true' && !$hasVipPermission) {
            return false;
        }

        return true;
    });

    // Structure categories with their eggs
    $structuredCategories = array_map(function ($category) use ($eggs) {
        $category['eggs'] = array_filter($eggs, function ($egg) use ($category) {
            return $egg['category'] == $category['id'];
        });

        return $category;
    }, $categories);

    // Filter out categories that have no eggs after VIP filtering
    $structuredCategories = array_filter($structuredCategories, function ($category) {
        return !empty($category['eggs']);
    });

    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);
    $resources = Servers::getUserTotalResourcesUsage($pterodactylUserId, true);
    $available_resources = User::getInfoArray($accountToken, [
        UserColumns::MEMORY_LIMIT,
        UserColumns::DISK_LIMIT,
        UserColumns::CPU_LIMIT,
        UserColumns::DATABASE_LIMIT,
        UserColumns::BACKUP_LIMIT,
        UserColumns::ALLOCATION_LIMIT,
        UserColumns::SERVER_LIMIT,
    ], []);

    $free_resources = [
        'memory' => $available_resources[UserColumns::MEMORY_LIMIT] - $resources['memory'],
        'disk' => $available_resources[UserColumns::DISK_LIMIT] - $resources['disk'],
        'cpu' => $available_resources[UserColumns::CPU_LIMIT] - $resources['cpu'],
        'databases' => $available_resources[UserColumns::DATABASE_LIMIT] - $resources['databases'],
        'backups' => $available_resources[UserColumns::BACKUP_LIMIT] - $resources['backups'],
        'allocations' => $available_resources[UserColumns::ALLOCATION_LIMIT] - $resources['allocations'],
        'servers' => $available_resources[UserColumns::SERVER_LIMIT] - $resources['servers'],
    ];

    $total_resources = [
        'memory' => $available_resources[UserColumns::MEMORY_LIMIT],
        'disk' => $available_resources[UserColumns::DISK_LIMIT],
        'cpu' => $available_resources[UserColumns::CPU_LIMIT],
        'databases' => $available_resources[UserColumns::DATABASE_LIMIT],
        'backups' => $available_resources[UserColumns::BACKUP_LIMIT],
        'allocations' => $available_resources[UserColumns::ALLOCATION_LIMIT],
        'servers' => $available_resources[UserColumns::SERVER_LIMIT],
    ];

    foreach ($locations as &$location) {
        $location['used_slots'] = Servers::getServerCountByLocation($location['pterodactyl_location_id']);
        $location['image'] = Image::get((int) $location['image_id']);
    }

    foreach ($structuredCategories as &$category) {
        foreach ($category['eggs'] as &$egg) {
            $egg['image'] = Image::get((int) $egg['image_id']);
        }
        $category['image'] = Image::get((int) $category['image_id']);
    }

    $appInstance->OK('Server Creation', [
        'locations' => array_values($locations), // Reset array keys
        'categories' => array_values($structuredCategories), // Reset array keys
        'used_resources' => $resources,
        'total_resources' => $total_resources,
        'free_resources' => $free_resources,
        'has_vip_permission' => $hasVipPermission,
    ]);
});

$router->post('/api/user/server/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $config = $appInstance->getConfig();
    if ($config->getDBSetting(ConfigInterface::ALLOW_SERVERS, 'false') == 'false') {
        $appInstance->BadRequest('Server creation is not allowed', ['error_code' => 'SERVER_CREATION_NOT_ALLOWED']);
        return;
    }
    if ($config->getDBSetting(ConfigInterface::FORCE_DISCORD_LINK, 'false') == 'true') {
        $discordLinked = User::getInfo($accountToken, UserColumns::DISCORD_LINKED, false);
        if ($discordLinked == 'false') {
            $appInstance->BadRequest('Discord account linking is required', ['error_code' => 'DISCORD_LINKING_REQUIRED']);
            return;
        }
    }
    if ($config->getDBSetting(ConfigInterface::FORCE_GITHUB_LINK, 'false') == 'true') {
        $githubLinked = User::getInfo($accountToken, UserColumns::GITHUB_LINKED, false);
        if ($githubLinked == 'false') {
            $appInstance->BadRequest('GitHub account linking is required', ['error_code' => 'GITHUB_LINKING_REQUIRED']);
            return;
        }
    }
    if ($config->getDBSetting(ConfigInterface::FORCE_MAIL_LINK, 'false') == 'true') {
        $emailVerified = User::getInfo($accountToken, UserColumns::VERIFIED, false);
        if ($emailVerified == 'false') {
            $appInstance->BadRequest('Email verification is required', ['error_code' => 'EMAIL_VERIFICATION_REQUIRED']);
            return;
        }
    }
    if (
        !isset($_POST['name'])
        || !isset($_POST['description'])
        || !isset($_POST['location_id'])
        || !isset($_POST['category_id'])
        || !isset($_POST['egg_id'])
        || !isset($_POST['memory'])
        || !isset($_POST['cpu'])
        || !isset($_POST['disk'])
        || !isset($_POST['databases'])
        || !isset($_POST['backups'])
        || !isset($_POST['allocations'])
    ) {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        return;
    }
    if (
        $_POST['name'] == ''
        || $_POST['description'] == ''
        || $_POST['location_id'] == ''
        || $_POST['category_id'] == ''
        || $_POST['egg_id'] == ''
        || $_POST['memory'] == ''
        || $_POST['cpu'] == ''
        || $_POST['disk'] == ''
        || $_POST['databases'] == ''
        || $_POST['backups'] == ''
        || $_POST['allocations'] == ''
    ) {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        return;
    }
    if (strlen($_POST['name']) > 32) {
        $appInstance->BadRequest('Name must be less than 32 characters', ['error_code' => 'NAME_TOO_LONG']);
        return;
    }
    if (strlen($_POST['description']) > 255) {
        $appInstance->BadRequest('Description must be less than 255 characters', ['error_code' => 'DESCRIPTION_TOO_LONG']);
        return;
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $location_id = (int) $_POST['location_id'];
    $category_id = (int) $_POST['category_id'];
    $egg_id = (int) $_POST['egg_id'];
    $memory = (int) $_POST['memory'];
    $cpu = (int) $_POST['cpu'];
    $disk = (int) $_POST['disk'];

    // Validate that memory is not negative
    if ($memory < 0) {
        $appInstance->BadRequest('Memory cannot be a negative number', ['error_code' => 'MEMORY_NEGATIVE']);
        return;
    }

    // Validate that CPU is not negative
    if ($cpu < 0) {
        $appInstance->BadRequest('CPU cannot be a negative number', ['error_code' => 'CPU_NEGATIVE']);
        return;
    }

    // Validate that disk is not negative
    if ($disk < 0) {
        $appInstance->BadRequest('Disk cannot be a negative number', ['error_code' => 'DISK_NEGATIVE']);
        return;
    }
    $databases = (int) $_POST['databases'];
    $backups = (int) $_POST['backups'];
    $allocations = (int) $_POST['allocations'];

    // Validate that databases is not negative
    if ($databases < 0) {
        $appInstance->BadRequest('Databases cannot be a negative number', ['error_code' => 'DATABASES_NEGATIVE']);
        return;
    }

    // Validate that backups is not negative
    if ($backups < 0) {
        $appInstance->BadRequest('Backups cannot be a negative number', ['error_code' => 'BACKUPS_NEGATIVE']);
        return;
    }

    // Validate that allocations is not negative
    if ($allocations < 0) {
        $appInstance->BadRequest('Allocations cannot be a negative number', ['error_code' => 'ALLOCATIONS_NEGATIVE']);
        return;
    }

    if (!Locations::exists($location_id)) {
        $appInstance->BadRequest('Location does not exist', ['error_code' => 'LOCATION_DOES_NOT_EXIST']);
        return;
    }

    if (!EggCategories::exists($category_id)) {
        $appInstance->BadRequest('Category does not exist', ['error_code' => 'CATEGORY_DOES_NOT_EXIST']);
        return;
    }

    if (!Eggs::exists($egg_id)) {
        $appInstance->BadRequest('Egg does not exist', ['error_code' => 'EGG_DOES_NOT_EXIST']);
        return;
    }

    if ($memory < 256) {
        $appInstance->BadRequest('Memory must be at least 256MB', ['error_code' => 'MEMORY_TOO_LOW']);
        return;
    }

    if ($cpu < 5) {
        $appInstance->BadRequest('CPU must be at least 5%', ['error_code' => 'CPU_TOO_LOW']);
        return;
    }

    if ($disk < 256) {
        $appInstance->BadRequest('Disk must be at least 256MB', ['error_code' => 'DISK_TOO_LOW']);
        return;
    }

    if ($allocations < 1) {
        $appInstance->BadRequest('Allocations must be at least 1', ['error_code' => 'ALLOCATIONS_TOO_LOW']);
        return;
    }

    $uuid = User::getInfo($accountToken, UserColumns::UUID, false);
    if (ServerQueue::hasAtLeastOnePendingItem($uuid)) {
        $appInstance->BadRequest('You already have a pending server creation request', ['error_code' => 'PENDING_SERVER_CREATION_REQUEST']);
        return;
    }
    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);
    $resources = Servers::getUserTotalResourcesUsage($pterodactylUserId);
    $available_resources = User::getInfoArray($accountToken, [
        UserColumns::MEMORY_LIMIT,
        UserColumns::DISK_LIMIT,
        UserColumns::CPU_LIMIT,
        UserColumns::DATABASE_LIMIT,
        UserColumns::BACKUP_LIMIT,
        UserColumns::ALLOCATION_LIMIT,
        UserColumns::SERVER_LIMIT,
    ], []);

    $free_resources = [
        'memory' => $available_resources[UserColumns::MEMORY_LIMIT] - $resources['memory'],
        'disk' => $available_resources[UserColumns::DISK_LIMIT] - $resources['disk'],
        'cpu' => $available_resources[UserColumns::CPU_LIMIT] - $resources['cpu'],
        'databases' => $available_resources[UserColumns::DATABASE_LIMIT] - $resources['databases'],
        'backups' => $available_resources[UserColumns::BACKUP_LIMIT] - $resources['backups'],
        'allocations' => $available_resources[UserColumns::ALLOCATION_LIMIT] - $resources['allocations'],
        'servers' => $available_resources[UserColumns::SERVER_LIMIT] - $resources['servers'],
    ];

    $total_resources = [
        'memory' => $available_resources[UserColumns::MEMORY_LIMIT],
        'disk' => $available_resources[UserColumns::DISK_LIMIT],
        'cpu' => $available_resources[UserColumns::CPU_LIMIT],
        'databases' => $available_resources[UserColumns::DATABASE_LIMIT],
        'backups' => $available_resources[UserColumns::BACKUP_LIMIT],
        'allocations' => $available_resources[UserColumns::ALLOCATION_LIMIT],
        'servers' => $available_resources[UserColumns::SERVER_LIMIT],
    ];

    if ($memory > $free_resources['memory'] || ($resources['memory'] + $memory) > $total_resources['memory']) {
        $appInstance->BadRequest('This server would exceed your maximum memory limit', ['error_code' => 'MAX_MEMORY_LIMIT', 'required' => $total_resources['memory'], 'current_usage' => $resources['memory'], 'attempted_to_add' => $memory]);
        return;
    }
    if ($cpu > $free_resources['cpu'] || ($resources['cpu'] + $cpu) > $total_resources['cpu']) {
        $appInstance->BadRequest('This server would exceed your maximum CPU limit', ['error_code' => 'MAX_CPU_LIMIT', 'required' => $total_resources['cpu'], 'current_usage' => $resources['cpu'], 'attempted_to_add' => $cpu]);
        return;
    }
    if ($disk > $free_resources['disk'] || ($resources['disk'] + $disk) > $total_resources['disk']) {
        $appInstance->BadRequest('This server would exceed your maximum disk limit', ['error_code' => 'MAX_DISK_LIMIT', 'required' => $total_resources['disk'], 'current_usage' => $resources['disk'], 'attempted_to_add' => $disk]);
        return;
    }
    if ($databases > $free_resources['databases'] || ($resources['databases'] + $databases) > $total_resources['databases']) {
        $appInstance->BadRequest('This server would exceed your maximum databases limit', ['error_code' => 'MAX_DATABASES_LIMIT', 'required' => $total_resources['databases'], 'current_usage' => $resources['databases'], 'attempted_to_add' => $databases]);
        return;
    }
    if ($backups > $free_resources['backups'] || ($resources['backups'] + $backups) > $total_resources['backups']) {
        $appInstance->BadRequest('This server would exceed your maximum backups limit', ['error_code' => 'MAX_BACKUPS_LIMIT', 'required' => $total_resources['backups'], 'current_usage' => $resources['backups'], 'attempted_to_add' => $backups]);
        return;
    }
    if ($allocations > $free_resources['allocations'] || ($resources['allocations'] + $allocations) > $total_resources['allocations']) {
        $appInstance->BadRequest('This server would exceed your maximum allocations limit', ['error_code' => 'MAX_ALLOCATIONS_LIMIT', 'required' => $total_resources['allocations'], 'current_usage' => $resources['allocations'], 'attempted_to_add' => $allocations]);
        return;
    }
    if ($free_resources['servers'] < 1) {
        $appInstance->BadRequest('Not enough servers', ['error_code' => 'NOT_ENOUGH_SERVERS']);
        return;
    }

    $locationInfo = Locations::get($location_id);
    $eggInfo = Eggs::getById($egg_id);

    // Check if location is VIP only and user doesn't have VIP permission
    if (isset($locationInfo['vip_only']) && $locationInfo['vip_only'] === 'true' && !$session->hasPermission(Permissions::USER_PERMISSION_VIP)) {
        $appInstance->BadRequest('Location is VIP only', ['error_code' => 'LOCATION_VIP_ONLY']);
        return;
    }

    // Check if egg is VIP only and user doesn't have VIP permission
    if (isset($eggInfo['vip_only']) && $eggInfo['vip_only'] === 'true' && !$session->hasPermission(Permissions::USER_PERMISSION_VIP)) {
        $appInstance->BadRequest('Egg is VIP only', ['error_code' => 'EGG_VIP_ONLY']);
        return;
    }

    if ($locationInfo['slots'] < 1) {
        $appInstance->BadRequest('Location is full', ['error_code' => 'LOCATION_FULL']);
        return;
    }

    $serverCount = MythicalDash\Chat\Servers\Server::getServerCountByLocationId($location_id);
    if ($serverCount >= $locationInfo['slots']) {
        $appInstance->BadRequest('Location is full', ['error_code' => 'LOCATION_FULL', 'server_count' => $serverCount, 'location_slots' => $locationInfo['slots']]);
        return;
    }

    $sv = ServerQueue::create($name, $description, $memory, $disk, $cpu, $allocations, $databases, $backups, $location_id, $uuid, $category_id, $egg_id);
    if ($sv == false) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
    }

    if ($sv == 0) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEURE_ITEM']);
    }

    try {
        global $eventManager;
        $eventManager->emit(ServerQueueEvent::onServerQueueCreated(), [
            'id' => $sv,
            'name' => $name,
            'description' => $description,
            'ram' => $memory,
            'disk' => $disk,
            'cpu' => $cpu,
            'ports' => $allocations,
            'databases' => $databases,
            'backups' => $backups,
            'location' => $location_id,
            'user' => $uuid,
            'nest' => $category_id,
            'egg' => $egg_id,
            'status' => 'pending',
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$server_create,
            CloudFlareRealIP::getRealIP(),
            "Created server queue item $sv"
        );

        $appInstance->OK('Server queue item created successfully.', ['error_code' => 'SERVER_QUEUE_ITEM_CREATED', 'server_queue_item' => $sv, 'server_count' => $serverCount, 'location_slots' => $locationInfo['slots']]);
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
    }
});

$router->post('/api/user/queue/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $serverQueue = ServerQueue::getByUserAndId($session->getInfo(UserColumns::UUID, false), (int) $id);
    if (empty($serverQueue)) {
        $appInstance->BadRequest('Server queue item not found', ['error_code' => 'SERVER_QUEUE_ITEM_NOT_FOUND', 'server_queue' => $serverQueue]);
    }
    ServerQueue::delete((int) $serverQueue['id'] ?? 0);
    $appInstance->OK('Server queue item deleted successfully.', ['error_code' => 'SERVER_QUEUE_ITEM_DELETED']);
});

// Get server by ID
$router->get('/api/user/server/(.*)', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $pterodactylUserId = User::getInfo($accountToken, UserColumns::PTERODACTYL_USER_ID, false);

    // Get server details
    $server = Servers::getServerPterodactylDetails((int) $id);

    if (empty($server)) {
        $appInstance->Forbidden('Server not found or you do not have permission to access it', ['error_code' => 'SERVER_NOT_FOUND', 'server' => $server]);
        return;
    }
    $pterodactylUserId = $session->getInfo(UserColumns::PTERODACTYL_USER_ID, false);
    $owner = $server['attributes']['user'];
    if ($owner != $pterodactylUserId) {
        $appInstance->Forbidden('You do not have permission to access this server', ['error_code' => 'FORBIDDEN']);
        return;
    }
    // Add additional server information
    $locationId = $server['attributes']['relationships']['location']['attributes']['id'];
    $location = Locations::getLocationByPterodactylLocationId($locationId);
    $server['location'] = $location;

    $eggId = $server['attributes']['relationships']['egg']['attributes']['id'];
    $egg = Eggs::getByPterodactylEggId($eggId);
    $server['service'] = $egg;

    $nestId = $server['attributes']['relationships']['nest']['attributes']['id'];
    $nest = EggCategories::getByPterodactylNestId($nestId);
    $server['category'] = $nest;

    if (MythicalDash\Chat\Servers\Server::doesServerExistByPterodactylId($id)) {
        $serverInfoDb = MythicalDash\Chat\Servers\Server::getByPterodactylId($id);
        $server['mythicaldash'] = $serverInfoDb;
    } else {
        $appInstance->BadRequest('Server not found in MythicalDash', ['error_code' => 'SERVER_NOT_FOUND_IN_MYTHICALDASH']);
    }

    $appInstance->OK('Server details about server ' . $id, [
        'server' => $server,
    ]);
});
