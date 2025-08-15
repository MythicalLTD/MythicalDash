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
use MythicalDash\Chat\User\UserLock;
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
    if ($config->getDBSetting(ConfigInterface::STORE_ENABLED, 'false') !== 'true') {
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
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_RAM_PRICE, 150),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::MEMORY_LIMIT, $session->getInfo(UserColumns::MEMORY_LIMIT, false) + 1024, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_RAM, 'false') === 'true',
        ],
        'disk' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_DISK_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DISK_LIMIT, $session->getInfo(UserColumns::DISK_LIMIT, false) + 1024, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_DISK, 'false') === 'true',
        ],
        'cpu' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_CPU_PRICE, 300),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::CPU_LIMIT, $session->getInfo(UserColumns::CPU_LIMIT, false) + 100, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_CPU, 'false') === 'true',
        ],
        'server_slot' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::SERVER_LIMIT, $session->getInfo(UserColumns::SERVER_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_SERVER_SLOTS, 'false') === 'true',
        ],
        'server_backup' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::BACKUP_LIMIT, $session->getInfo(UserColumns::BACKUP_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_BACKUPS, 'false') === 'true',
        ],
        'server_allocation' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::ALLOCATION_LIMIT, $session->getInfo(UserColumns::ALLOCATION_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_PORTS, 'false') === 'true',
        ],
        'server_database' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
            'effect' => function ($session) {
                $session->setInfo(UserColumns::DATABASE_LIMIT, $session->getInfo(UserColumns::DATABASE_LIMIT, false) + 1, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_DATABASES, 'false') === 'true',
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
    $userUuid = $session->getInfo(UserColumns::UUID, false);

    // Execute purchase with user lock protection to prevent race conditions
    try {
        $result = UserLock::executeWithLock($userUuid, function () use ($session, $item, $price, $itemId, $config) {
            // Re-check balance after acquiring lock to ensure it's still sufficient
            $currentCoins = (int) $session->getInfo(UserColumns::CREDITS, false);

            // Validate user has enough coins
            if ($currentCoins < $price) {
                throw new Exception('Insufficient coins');
            }

            // Check resource limits based on item type
            switch ($itemId) {
                case 'ram':
                    $maxRam = $config->getDBSetting(ConfigInterface::MAX_RAM, 1024);
                    $currentRam = (int) $session->getInfo(UserColumns::MEMORY_LIMIT, false);
                    $ramToAdd = isset($item['ram']) ? (int) $item['ram'] : 1024;
                    if ($currentRam >= (int) $maxRam) {
                        throw new Exception('You have reached the maximum RAM limit');
                    }
                    if (($currentRam + $ramToAdd) > (int) $maxRam) {
                        throw new Exception('This purchase would exceed your maximum RAM limit');
                    }
                    break;
                case 'disk':
                    $maxDisk = $config->getDBSetting(ConfigInterface::MAX_DISK, 1024);
                    $currentDisk = (int) $session->getInfo(UserColumns::DISK_LIMIT, false);
                    $diskToAdd = isset($item['disk']) ? (int) $item['disk'] : 1024;
                    if ($currentDisk >= (int) $maxDisk) {
                        throw new Exception('You have reached the maximum disk limit');
                    }
                    if (($currentDisk + $diskToAdd) > (int) $maxDisk) {
                        throw new Exception('This purchase would exceed your maximum disk limit');
                    }
                    break;
                case 'cpu':
                    $maxCpu = $config->getDBSetting(ConfigInterface::MAX_CPU, 100);
                    $currentCpu = (int) $session->getInfo(UserColumns::CPU_LIMIT, false);
                    $cpuToAdd = isset($item['cpu']) ? (int) $item['cpu'] : 100;
                    if ($currentCpu >= (int) $maxCpu) {
                        throw new Exception('You have reached the maximum CPU limit');
                    }
                    if (($currentCpu + $cpuToAdd) > (int) $maxCpu) {
                        throw new Exception('This purchase would exceed your maximum CPU limit');
                    }
                    break;
                case 'server_slot':
                    $maxServerSlots = (int) $config->getDBSetting(ConfigInterface::MAX_SERVER_SLOTS, 1);
                    $currentSlots = (int) $session->getInfo(UserColumns::SERVER_LIMIT, false);
                    if ($currentSlots >= (int) $maxServerSlots) {
                        throw new Exception('You have reached the maximum server slots limit');
                    }
                    $slotsToAdd = isset($item['slots']) ? (int) $item['slots'] : 1;
                    if (($currentSlots + $slotsToAdd) > (int) $maxServerSlots) {
                        throw new Exception('This purchase would exceed your maximum server slots limit');
                    }
                    break;
                case 'server_backup':
                    $maxBackups = $config->getDBSetting(ConfigInterface::MAX_BACKUPS, 5);
                    $currentBackups = (int) $session->getInfo(UserColumns::BACKUP_LIMIT, false);
                    $backupsToAdd = isset($item['backups']) ? (int) $item['backups'] : 1;
                    if ($currentBackups >= (int) $maxBackups) {
                        throw new Exception('You have reached the maximum backups limit');
                    }
                    if (($currentBackups + $backupsToAdd) > (int) $maxBackups) {
                        throw new Exception('This purchase would exceed your maximum backups limit');
                    }
                    break;
                case 'server_allocation':
                    $maxPorts = $config->getDBSetting(ConfigInterface::MAX_PORTS, 2);
                    $currentPorts = (int) $session->getInfo(UserColumns::ALLOCATION_LIMIT, false);
                    $portsToAdd = isset($item['ports']) ? (int) $item['ports'] : 1;
                    if ($currentPorts >= (int) $maxPorts) {
                        throw new Exception('You have reached the maximum ports limit');
                    }
                    if (($currentPorts + $portsToAdd) > (int) $maxPorts) {
                        throw new Exception('This purchase would exceed your maximum ports limit');
                    }
                    break;
                case 'server_database':
                    $maxDatabases = $config->getDBSetting(ConfigInterface::MAX_DATABASES, 1);
                    $currentDatabases = (int) $session->getInfo(UserColumns::DATABASE_LIMIT, false);
                    $databasesToAdd = isset($item['databases']) ? (int) $item['databases'] : 1;
                    if ($currentDatabases >= (int) $maxDatabases) {
                        throw new Exception('You have reached the maximum databases limit');
                    }
                    if (($currentDatabases + $databasesToAdd) > (int) $maxDatabases) {
                        throw new Exception('This purchase would exceed your maximum databases limit');
                    }
                    break;
            }

            // Process purchase
            // Deduct coins
            $session->removeCredits((int) intval($price));

            // Apply item effect
            $item['effect']($session);

            // Get updated balance for response
            $newBalance = (int) $session->getInfo(UserColumns::CREDITS, false);

            return [
                'success' => true,
                'new_balance' => $newBalance,
                'price' => $price,
            ];
        });

        // If we get here, the purchase was successful
        global $eventManager;
        $eventManager->emit(StoreEvent::onStoreBuy(), [
            'user' => $userUuid,
            'item' => $itemId,
            'price' => $price,
        ]);

        UserActivities::add(
            $userUuid,
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
            'remaining_coins' => $result['new_balance'],
        ]);

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();

        // Handle specific error cases
        if ($errorMessage === 'Insufficient coins') {
            $currentCoins = (int) $session->getInfo(UserColumns::CREDITS, false);
            $appInstance->BadRequest('Insufficient coins', [
                'error_code' => 'INSUFFICIENT_COINS',
                'required' => $price,
                'available' => $currentCoins,
            ]);
        } else {
            $appInstance->BadRequest('Failed to process purchase', [
                'error_code' => 'PURCHASE_FAILED',
                'message' => $errorMessage,
            ]);
        }
    }
});
