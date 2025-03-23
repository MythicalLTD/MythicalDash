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

$router->add('/api/system/license', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $licenseValidator = $appInstance->getLicenseValidator();
    if ($licenseValidator->validate()) {
        App::OK('License is valid!', ['valid' => true]);
    } else {
        App::OK('License is invalid!', ['valid' => false]);
    }
});
