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
use MythicalDash\Chat\columns\UserColumns;

$router->post('/api/user/store/purchase', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();

    // Check if store is enabled
    if ($config->getSetting(ConfigInterface::STORE_ENABLED, 'false') !== 'true') {
        $appInstance->BadRequest('Store is not enabled', ['error_code' => 'STORE_NOT_ENABLED']);

        return;
    }

    // Validate required parameters
    if (!isset($_POST['itemId'])) {
        $appInstance->BadRequest('Item ID is required', ['error_code' => 'MISSING_ITEM_ID']);

        return;
    }

    if ($_POST['itemId'] == '') {
        $appInstance->BadRequest('Item ID is empty', ['error_code' => 'EMPTY_ITEM_ID']);

        return;
    }
    // Define available items with their prices and effects
    $items = [
        'ram' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_RAM_PRICE, 150),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::MEMORY_LIMIT, $session->getInfo(UserColumns::MEMORY_LIMIT, false) + 1024, false);
            },
        ],
        'disk' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_DISK_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DISK_LIMIT, $session->getInfo(UserColumns::DISK_LIMIT, false) + 1024, false);
            },
        ],
        'cpu' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_CPU_PRICE, 300),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::CPU_LIMIT, $session->getInfo(UserColumns::CPU_LIMIT, false) + 100, false);
            },
        ],
        'server_slot' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::SERVER_LIMIT, $session->getInfo(UserColumns::SERVER_LIMIT, false) + 1, false);
            },
        ],
        'server_backup' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::BACKUP_LIMIT, $session->getInfo(UserColumns::BACKUP_LIMIT, false) + 1, false);
            },
        ],
        'server_allocation' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::ALLOCATION_LIMIT, $session->getInfo(UserColumns::ALLOCATION_LIMIT, false) + 1, false);
            },
        ],
        'server_database' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DATABASE_LIMIT, $session->getInfo(UserColumns::DATABASE_LIMIT, false) + 1, false);
            },
        ],
    ];

    $itemId = $_POST['itemId'];

    // Check if item exists
    if (!isset($items[$itemId])) {
        $appInstance->BadRequest('Invalid item ID: ' . $itemId, ['error_code' => 'INVALID_ITEM_ID']);

        return;
    }

    $item = $items[$itemId];
    $price = $item['price'];
    $currentCoins = (int) $session->getInfo(UserColumns::CREDITS, false);

    // Validate user has enough coins
    if ($currentCoins < $price) {
        $appInstance->BadRequest('Insufficient coins', [
            'error_code' => 'INSUFFICIENT_COINS',
            'required' => $price,
            'available' => $currentCoins,
        ]);

        return;
    }

    // Process purchase
    try {
        // Deduct coins
        $session->setInfo(UserColumns::CREDITS, $currentCoins - $price, false);

        // Apply item effect
        $item['effect']($session);

        // Return success response
        $appInstance->OK('Purchase successful', [
            'item' => [
                'id' => $itemId,
                'price' => $price,
            ],
            'remaining_coins' => $currentCoins - $price,
        ]);
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to process purchase', [
            'error_code' => 'PURCHASE_FAILED',
            'message' => $e->getMessage(),
        ]);
    }
});
