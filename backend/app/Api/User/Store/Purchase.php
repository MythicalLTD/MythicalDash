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
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;

$router->post('/api/user/store/purchase', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();

    if ($config->getSetting(ConfigInterface::STORE_ENABLED, 'false') !== 'true') {
        $appInstance->BadRequest('Store is not enabled', ['error_code' => 'STORE_NOT_ENABLED']);
    }

    // Get the request data
    $requestData = $appInstance->getRequestData();

    // Validate required fields
    if (!isset($requestData['itemId'])) {
        $appInstance->error('Item ID is required', 400);

        return;
    }

    $itemId = $requestData['itemId'];
    $user = $session->getUser();

    // Mock items data - in a real implementation, this would be fetched from a database
    $items = [
        'ram_512mb' => [
            'id' => 'ram_512mb',
            'name' => 'RAM Upgrade',
            'price' => 150,
            'category' => 'ram',
            'action' => 'addRam',
        ],
        'disk_1024mb' => [
            'id' => 'disk_1024mb',
            'name' => 'Disk Storage',
            'price' => 200,
            'category' => 'disk',
            'action' => 'addDisk',
        ],
        'cpu_50pct' => [
            'id' => 'cpu_50pct',
            'name' => 'CPU Resource',
            'price' => 300,
            'category' => 'cpu',
            'action' => 'addCpu',
        ],
        'server_slot' => [
            'id' => 'server_slot',
            'name' => 'Server Slot',
            'price' => 500,
            'category' => 'slots',
            'action' => 'addServerSlot',
        ],
        'server_backup' => [
            'id' => 'server_backup',
            'name' => 'Server Backup',
            'price' => 150,
            'category' => 'backups',
            'action' => 'addBackup',
        ],
        'server_allocation' => [
            'id' => 'server_allocation',
            'name' => 'Server Allocation',
            'price' => 100,
            'category' => 'allocations',
            'action' => 'addAllocation',
        ],
        'server_database' => [
            'id' => 'server_database',
            'name' => 'Server Database',
            'price' => 200,
            'category' => 'databases',
            'action' => 'addDatabase',
        ],
    ];

    // Check if the item exists
    if (!isset($items[$itemId])) {
        $appInstance->error('Item not found', 404);

        return;
    }

    $item = $items[$itemId];

    // Check if user has enough coins
    if ($user->credits < $item['price']) {
        $appInstance->error('Insufficient coins', 400);

        return;
    }

    // Deduct coins from user
    $user->credits -= $item['price'];
    $user->save();

    // Process the purchase based on item type
    switch ($item['action']) {
        case 'addRam':
            // In a real implementation, this would update the user's server resources
            logInfo("Added 512MB RAM for user {$user->id}");
            break;
        case 'addDisk':
            logInfo("Added 1024MB disk for user {$user->id}");
            break;
        case 'addCpu':
            logInfo("Added 50% CPU for user {$user->id}");
            break;
        case 'addServerSlot':
            logInfo("Added server slot for user {$user->id}");
            break;
        case 'addBackup':
            logInfo("Added backup slot for user {$user->id}");
            break;
        case 'addAllocation':
            logInfo("Added allocation for user {$user->id}");
            break;
        case 'addDatabase':
            logInfo("Added database for user {$user->id}");
            break;
        default:
            logWarning("Unknown action {$item['action']} for user {$user->id}");
            break;
    }

    // Record the purchase in the transaction history
    // In a real implementation, save to database

    $appInstance->OK('Purchase successful', [
        'item' => $item,
        'currentCoins' => $user->credits,
    ]);
});
