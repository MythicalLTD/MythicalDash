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

use MythicalDash\Plugins\PluginManager;

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

try {
    if (file_exists(APP_DIR . 'storage/packages')) {
        require APP_DIR . 'storage/packages/autoload.php';
    } else {
        throw new Exception('Packages not installed looked at this path: ' . APP_DIR . 'storage/packages');
    }
} catch (Exception $e) {
    echo $e->getMessage();
    echo "\n";
    exit;
}

if (!defined('IS_CLI')) {
    ini_set('expose_php', 'off');
    header_remove('X-Powered-By');
    header_remove('Server');
}

if (!is_writable(__DIR__)) {
    $error = 'Please make sure the root directory is writable.';
    exit(json_encode(['error' => $error, 'code' => 500, 'message' => 'Please make sure the root directory is writable.', 'success' => false]));
}

if (!is_writable(__DIR__ . '/../storage')) {
    exit(json_encode(['error' => 'Please make sure the storage directory is writable.', 'code' => 500, 'message' => 'Please make sure the storage directory is writable.', 'success' => false]));
}

if (file_exists(APP_DIR . 'storage/.env')) {
    /**
     * Initialize the plugin manager.
     */
    $pluginManager = new PluginManager();
    $eventManager = $pluginManager->getEventManager();

    /**
     * @global \MythicalDash\Plugins\PluginManager $pluginManager
     * @global \MythicalDash\Plugins\Events\PluginEvent $eventManager
     */
    global $pluginManager, $eventManager;
}
