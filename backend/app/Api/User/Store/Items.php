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

$router->get('/api/user/store/items', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();
    // Check if store is enabled
    if ($config->getDBSetting(ConfigInterface::STORE_ENABLED, 'false') !== 'true') {
        $appInstance->BadRequest('Store is not enabled', ['error_code' => 'STORE_NOT_ENABLED']);
    }

    $items = [
        [
            'id' => 'ram',
            'price' => $config->getDBSetting(ConfigInterface::STORE_RAM_PRICE, 150), 0,
        ],
        [
            'id' => 'disk',
            'price' => $config->getDBSetting(ConfigInterface::STORE_DISK_PRICE, 200),
        ],
        [
            'id' => 'cpu',
            'price' => $config->getDBSetting(ConfigInterface::STORE_CPU_PRICE, 300),
        ],
        [
            'id' => 'server_slot',
            'price' => $config->getDBSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, 500),
        ],
        [
            'id' => 'server_backup',
            'price' => $config->getDBSetting(ConfigInterface::STORE_BACKUPS_PRICE, 150),
        ],
        [
            'id' => 'server_allocation',
            'price' => $config->getDBSetting(ConfigInterface::STORE_PORTS_PRICE, 100),
        ],
        [
            'id' => 'server_database',
            'price' => $config->getDBSetting(ConfigInterface::STORE_DATABASES_PRICE, 200),
        ],
    ];

    $filteredItems = [];
    foreach ($items as $item) {
        $skip = false;

        switch ($item['id']) {
            case 'ram':
                if ($config->getDBSetting(ConfigInterface::BLOCK_RAM, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'disk':
                if ($config->getDBSetting(ConfigInterface::BLOCK_DISK, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'cpu':
                if ($config->getDBSetting(ConfigInterface::BLOCK_CPU, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_allocation':
                if ($config->getDBSetting(ConfigInterface::BLOCK_PORTS, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_database':
                if ($config->getDBSetting(ConfigInterface::BLOCK_DATABASES, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_slot':
                if ($config->getDBSetting(ConfigInterface::BLOCK_SERVER_SLOTS, 'false') === 'true') {
                    $skip = true;
                }
                break;
            case 'server_backup':
                if ($config->getDBSetting(ConfigInterface::BLOCK_BACKUPS, 'false') === 'true') {
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
