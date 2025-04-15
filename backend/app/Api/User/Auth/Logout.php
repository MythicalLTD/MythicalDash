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
use MythicalDash\Hooks\MythicalSystems\Debugger;
use MythicalDash\Plugins\Events\Events\AuthEvent;
Debugger::ShowAllErrors();
$router->get('/api/user/auth/logout', function (): void {
    global $eventManager;
	header('location: /auth/logout');
});
