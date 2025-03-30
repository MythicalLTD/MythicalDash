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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Locations\Locations;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\LocationEvent;

$router->get('/api/admin/users', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyGET();
	$session = new MythicalDash\Chat\User\Session($appInstance);

	if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		$user = User::getListWithFilters(['id', 'username', 'first_name', 'last_name', 'email', 'avatar', 'pterodactyl_user_id', 'role', 'last_seen'], ['first_name', 'last_name']);

		$appInstance->OK('Users data retrieved successfully.', [
			'users' => $user
		]);
	} else {
		$appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
	}
});

