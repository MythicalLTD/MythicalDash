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
            'id' => 'ram_512mb',
            'name' => 'RAM Upgrade',
            'description' => '512MB of additional memory for your server',
            'price' => 150,
            'category' => 'ram',
            'totalAmount' => 512,
            'usedAmount' => 512,
            'percentUsed' => 100,
            'unit' => 'MB',
            'features' => [
                'Instant activation',
                'Improved server performance',
                'Handle more concurrent users',
            ],
            'stock' => 10,
        ],
        [
            'id' => 'disk_1024mb',
            'name' => 'Disk Storage',
            'description' => '1024MB of additional storage space',
            'price' => 200,
            'category' => 'disk',
            'totalAmount' => 1024,
            'usedAmount' => 1024,
            'percentUsed' => 100,
            'unit' => 'MB',
            'features' => [
                'High-speed SSD storage',
                'Store more files and data',
                'No I/O limitations',
            ],
            'stock' => 15,
        ],
        [
            'id' => 'cpu_50pct',
            'name' => 'CPU Resource',
            'description' => '50% CPU allocation for your server',
            'price' => 300,
            'category' => 'cpu',
            'totalAmount' => 50,
            'usedAmount' => 50,
            'percentUsed' => 100,
            'unit' => '%',
            'features' => [
                'Dedicated CPU resources',
                'Faster response times',
                'Better overall performance',
            ],
            'stock' => 5,
        ],
        [
            'id' => 'server_slot',
            'name' => 'Server Slot',
            'description' => 'Additional server deployment slot',
            'price' => 500,
            'category' => 'slots',
            'totalAmount' => 1,
            'usedAmount' => 1,
            'percentUsed' => 100,
            'unit' => '',
            'features' => [
                'Deploy an additional server',
                'Separate resources and configurations',
                'Independent management',
            ],
            'stock' => 3,
        ],
        [
            'id' => 'server_backup',
            'name' => 'Server Backup',
            'description' => 'Daily automatic server backup slot',
            'price' => 150,
            'category' => 'backups',
            'totalAmount' => 1,
            'usedAmount' => 1,
            'percentUsed' => 100,
            'unit' => '',
            'features' => [
                'Automatic daily backups',
                'One-click restore capability',
                '7-day retention period',
            ],
            'stock' => 10,
        ],
        [
            'id' => 'server_allocation',
            'name' => 'Server Allocation',
            'description' => 'Additional IP/Port allocation',
            'price' => 100,
            'category' => 'allocations',
            'totalAmount' => 1,
            'usedAmount' => 1,
            'percentUsed' => 100,
            'unit' => '',
            'features' => [
                'Additional IP address and port',
                'Support for multiple services',
                'Improved network configuration',
            ],
            'stock' => 20,
        ],
        [
            'id' => 'server_database',
            'name' => 'Server Database',
            'description' => 'Additional database for your applications',
            'price' => 200,
            'category' => 'databases',
            'totalAmount' => 1,
            'usedAmount' => 1,
            'percentUsed' => 100,
            'unit' => '',
            'features' => [
                'MySQL/MariaDB database',
                'Full database management',
                'Automated backups',
            ],
            'stock' => 8,
        ],
    ];

    $appInstance->OK('Store items fetched successfully!', ['data' => [
        'items' => $items,
    ]]);
});
