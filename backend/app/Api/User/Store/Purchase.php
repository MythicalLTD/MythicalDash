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
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\StoreEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

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
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_RAM, 'false') === 'true',
        ],
        'disk' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_DISK_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DISK_LIMIT, $session->getInfo(UserColumns::DISK_LIMIT, false) + 1024, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_DISK, 'false') === 'true',
        ],
        'cpu' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_CPU_PRICE, 300),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::CPU_LIMIT, $session->getInfo(UserColumns::CPU_LIMIT, false) + 100, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_CPU, 'false') === 'true',
        ],
        'server_slot' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::SERVER_LIMIT, $session->getInfo(UserColumns::SERVER_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_SERVER_SLOTS, 'false') === 'true',
        ],
        'server_backup' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::BACKUP_LIMIT, $session->getInfo(UserColumns::BACKUP_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_BACKUPS, 'false') === 'true',
        ],
        'server_allocation' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::ALLOCATION_LIMIT, $session->getInfo(UserColumns::ALLOCATION_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_PORTS, 'false') === 'true',
        ],
        'server_database' => [
            'price' => (int) $config->getSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DATABASE_LIMIT, $session->getInfo(UserColumns::DATABASE_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getSetting(ConfigInterface::BLOCK_DATABASES, 'false') === 'true',
        ],
    ];

    // Filter out blocked items
    $items = array_filter($items, function ($item) {
        return !$item['blocked'];
    });

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

    // Check resource limits based on item type
    switch ($itemId) {
        case 'ram':
            $maxRam = $config->getSetting(ConfigInterface::MAX_RAM, 1024);
            if ($session->getInfo(UserColumns::MEMORY_LIMIT, false) >= $maxRam) {
                $appInstance->BadRequest('You have reached the maximum RAM limit', [
                    'error_code' => 'MAX_RAM_LIMIT',
                    'required' => $maxRam,
                    'available' => $session->getInfo(UserColumns::MEMORY_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'disk':
            $maxDisk = $config->getSetting(ConfigInterface::MAX_DISK, 1024);
            if ($session->getInfo(UserColumns::DISK_LIMIT, false) >= $maxDisk) {
                $appInstance->BadRequest('You have reached the maximum disk limit', [
                    'error_code' => 'MAX_DISK_LIMIT',
                    'required' => $maxDisk,
                    'available' => $session->getInfo(UserColumns::DISK_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'cpu':
            $maxCpu = $config->getSetting(ConfigInterface::MAX_CPU, 100);
            if ($session->getInfo(UserColumns::CPU_LIMIT, false) >= $maxCpu) {
                $appInstance->BadRequest('You have reached the maximum CPU limit', [
                    'error_code' => 'MAX_CPU_LIMIT',
                    'required' => $maxCpu,
                    'available' => $session->getInfo(UserColumns::CPU_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'server_slot':
            $maxServerSlots = $config->getSetting(ConfigInterface::MAX_SERVER_SLOTS, 1);
            if ($session->getInfo(UserColumns::SERVER_LIMIT, false) >= $maxServerSlots) {
                $appInstance->BadRequest('You have reached the maximum server slots limit', [
                    'error_code' => 'MAX_SERVER_SLOTS_LIMIT',
                    'required' => $maxServerSlots,
                    'available' => $session->getInfo(UserColumns::SERVER_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'server_backup':
            $maxBackups = $config->getSetting(ConfigInterface::MAX_BACKUPS, 5);
            if ($session->getInfo(UserColumns::BACKUP_LIMIT, false) >= $maxBackups) {
                $appInstance->BadRequest('You have reached the maximum backups limit', [
                    'error_code' => 'MAX_BACKUPS_LIMIT',
                    'required' => $maxBackups,
                    'available' => $session->getInfo(UserColumns::BACKUP_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'server_allocation':
            $maxPorts = $config->getSetting(ConfigInterface::MAX_PORTS, 2);
            if ($session->getInfo(UserColumns::ALLOCATION_LIMIT, false) >= $maxPorts) {
                $appInstance->BadRequest('You have reached the maximum ports limit', [
                    'error_code' => 'MAX_PORTS_LIMIT',
                    'required' => $maxPorts,
                    'available' => $session->getInfo(UserColumns::ALLOCATION_LIMIT, false),
                ]);

                return;
            }
            break;
        case 'server_database':
            $maxDatabases = $config->getSetting(ConfigInterface::MAX_DATABASES, 1);
            if ($session->getInfo(UserColumns::DATABASE_LIMIT, false) >= $maxDatabases) {
                $appInstance->BadRequest('You have reached the maximum databases limit', [
                    'error_code' => 'MAX_DATABASES_LIMIT',
                    'required' => $maxDatabases,
                    'available' => $session->getInfo(UserColumns::DATABASE_LIMIT, false),
                ]);

                return;
            }
            break;
    }

    // Process purchase
    try {
        // Deduct coins
        $session->setInfo(UserColumns::CREDITS, $currentCoins - $price, false);

        // Apply item effect
        $item['effect']($session);
        global $eventManager;
        $eventManager->emit(StoreEvent::onStoreBuy(), [
            'user' => $session->getInfo(UserColumns::UUID, false),
            'item' => $itemId,
            'price' => $price,
        ]);

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$store_buy,
            CloudFlareRealIP::getRealIP(),
            "Purchased $itemId for $price coins"
        );

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
