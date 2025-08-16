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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;

$router->add('/api/system/custom.css', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $customCss = $config->getDBSetting(ConfigInterface::CUSTOM_CSS, '');

    header('Content-Type: text/css');
    echo "// Custom CSS\n";
    echo $customCss;
    echo "\n";
    echo "// Plugin CSS\n";
    // Append plugin JS
    $pluginDir = __DIR__ . '/../../../../storage/addons';
    if (is_dir($pluginDir)) {
        $plugins = array_diff(scandir($pluginDir), ['.', '..']);
        foreach ($plugins as $plugin) {
            $cssPath = $pluginDir . "/$plugin/CSS/index.css";
            if (file_exists($cssPath)) {
                echo "\n// Plugin: $plugin\n";
                echo file_get_contents($cssPath) . "\n";
            }
        }
    }
});
