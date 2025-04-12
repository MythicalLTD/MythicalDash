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

$router->get('/api/user/store/items', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();
    // Check if store is enabled
    if ($config->getSetting(ConfigInterface::STORE_ENABLED, 'false') !== 'true') {
        $appInstance->BadRequest('Store is not enabled', ['error_code' => 'STORE_NOT_ENABLED']);
    }

    $items = [
        [
            'id' => 'ram',
            'price' => $config->getSetting(ConfigInterface::STORE_RAM_PRICE, 150), 0,
        ],
        [
            'id' => 'disk',
            'price' => $config->getSetting(ConfigInterface::STORE_DISK_PRICE, 200),
        ],
        [
            'id' => 'cpu',
            'price' => $config->getSetting(ConfigInterface::STORE_CPU_PRICE, 300),
        ],
        [
            'id' => 'server_slot',
            'price' => $config->getSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
        ],
        [
            'id' => 'server_backup',
            'price' => $config->getSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
        ],
        [
            'id' => 'server_allocation',
            'price' => $config->getSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
        ],
        [
            'id' => 'server_database',
            'price' => $config->getSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
        ],
    ];

    $filteredItems = [];
    foreach ($items as $item) {
        $skip = false;

        switch ($item['id']) {
            case 'ram':
                if ($config->getSetting(ConfigInterface::BLOCK_RAM, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'disk':
                if ($config->getSetting(ConfigInterface::BLOCK_DISK, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'cpu':
                if ($config->getSetting(ConfigInterface::BLOCK_CPU, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_allocation':
                if ($config->getSetting(ConfigInterface::BLOCK_PORTS, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_database':
                if ($config->getSetting(ConfigInterface::BLOCK_DATABASES, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_slot':
                if ($config->getSetting(ConfigInterface::BLOCK_SERVER_SLOTS, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_backup':
                if ($config->getSetting(ConfigInterface::BLOCK_BACKUPS, 'false') === 'true') {
                    $skip = true;
                }
                break;
        }

        if (!$skip) {
            $filteredItems[] = $item;
        }
    }

    $items = $filteredItems;

    $appInstance->OK('Store items fetched successfully!', ['data' => [
        'items' => $items,
    ]]);
});
