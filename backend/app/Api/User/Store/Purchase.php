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
            $currentRam = (int)$session->getInfo(UserColumns::MEMORY_LIMIT, false);
            $ramToAdd = isset($item['ram']) ? (int)$item['ram'] : 1024;
            if ($currentRam >= (int)$maxRam) {
                $appInstance->BadRequest('You have reached the maximum RAM limit', [
                    'error_code' => 'MAX_RAM_LIMIT',
                    'required' => $maxRam,
                    'available' => $currentRam,
                ]);
                return;
            }
            if (($currentRam + $ramToAdd) > (int)$maxRam) {
                $appInstance->BadRequest('This purchase would exceed your maximum RAM limit', [
                    'error_code' => 'MAX_RAM_LIMIT',
                    'required' => $maxRam,
                    'available' => $currentRam,
                    'attempted_to_add' => $ramToAdd,
                ]);
                return;
            }
            break;
        case 'disk':
            $maxDisk = $config->getSetting(ConfigInterface::MAX_DISK, 1024);
            $currentDisk = (int)$session->getInfo(UserColumns::DISK_LIMIT, false);
            $diskToAdd = isset($item['disk']) ? (int)$item['disk'] : 1024;
            if ($currentDisk >= (int)$maxDisk) {
                $appInstance->BadRequest('You have reached the maximum disk limit', [
                    'error_code' => 'MAX_DISK_LIMIT',
                    'required' => $maxDisk,
                    'available' => $currentDisk,
                ]);
                return;
            }
            if (($currentDisk + $diskToAdd) > (int)$maxDisk) {
                $appInstance->BadRequest('This purchase would exceed your maximum disk limit', [
                    'error_code' => 'MAX_DISK_LIMIT',
                    'required' => $maxDisk,
                    'available' => $currentDisk,
                    'attempted_to_add' => $diskToAdd,
                ]);
                return;
            }
            break;
        case 'cpu':
            $maxCpu = $config->getSetting(ConfigInterface::MAX_CPU, 100);
            $currentCpu = (int)$session->getInfo(UserColumns::CPU_LIMIT, false);
            $cpuToAdd = isset($item['cpu']) ? (int)$item['cpu'] : 100;
            if ($currentCpu >= (int)$maxCpu) {
                $appInstance->BadRequest('You have reached the maximum CPU limit', [
                    'error_code' => 'MAX_CPU_LIMIT',
                    'required' => $maxCpu,
                    'available' => $currentCpu,
                ]);
                return;
            }
            if (($currentCpu + $cpuToAdd) > (int)$maxCpu) {
                $appInstance->BadRequest('This purchase would exceed your maximum CPU limit', [
                    'error_code' => 'MAX_CPU_LIMIT',
                    'required' => $maxCpu,
                    'available' => $currentCpu,
                    'attempted_to_add' => $cpuToAdd,
                ]);
                return;
            }
            break;
        case 'server_slot':
            $maxServerSlots = (int)$config->getSetting(ConfigInterface::MAX_SERVER_SLOTS, 1);
            $currentSlots = (int)$session->getInfo(UserColumns::SERVER_LIMIT, false);
            if ($currentSlots >= (int)$maxServerSlots) {
                $appInstance->BadRequest('You have reached the maximum server slots limit', [
                    'error_code' => 'MAX_SERVER_SLOTS_LIMIT',
                    'required' => $maxServerSlots,
                    'available' => $currentSlots,
                ]);
                return;
            }
            $slotsToAdd = isset($item['slots']) ? (int)$item['slots'] : 1;
            if (($currentSlots + $slotsToAdd) > (int)$maxServerSlots) {
                $appInstance->BadRequest('This purchase would exceed your maximum server slots limit', [
                    'error_code' => 'MAX_SERVER_SLOTS_LIMIT',
                    'required' => $maxServerSlots,
                    'available' => $currentSlots,
                    'attempted_to_add' => $slotsToAdd,
                ]);
                return;
            }
            break;
        case 'server_backup':
            $maxBackups = $config->getSetting(ConfigInterface::MAX_BACKUPS, 5);
            $currentBackups = (int)$session->getInfo(UserColumns::BACKUP_LIMIT, false);
            $backupsToAdd = isset($item['backups']) ? (int)$item['backups'] : 1;
            if ($currentBackups >= (int)$maxBackups) {
                $appInstance->BadRequest('You have reached the maximum backups limit', [
                    'error_code' => 'MAX_BACKUPS_LIMIT',
                    'required' => $maxBackups,
                    'available' => $currentBackups,
                ]);
                return;
            }
            if (($currentBackups + $backupsToAdd) > (int)$maxBackups) {
                $appInstance->BadRequest('This purchase would exceed your maximum backups limit', [
                    'error_code' => 'MAX_BACKUPS_LIMIT',
                    'required' => $maxBackups,
                    'available' => $currentBackups,
                    'attempted_to_add' => $backupsToAdd,
                ]);
                return;
            }
            break;
        case 'server_allocation':
            $maxPorts = $config->getSetting(ConfigInterface::MAX_PORTS, 2);
            $currentPorts = (int)$session->getInfo(UserColumns::ALLOCATION_LIMIT, false);
            $portsToAdd = isset($item['ports']) ? (int)$item['ports'] : 1;
            if ($currentPorts >= (int)$maxPorts) {
                $appInstance->BadRequest('You have reached the maximum ports limit', [
                    'error_code' => 'MAX_PORTS_LIMIT',
                    'required' => $maxPorts,
                    'available' => $currentPorts,
                ]);
                return;
            }
            if (($currentPorts + $portsToAdd) > (int)$maxPorts) {
                $appInstance->BadRequest('This purchase would exceed your maximum ports limit', [
                    'error_code' => 'MAX_PORTS_LIMIT',
                    'required' => $maxPorts,
                    'available' => $currentPorts,
                    'attempted_to_add' => $portsToAdd,
                ]);
                return;
            }
            break;
        case 'server_database':
            $maxDatabases = $config->getSetting(ConfigInterface::MAX_DATABASES, 1);
            $currentDatabases = (int)$session->getInfo(UserColumns::DATABASE_LIMIT, false);
            $databasesToAdd = isset($item['databases']) ? (int)$item['databases'] : 1;
            if ($currentDatabases >= (int)$maxDatabases) {
                $appInstance->BadRequest('You have reached the maximum databases limit', [
                    'error_code' => 'MAX_DATABASES_LIMIT',
                    'required' => $maxDatabases,
                    'available' => $currentDatabases,
                ]);
                return;
            }
            if (($currentDatabases + $databasesToAdd) > (int)$maxDatabases) {
                $appInstance->BadRequest('This purchase would exceed your maximum databases limit', [
                    'error_code' => 'MAX_DATABASES_LIMIT',
                    'required' => $maxDatabases,
                    'available' => $currentDatabases,
                    'attempted_to_add' => $databasesToAdd,
                ]);
                return;
            }
            break;
    }

    // Process purchase
    try {
        // Deduct coins
        $session->removeCredits((int) intval($price));

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
