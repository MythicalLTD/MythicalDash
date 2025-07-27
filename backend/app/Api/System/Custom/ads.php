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

$router->add('/api/system/ga-ads', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $googleAdsEnabled = $config->getDBSetting(ConfigInterface::GOOGLE_ADS_ENABLED, 'false');
    $googleAdsClientId = $config->getDBSetting(ConfigInterface::GOOGLE_ADS_CLIENT_ID, '');

    if ($googleAdsEnabled == 'true') {
        echo trim($googleAdsClientId) !== '' ? trim($googleAdsClientId) : 'null';
    } else {
        echo 'null';
    }
});
