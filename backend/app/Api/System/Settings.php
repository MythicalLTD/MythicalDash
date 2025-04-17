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
use MythicalDash\Config\PublicConfig;

$router->add('/api/system/settings', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $settingsPublic = PublicConfig::getPublicSettingsWithDefaults();
    // Retrieve actual settings with defaults in a single array map
    $settings = $config->getSettings(array_keys($settingsPublic));

    // Fill in any missing settings with defaults
    foreach ($settingsPublic as $key => $defaultValue) {
        if (!isset($settings[$key])) {
            $settings[$key] = $defaultValue;
        }
    }

    App::OK('Sure here are the settings you were looking for', ['settings' => $settings]);
});
