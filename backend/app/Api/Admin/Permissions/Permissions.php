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
use MythicalDash\Chat\User\Permissions;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get permissions for a specific role
$router->get('/api/admin/roles/(.*)/permissions', function (int $roleId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		$permissions = Permissions::getPermissionsByRole($roleId);
		$appInstance->OK('Permissions fetched successfully', ['permissions' => $permissions]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Get a specific permission
$router->get('/api/admin/permissions/(.*)', function (int $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		$permission = Permissions::getPermission($id);
		if ($permission) {
			$appInstance->OK('Permission fetched successfully', ['permission' => $permission]);
		} else {
			$appInstance->NotFound('Permission not found', ['error_code' => 'PERMISSION_NOT_FOUND']);
		}
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Create a permission for a specific role
$router->post('/api/admin/roles/(.*)/permissions/create', function (int $roleId): void {
	App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		if (isset($_POST['permission']) && isset($_POST['granted'])) {
			$permission = $_POST['permission'];
			$granted = $_POST['granted'];
			$created = Permissions::createPermission($roleId, $permission, $granted);
			if ($created) {
				UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_permission_create, CloudFlareRealIP::getRealIP(), 'Permission created successfully');
				$appInstance->OK('Permission created successfully', []);
			} else {
				$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_CREATE_PERMISSION']);
			}
		} else {
			$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
		}
	} else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Update a permission
$router->post('/api/admin/permissions/update', function (): void {
	App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		if (isset($_POST['id']) && isset($_POST['role_id']) && isset($_POST['permission']) && isset($_POST['granted'])) {
			$id = $_POST['id'];
			$role_id = $_POST['role_id'];
			$permission = $_POST['permission'];
			$granted = $_POST['granted'];
			$updated = Permissions::updatePermission((int)$id, (int)$role_id, $permission, $granted);
			if ($updated) {
				UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_permission_update, CloudFlareRealIP::getRealIP(), 'Permission updated successfully');
				$appInstance->OK('Permission updated successfully', []);
			} else {
				$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_UPDATE_PERMISSION']);
			}
		} else {
			$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
		}
	} else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Delete a permission
$router->post('/api/admin/permissions/delete', function (): void {
	App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
		if (isset($_POST['id'])) {
			$id = $_POST['id'];
			$deleted = Permissions::deletePermission((int)$id);
			if ($deleted) {
				UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_permission_delete, CloudFlareRealIP::getRealIP(), 'Permission deleted successfully');
				$appInstance->OK('Permission deleted successfully', []);
			} else {
				$appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_DELETE_PERMISSION']);
			}
		} else {
			$appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
		}
	} else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
}); 