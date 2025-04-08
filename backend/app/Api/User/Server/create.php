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
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\Servers\ServerQueue;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Plugins\Events\Events\ServerQueueEvent;

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

        $appInstance->OK('Server queue item created successfully.', ['error_code' => 'SERVER_QUEUE_ITEM_CREATED', 'server_queue_item' => $sv]);
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to create server queue item', ['error_code' => 'FAILED_TO_CREATE_SERVER_QUEUE_ITEM']);
    }
});
