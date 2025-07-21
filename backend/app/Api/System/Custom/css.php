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

$router->add('/api/system/custom.css', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $customCss = $config->getSetting(ConfigInterface::CUSTOM_CSS, '');

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
