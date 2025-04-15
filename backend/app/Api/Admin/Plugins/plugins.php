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
use MythicalDash\Chat\User\Can;
use MythicalDash\Plugins\PluginConfig;
use MythicalDash\Chat\columns\UserColumns;

$router->get('/api/admin/plugins/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $plugins = $pluginManager->getLoadedMemoryPlugins();
        $pluginsList = [];
        foreach ($plugins as $plugin) {
            $info = PluginConfig::getConfig($plugin);
            $pluginsList[$plugin] = $info;
        }
        $appInstance->OK('Plugins fetched successfully', ['plugins' => $pluginsList]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
