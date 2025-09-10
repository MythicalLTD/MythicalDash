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
use MythicalDash\Permissions;
use MythicalDash\Chat\User\Roles;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\Chat\columns\RolesColumns;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\RolesEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/roles/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_LIST, $session);
    $roles = Roles::getList();
    $appInstance->OK('Roles fetched successfully', ['roles' => $roles]);
});

$router->post('/api/admin/roles/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    global $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_CREATE, $session);
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
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_EDIT, $session);
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
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_DELETE, $session);
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
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROLES_LIST, $session);
    $role = Roles::getRole($id);
    if ($role) {
        $appInstance->OK('Role fetched successfully', ['role' => $role]);
    } else {
        $appInstance->NotFound('Role not found', ['error_code' => 'ROLE_NOT_FOUND']);
    }
});
