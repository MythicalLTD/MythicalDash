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
use MythicalDash\Chat\RedirectLinks\RedirectLink;

$router->get('/api/system/redirect-links', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();

    $redirectLinks = RedirectLink::getAll();
    $appInstance->OK('Redirect links fetched successfully', ['redirect_links' => $redirectLinks]);

});
