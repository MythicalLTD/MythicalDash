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
use MythicalDash\Chat\Tickets\Departments;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\DepartmentsEvent;

$router->get('/api/admin/ticket/departments', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $departments = Departments::getAll();

        $appInstance->OK('Departments retrieved successfully.', [
            'departments' => $departments,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/ticket/departments/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/ticket/departments/(.*)/update', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/ticket/departments/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
