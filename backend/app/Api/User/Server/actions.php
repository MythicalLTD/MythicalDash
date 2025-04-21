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
use MythicalDash\Chat\Eggs\Eggs;
use MythicalDash\Chat\User\User;
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
    if (isset($_POST['memory']) && !empty($_POST['memory'])) {
        $memory = $_POST['memory'];
    } else {
        $appInstance->BadRequest('Memory is required', ['error_code' => 'MEMORY_REQUIRED']);

        return;
    }
    if (isset($_POST['cpu']) && !empty($_POST['cpu'])) {
        $cpu = $_POST['cpu'];
    } else {
        $appInstance->BadRequest('CPU is required', ['error_code' => 'CPU_REQUIRED']);

        return;
    }
    if (isset($_POST['disk']) && !empty($_POST['disk'])) {
        $disk = $_POST['disk'];
    } else {
        $appInstance->BadRequest('Disk is required', ['error_code' => 'DISK_REQUIRED']);

        return;
    }
    if (isset($_POST['databases']) && !empty($_POST['databases'])) {
        $databases = $_POST['databases'];
    } else {
        $appInstance->BadRequest('Databases is required', ['error_code' => 'DATABASES_REQUIRED']);

        return;
    }
    if (isset($_POST['backups']) && !empty($_POST['backups'])) {
        $backups = $_POST['backups'];
    } else {
        $appInstance->BadRequest('Backups is required', ['error_code' => 'BACKUPS_REQUIRED']);

        return;
    }
    if (isset($_POST['allocations']) && !empty($_POST['allocations'])) {
        $allocations = $_POST['allocations'];
    } else {
        $appInstance->BadRequest('Allocations is required', ['error_code' => 'ALLOCATIONS_REQUIRED']);

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

    $free_resources = [
        'memory' => $available_resources[UserColumns::MEMORY_LIMIT] - $resources['memory'],
        'disk' => $available_resources[UserColumns::DISK_LIMIT] - $resources['disk'],
        'cpu' => $available_resources[UserColumns::CPU_LIMIT] - $resources['cpu'],
        'databases' => $available_resources[UserColumns::DATABASE_LIMIT] - $resources['databases'],
        'backups' => $available_resources[UserColumns::BACKUP_LIMIT] - $resources['backups'],
        'allocations' => $available_resources[UserColumns::ALLOCATION_LIMIT] - $resources['allocations'],
        'servers' => $available_resources[UserColumns::SERVER_LIMIT] - $resources['servers'],
    ];

    // Check if user has enough resources for the changes
    if ($memory > $free_resources['memory']) {
        $appInstance->BadRequest('You do not have enough memory resources', ['error_code' => 'MEMORY_INSUFFICIENT']);

        return;
    }

    if ($cpu > $free_resources['cpu']) {
        $appInstance->BadRequest('You do not have enough CPU resources', ['error_code' => 'CPU_INSUFFICIENT']);

        return;
    }

    if ($disk > $free_resources['disk']) {
        $appInstance->BadRequest('You do not have enough disk space resources', ['error_code' => 'DISK_INSUFFICIENT']);

        return;
    }

    if ($databases > $free_resources['databases']) {
        $appInstance->BadRequest('You do not have enough database resources', ['error_code' => 'DATABASES_INSUFFICIENT']);

        return;
    }

    if ($backups > $free_resources['backups']) {
        $appInstance->BadRequest('You do not have enough backup resources', ['error_code' => 'BACKUPS_INSUFFICIENT']);

        return;
    }

    if ($allocations > $free_resources['allocations']) {
        $appInstance->BadRequest('You do not have enough allocation resources', ['error_code' => 'ALLOCATIONS_INSUFFICIENT']);

        return;
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
    if ($config->getSetting(ConfigInterface::SERVER_RENEW_ENABLED, 'false') == 'false') {
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
    $serverId = $server['attributes']['id'];
    $serverInfoDb = MythicalDash\Chat\Servers\Server::getByPterodactylId($serverId);
    if (!$serverInfoDb) {
        $appInstance->BadRequest('Server not found in database', ['error_code' => 'SERVER_NOT_FOUND_IN_DB']);

        return;
    }

    // Get renewal settings
    $server_renew_cost = (int) $config->getSetting(ConfigInterface::SERVER_RENEW_COST, 120);
    $server_renew_days = (int) $config->getSetting(ConfigInterface::SERVER_RENEW_DAYS, 30);

    // Validate renewal settings
    if ($server_renew_cost <= 0) {
        $appInstance->BadRequest('Invalid renewal cost configuration', ['error_code' => 'INVALID_RENEWAL_COST']);

        return;
    }

    if ($server_renew_days <= 0) {
        $appInstance->BadRequest('Invalid renewal days configuration', ['error_code' => 'INVALID_RENEWAL_DAYS']);

        return;
    }

    // Check user balance
    $userBalance = (int) $session->getInfo(UserColumns::CREDITS, false);
    if ($userBalance < $server_renew_cost) {
        $appInstance->BadRequest('You do not have enough credits to renew this server', ['error_code' => 'INSUFFICIENT_CREDITS']);

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

        // Deduct credits from user
        $newBalance = $userBalance - $server_renew_cost;
        $session->setInfo(UserColumns::CREDITS, $newBalance, false);

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
                'new_balance' => $newBalance,
            ],
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
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
    if (MythicalDash\Chat\Servers\Server::doesServerExistByPterodactylId($serverId)) {
        MythicalDash\Chat\Servers\Server::deleteServerByPterodactylId($serverId);
    }

    try {
        Servers::deletePterodactylServer($serverId, false);
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

    // Structure categories with their eggs
    $structuredCategories = array_map(function ($category) use ($eggs) {
        $category['eggs'] = array_filter($eggs, function ($egg) use ($category) {
            return $egg['category'] == $category['id'];
        });

        return $category;
    }, $categories);

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
    }

    $appInstance->OK('Server Creation', [
        'locations' => $locations,
        'categories' => $structuredCategories,
        'used_resources' => $resources,
        'total_resources' => $total_resources,
        'free_resources' => $free_resources,
    ]);
});

$router->post('/api/user/server/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $config = $appInstance->getConfig();
    if ($config->getSetting(ConfigInterface::ALLOW_SERVERS, 'false') == 'false') {
        $appInstance->BadRequest('Server creation is not allowed', ['error_code' => 'SERVER_CREATION_NOT_ALLOWED']);

        return;
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
    $databases = (int) $_POST['databases'];
    $backups = (int) $_POST['backups'];
    $allocations = (int) $_POST['allocations'];

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

    if ($free_resources['memory'] < $memory) {
        $appInstance->BadRequest('Not enough memory', ['error_code' => 'NOT_ENOUGH_MEMORY']);

        return;
    }

    if ($free_resources['disk'] < $disk) {
        $appInstance->BadRequest('Not enough disk space', ['error_code' => 'NOT_ENOUGH_DISK_SPACE']);

        return;
    }

    if ($free_resources['cpu'] < $cpu) {
        $appInstance->BadRequest('Not enough CPU', ['error_code' => 'NOT_ENOUGH_CPU']);

        return;
    }

    if ($free_resources['databases'] < $databases) {
        $appInstance->BadRequest('Not enough databases', ['error_code' => 'NOT_ENOUGH_DATABASES']);

        return;
    }

    if ($free_resources['backups'] < $backups) {
        $appInstance->BadRequest('Not enough backups', ['error_code' => 'NOT_ENOUGH_BACKUPS']);

        return;
    }

    if ($free_resources['allocations'] < $allocations) {
        $appInstance->BadRequest('Not enough allocations', ['error_code' => 'NOT_ENOUGH_ALLOCATIONS']);

        return;
    }

    if ($free_resources['servers'] < 1) {
        $appInstance->BadRequest('Not enough servers', ['error_code' => 'NOT_ENOUGH_SERVERS']);

        return;
    }

    $sv = ServerQueue::create($name, $description, $memory, $disk, $cpu, $allocations, $databases, $backups, $location_id, $uuid, $category_id, $egg_id);
    if ($sv == false) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
    }

    if ($sv == 0) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
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

        $appInstance->OK('Server queue item created successfully.', ['error_code' => 'SERVER_QUEUE_ITEM_CREATED', 'server_queue_item' => $sv]);
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
    }
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
