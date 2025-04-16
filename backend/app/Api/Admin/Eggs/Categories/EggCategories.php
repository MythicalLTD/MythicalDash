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
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Eggs;
use MythicalDash\Hooks\Pterodactyl\Admin\Nests;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\EggCategoriesEvent;

$router->get('/api/admin/egg-categories/pterodactyl-nests', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $nests = Nests::getNests();

        $appInstance->OK('Pterodactyl nests', [
            'nests' => $nests,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/egg-categories/pterodactyl-nests/(.*)/eggs', function ($nestId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $eggs = Eggs::getEggs((int) $nestId);

        $appInstance->OK('Pterodactyl eggs', [
            'eggs' => $eggs,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/egg-categories', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $categories = EggCategories::getCategories();

        $appInstance->OK('Egg Categories', [
            'categories' => $categories,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/egg-categories/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_egg_category_create,
        CloudFlareRealIP::getRealIP()
    );

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['pterodactyl_nest_id'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pterodactyl_nest_id = $_POST['pterodactyl_nest_id'];
            $enabled = isset($_POST['enabled']) ? filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN) : true;

            if ($name == '' || $description == '' || $pterodactyl_nest_id == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

                return;
            }

            $pterodactyl_nest_id = intval($pterodactyl_nest_id);

            if (EggCategories::existsByPterodactylNestId($pterodactyl_nest_id)) {
                $appInstance->BadRequest('Category with this Pterodactyl nest ID already exists', ['error_code' => 'ERROR_CATEGORY_ALREADY_EXISTS']);

                return;
            }

            if (!Nests::doesNestExist($pterodactyl_nest_id)) {
                $appInstance->BadRequest('Invalid Pterodactyl nest ID', ['error_code' => 'ERROR_INVALID_PTERODACTYL_NEST_ID']);

                return;
            }

            $id = EggCategories::create($name, $description, $pterodactyl_nest_id, $enabled);
            if ($id == 0) {
                $appInstance->BadRequest('Failed to create egg category', ['error_code' => 'ERROR_FAILED_TO_CREATE_CATEGORY']);

                return;
            }

            global $eventManager;
            $eventManager->emit(EggCategoriesEvent::onCreateEggCategory(), [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'pterodactyl_nest_id' => $pterodactyl_nest_id,
                'enabled' => $enabled,
            ]);
            $appInstance->OK('Egg category created', [
                'category' => [
                    'name' => $name,
                    'description' => $description,
                    'pterodactyl_nest_id' => $pterodactyl_nest_id,
                    'enabled' => $enabled,
                    'id' => $id,
                ],
            ]);
        } else {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/egg-categories/(.*)/update', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['enabled'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $enabled = $_POST['enabled'];

            if ($name == '' || $description == '' || $enabled == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

                return;
            }

            if (!EggCategories::exists($id)) {
                $appInstance->BadRequest('Egg category not found', ['error_code' => 'ERROR_CATEGORY_NOT_FOUND']);

                return;
            }

            $updated = EggCategories::update($id, $name, $description, $enabled);
            if (!$updated) {
                $appInstance->BadRequest('Failed to update egg category', ['error_code' => 'ERROR_FAILED_TO_UPDATE_CATEGORY']);

                return;
            }

            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_egg_category_update,
                CloudFlareRealIP::getRealIP()
            );

            global $eventManager;
            $eventManager->emit(EggCategoriesEvent::onUpdateEggCategory(), [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'enabled' => $enabled,
            ]);

            $appInstance->OK('Egg category updated', [
                'category' => [
                    'name' => $name,
                    'description' => $description,
                    'enabled' => $enabled,
                    'id' => $id,
                ],
            ]);
        } else {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/egg-categories/(.*)/delete', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!EggCategories::exists($id)) {
            $appInstance->BadRequest('Egg category not found', ['error_code' => 'ERROR_CATEGORY_NOT_FOUND']);

            return;
        }
        global $eventManager;
        $eventManager->emit(EggCategoriesEvent::onDeleteEggCategory(), [
            'id' => $id,
        ]);

        $deleted = EggCategories::delete($id);
        if (!$deleted) {
            $appInstance->BadRequest('Failed to delete egg category', ['error_code' => 'ERROR_FAILED_TO_DELETE_CATEGORY']);

            return;
        }

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_egg_category_delete,
            CloudFlareRealIP::getRealIP()
        );

        $appInstance->OK('Egg category deleted', [
            'category' => [
                'id' => $id,
            ],
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
