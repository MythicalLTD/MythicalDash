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
use MythicalDash\Chat\columns\RolesColumns;
use MythicalDash\Chat\User\Roles;
use MythicalDash\Plugins\Events\Events\RolesEvent;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Permissions;

$router->get('/api/admin/roles/list', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyGET();
	global $pluginManager;
	$session = new MythicalDash\Chat\User\Session($appInstance);
	PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_LIST);
	$roles = Roles::getList();
	$appInstance->OK('Roles fetched successfully', ['roles' => $roles]);
});



$router->post('/api/admin/roles/create', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyPOST();
	global $eventManager;
	$session = new MythicalDash\Chat\User\Session($appInstance);
	PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_CREATE);
	if (isset($_POST['name']) && isset($_POST['color']) && isset($_POST['real_name'])) {
		$name = $_POST['name'];
		$color = $_POST['color'];
		$real_name = $_POST['real_name'];
		$created = Roles::createRole($name, $real_name, $color);
		if ($created) {
			UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_role_create, CloudFlareRealIP::getRealIP(), 'Role created successfully');
			$eventManager->emit(RolesEvent::onRoleCreated(), [$name, $real_name, $color]);
			$appInstance->OK('Role created successfully', []);
		} else {
			$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_CREATE_ROLE']);
		}
	} else {
		$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
	}
});

$router->post('/api/admin/roles/update', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyPOST();
	global $eventManager;
	$session = new MythicalDash\Chat\User\Session($appInstance);
	PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_EDIT);
	if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['color']) && isset($_POST['real_name'])) {
		$id = $_POST['id'];
		$name = $_POST['name'];
		$color = $_POST['color'];
		$real_name = $_POST['real_name'];
		$updated = Roles::updateInfo((int) $id, RolesColumns::$name, $name);
		$updated = Roles::updateInfo((int) $id, RolesColumns::$real_name, $real_name);
		$updated = Roles::updateInfo((int) $id, RolesColumns::$color, $color);
		if ($updated) {
			UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_role_update, CloudFlareRealIP::getRealIP(), 'Role updated successfully');
			$eventManager->emit(RolesEvent::onRoleUpdated(), [$id, $name, $real_name, $color]);
			$appInstance->OK('Role updated successfully', []);
		} else {
			$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_UPDATE_ROLE']);
		}
	} else {
		$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
	}
});

$router->post('/api/admin/roles/delete', function (): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyPOST();
	global $eventManager;
	$session = new MythicalDash\Chat\User\Session($appInstance);
	PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_DELETE);
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		$deleted = Roles::deleteRole((int) $id);
		if ($deleted) {
			UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_role_delete, CloudFlareRealIP::getRealIP(), 'Role deleted successfully');
			$eventManager->emit(RolesEvent::onRoleDeleted(), [$id]);
			$appInstance->OK('Role deleted successfully', []);
		} else {
			$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_DELETE_ROLE']);
		}
	} else {
		$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
	}
});

$router->get('/api/admin/roles/(.*)', function (int $id): void {
	App::init();
	$appInstance = App::getInstance(true);
	$appInstance->allowOnlyGET();
	$session = new MythicalDash\Chat\User\Session($appInstance);
	PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_LIST);
	$role = Roles::getRole($id);
	if ($role) {
		$appInstance->OK('Role fetched successfully', ['role' => $role]);
	} else {
		$appInstance->NotFound('Role not found', ['error_code' => 'ROLE_NOT_FOUND']);
	}
});