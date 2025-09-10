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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Tickets\Departments;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DepartmentsEvent;

$router->get('/api/admin/ticket/departments', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DEPARTMENTS_LIST, $session);
    $departments = Departments::getAll();

    $appInstance->OK('Departments retrieved successfully.', [
        'departments' => $departments,
    ]);

});

$router->post('/api/admin/ticket/departments/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DEPARTMENTS_CREATE, $session);
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['open']) && isset($_POST['close']) && isset($_POST['enabled'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $open = $_POST['open'];
        $close = $_POST['close'];
        $enabled = $_POST['enabled'];
        if ($name == '' || $description == '' || $open == '' || $close == '') {
            $appInstance->BadRequest('Missing required fields.', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
        if ($enabled != 'true' && $enabled != 'false') {
            $appInstance->BadRequest('Invalid enabled value.', ['error_code' => 'INVALID_ENABLED_VALUE']);
        }
        $departmentId = Departments::create($name, $description, $open, $close, $enabled);

        if ($departmentId === 0) {
            $appInstance->BadRequest('Failed to create department.', ['error_code' => 'FAILED_TO_CREATE_DEPARTMENT']);
        }
        if ($departmentId === false) {
            $appInstance->BadRequest('Failed to create department.', ['error_code' => 'FAILED_TO_CREATE_DEPARTMENT']);
        }
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_ticket_department_create,
            CloudFlareRealIP::getRealIP(),
        );

        global $eventManager;
        $eventManager->emit(DepartmentsEvent::onCreateDepartment(), [
            'id' => $departmentId,
            'name' => $name,
            'description' => $description,
            'open' => $open,
            'close' => $close,
        ]);

        $appInstance->OK('Department created successfully.', [
            'department' => [
                'id' => $departmentId,
                'name' => $name,
                'description' => $description,
                'open' => $open,
                'close' => $close,
            ],
        ]);
    } else {
        $appInstance->BadRequest('Missing required fields.', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }

});

$router->post('/api/admin/ticket/departments/(.*)/update', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DEPARTMENTS_EDIT, $session);
    $departmentId = intval($id);
    if ($departmentId == 0) {
        $appInstance->BadRequest('Invalid department ID.', ['error_code' => 'INVALID_DEPARTMENT_ID']);
    }
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['open']) && isset($_POST['close']) && isset($_POST['enabled'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $open = $_POST['open'];
        $close = $_POST['close'];
        $enabled = $_POST['enabled'];
    }
    if ($name == '' || $description == '' || $open == '' || $close == '') {
        $appInstance->BadRequest('Missing required fields.', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }
    if ($enabled != 'true' && $enabled != 'false') {
        $appInstance->BadRequest('Invalid enabled value.', ['error_code' => 'INVALID_ENABLED_VALUE']);
    }
    $departmentId = Departments::update($departmentId, $name, $description, $open, $close, $enabled);
    if ($departmentId === false) {
        $appInstance->BadRequest('Failed to update department.', ['error_code' => 'FAILED_TO_UPDATE_DEPARTMENT']);
    }
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_ticket_department_update,
        CloudFlareRealIP::getRealIP(),
    );
    global $eventManager;
    $eventManager->emit(DepartmentsEvent::onUpdateDepartment(), [
        'id' => $departmentId,
        'name' => $name,
        'description' => $description,
        'open' => $open,
        'close' => $close,
    ]);
    $appInstance->OK('Department updated successfully.', [
        'department' => [
            'id' => $departmentId,
            'name' => $name,
            'description' => $description,
            'open' => $open,
            'close' => $close,
            'enabled' => $enabled,
        ],
    ]);

});

$router->post('/api/admin/ticket/departments/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DEPARTMENTS_DELETE, $session);
    $departmentId = intval($id);
    if ($departmentId == 0) {
        $appInstance->BadRequest('Invalid department ID.', ['error_code' => 'INVALID_DEPARTMENT_ID']);
    }

    $department = Departments::getById($departmentId);
    if ($department === null) {
        $appInstance->BadRequest('Department not found.', ['error_code' => 'DEPARTMENT_NOT_FOUND']);
    }

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_ticket_department_delete,
        CloudFlareRealIP::getRealIP(),
    );

    global $eventManager;
    $eventManager->emit(DepartmentsEvent::onDeleteDepartment(), [
        'id' => $departmentId,
    ]);

    $departmentId = Departments::delete($departmentId);
    if ($departmentId === false) {
        $appInstance->BadRequest('Failed to delete department.', ['error_code' => 'FAILED_TO_DELETE_DEPARTMENT']);
    }
    $appInstance->OK('Department deleted successfully.', []);

});
