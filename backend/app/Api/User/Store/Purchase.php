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
    // Define available items with their prices, quantities, and effects
    $items = [
        'ram' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_RAM_PRICE, 150),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_RAM_QUANTITY, 1024),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_RAM_QUANTITY, 1024);
                $session->setInfo(UserColumns::MEMORY_LIMIT, $session->getInfo(UserColumns::MEMORY_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_RAM, 'false') === 'true',
        ],
        'disk' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_DISK_PRICE, 200),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_DISK_QUANTITY, 1024),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_DISK_QUANTITY, 1024);
                $session->setInfo(UserColumns::DISK_LIMIT, $session->getInfo(UserColumns::DISK_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_DISK, 'false') === 'true',
        ],
        'cpu' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_CPU_PRICE, 300),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_CPU_QUANTITY, 100),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_CPU_QUANTITY, 100);
                $session->setInfo(UserColumns::CPU_LIMIT, $session->getInfo(UserColumns::CPU_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_CPU, 'false') === 'true',
        ],
        'server_slot' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_SERVER_SLOT_QUANTITY, 1),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_SERVER_SLOT_QUANTITY, 1);
                $session->setInfo(UserColumns::SERVER_LIMIT, $session->getInfo(UserColumns::SERVER_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_SERVER_SLOTS, 'false') === 'true',
        ],
        'server_backup' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_BACKUPS_QUANTITY, 1),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_BACKUPS_QUANTITY, 1);
                $session->setInfo(UserColumns::BACKUP_LIMIT, $session->getInfo(UserColumns::BACKUP_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_BACKUPS, 'false') === 'true',
        ],
        'server_allocation' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_ALLOCATION_QUANTITY, 2),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_ALLOCATION_QUANTITY, 2);
                $session->setInfo(UserColumns::ALLOCATION_LIMIT, $session->getInfo(UserColumns::ALLOCATION_LIMIT, false) + $quantity, false);
            },
            'blocked' => $config->getDBSetting(ConfigInterface::BLOCK_PORTS, 'false') === 'true',
        ],
        'server_database' => [
            'price' => (int) $config->getDBSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
            'quantity' => (int) $config->getDBSetting(ConfigInterface::STORE_DATABASES_QUANTITY, 1),
            'effect' => function ($session) use ($config) {
                $quantity = (int) $config->getDBSetting(ConfigInterface::STORE_DATABASES_QUANTITY, 1);
                $session->setInfo(UserColumns::DATABASE_LIMIT, $session->getInfo(UserColumns::DATABASE_LIMIT, false) + $quantity, false);
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

    // Check if user has sufficient credits atomically (prevents race conditions)
    $creditCheck = $session->checkCreditsAtomic($price);
    if (!$creditCheck['has_sufficient']) {
        $appInstance->BadRequest('Insufficient coins', [
            'error_code' => 'INSUFFICIENT_COINS',
            'required' => $price,
            'available' => $creditCheck['current_credits'],
        ]);

        return;
    }

    // Check resource limits based on item type
    switch ($itemId) {
        case 'ram':
            $maxRam = $config->getDBSetting(ConfigInterface::MAX_RAM, 1024);
            $currentRam = (int) $session->getInfo(UserColumns::MEMORY_LIMIT, false);
            $ramToAdd = $item['quantity'];
            if ($currentRam >= (int) $maxRam) {
                $appInstance->BadRequest('You have reached the maximum RAM limit', [
                    'error_code' => 'MAX_RAM_LIMIT',
                    'required' => $maxRam,
                    'available' => $currentRam,
                ]);

                return;
            }
            if (($currentRam + $ramToAdd) > (int) $maxRam) {
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
            $maxDisk = $config->getDBSetting(ConfigInterface::MAX_DISK, 1024);
            $currentDisk = (int) $session->getInfo(UserColumns::DISK_LIMIT, false);
            $diskToAdd = $item['quantity'];
            if ($currentDisk >= (int) $maxDisk) {
                $appInstance->BadRequest('You have reached the maximum disk limit', [
                    'error_code' => 'MAX_DISK_LIMIT',
                    'required' => $maxDisk,
                    'available' => $currentDisk,
                ]);

                return;
            }
            if (($currentDisk + $diskToAdd) > (int) $maxDisk) {
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
            $maxCpu = $config->getDBSetting(ConfigInterface::MAX_CPU, 100);
            $currentCpu = (int) $session->getInfo(UserColumns::CPU_LIMIT, false);
            $cpuToAdd = $item['quantity'];
            if ($currentCpu >= (int) $maxCpu) {
                $appInstance->BadRequest('You have reached the maximum CPU limit', [
                    'error_code' => 'MAX_CPU_LIMIT',
                    'required' => $maxCpu,
                    'available' => $currentCpu,
                ]);

                return;
            }
            if (($currentCpu + $cpuToAdd) > (int) $maxCpu) {
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
            $maxServerSlots = (int) $config->getDBSetting(ConfigInterface::MAX_SERVER_SLOTS, 1);
            $currentSlots = (int) $session->getInfo(UserColumns::SERVER_LIMIT, false);
            if ($currentSlots >= (int) $maxServerSlots) {
                $appInstance->BadRequest('You have reached the maximum server slots limit', [
                    'error_code' => 'MAX_SERVER_SLOTS_LIMIT',
                    'required' => $maxServerSlots,
                    'available' => $currentSlots,
                ]);

                return;
            }
            $slotsToAdd = $item['quantity'];
            if (($currentSlots + $slotsToAdd) > (int) $maxServerSlots) {
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
            $maxBackups = $config->getDBSetting(ConfigInterface::MAX_BACKUPS, 5);
            $currentBackups = (int) $session->getInfo(UserColumns::BACKUP_LIMIT, false);
            $backupsToAdd = $item['quantity'];
            if ($currentBackups >= (int) $maxBackups) {
                $appInstance->BadRequest('You have reached the maximum backups limit', [
                    'error_code' => 'MAX_BACKUPS_LIMIT',
                    'required' => $maxBackups,
                    'available' => $currentBackups,
                ]);

                return;
            }
            if (($currentBackups + $backupsToAdd) > (int) $maxBackups) {
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
            $maxPorts = $config->getDBSetting(ConfigInterface::MAX_PORTS, 2);
            $currentPorts = (int) $session->getInfo(UserColumns::ALLOCATION_LIMIT, false);
            $portsToAdd = $item['quantity'];
            if ($currentPorts >= (int) $maxPorts) {
                $appInstance->BadRequest('You have reached the maximum ports limit', [
                    'error_code' => 'MAX_PORTS_LIMIT',
                    'required' => $maxPorts,
                    'available' => $currentPorts,
                ]);

                return;
            }
            if (($currentPorts + $portsToAdd) > (int) $maxPorts) {
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
            $maxDatabases = $config->getDBSetting(ConfigInterface::MAX_DATABASES, 1);
            $currentDatabases = (int) $session->getInfo(UserColumns::DATABASE_LIMIT, false);
            $databasesToAdd = $item['quantity'];
            if ($currentDatabases >= (int) $maxDatabases) {
                $appInstance->BadRequest('You have reached the maximum databases limit', [
                    'error_code' => 'MAX_DATABASES_LIMIT',
                    'required' => $maxDatabases,
                    'available' => $currentDatabases,
                ]);

                return;
            }
            if (($currentDatabases + $databasesToAdd) > (int) $maxDatabases) {
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

    // Process purchase atomically (prevents race conditions)
    try {
        $purchaseResult = $session->processPurchaseAtomic($price, function ($session) use ($item) {
            // Apply item effect
            $item['effect']($session);
        });

        if (!$purchaseResult['success']) {
            $appInstance->BadRequest('Failed to process purchase', [
                'error_code' => $purchaseResult['error_code'],
                'required' => $purchaseResult['required'] ?? null,
                'available' => $purchaseResult['available'] ?? null,
                'message' => $purchaseResult['message'] ?? null,
            ]);

            return;
        }

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
            'remaining_coins' => $purchaseResult['remaining_coins'],
        ]);
    } catch (Exception $e) {
        $appInstance->BadRequest('Failed to process purchase', [
            'error_code' => 'PURCHASE_FAILED',
            'message' => $e->getMessage(),
        ]);
    }
});
