<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Chat\User\Permissions;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Permissions as PermissionsIndex;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get permissions for a specific role
$router->get('/api/admin/roles/(.*)/permissions', function (int $roleId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, PermissionsIndex::ADMIN_PERMISSIONS_LIST, $session);
    $permissions = Permissions::getPermissionsByRole($roleId);
    $appInstance->OK('Permissions fetched successfully', ['permissions' => $permissions]);
});

// Get a specific permission
$router->get('/api/admin/permissions/(.*)', function (int $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, PermissionsIndex::ADMIN_PERMISSIONS_LIST, $session);
    $permission = Permissions::getPermission($id);
    if ($permission) {
        $appInstance->OK('Permission fetched successfully', ['permission' => $permission]);
    } else {
        $appInstance->NotFound('Permission not found', ['error_code' => 'PERMISSION_NOT_FOUND']);
    }
});

// Create a permission for a specific role
$router->post('/api/admin/roles/(.*)/permissions/create', function (int $roleId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, PermissionsIndex::ADMIN_PERMISSIONS_CREATE, $session);
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
});

// Update a permission
$router->post('/api/admin/permissions/update', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, PermissionsIndex::ADMIN_PERMISSIONS_EDIT, $session);
    if (isset($_POST['id']) && isset($_POST['role_id']) && isset($_POST['permission']) && isset($_POST['granted'])) {
        $id = $_POST['id'];
        $role_id = $_POST['role_id'];
        $permission = $_POST['permission'];
        $granted = $_POST['granted'];
        $updated = Permissions::updatePermission((int) $id, (int) $role_id, $permission, $granted);
        if ($updated) {
            UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_permission_update, CloudFlareRealIP::getRealIP(), 'Permission updated successfully');
            $appInstance->OK('Permission updated successfully', []);
        } else {
            $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_UPDATE_PERMISSION']);
        }
    } else {
        $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
    }
});

// Delete a permission
$router->post('/api/admin/permissions/delete', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, PermissionsIndex::ADMIN_PERMISSIONS_DELETE, $session);
    if (isset($_POST['id'])) {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            if ($id <= 0) {
                $appInstance->BadRequest('Invalid permission id', ['error_code' => 'INVALID_ID']);

                return;
            }
            $deleted = Permissions::deletePermission($id);
            if ($deleted) {
                UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_permission_delete, CloudFlareRealIP::getRealIP(), 'Permission deleted successfully');
                $appInstance->OK('Permission deleted successfully', []);
            } else {
                $appInstance->InternalServerError('Internal Server Error', ['error_code' => 'FAILED_TO_DELETE_PERMISSION']);
            }
        } else {
            $appInstance->BadRequest('Bad Request', ['error_code' => 'MISSING_PARAMETERS']);
        }
    }
});
