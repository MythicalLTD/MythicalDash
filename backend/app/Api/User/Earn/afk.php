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
use MythicalDash\Config\ConfigInterface;

$router->post('/api/user/earn/afk/work', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    if ($config->getSetting(ConfigInterface::AFK_ENABLED, 'false') === 'false') {
        App::NotFound('AFK is not enabled', []);
    }

    App::OK('Coins and AFK time added successfully', []);
});

$router->get('/api/user/earn/afk/work', function (): void {
    App::init();

    $appInstance = App::getInstance(true);

    $config = $appInstance->getConfig();

    if ($config->getSetting(ConfigInterface::AFK_ENABLED, 'false') === 'false') {
        App::NotFound('AFK is not enabled', []);
    } else {
        App::OK('AFK is enabled', []);
    }
});
