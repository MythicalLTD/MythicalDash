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
use MythicalDash\Chat\Eggs\Eggs as EggManager;
use MythicalDash\Hooks\Pterodactyl\Admin\Eggs;
use MythicalDash\Plugins\Events\Events\EggsEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

// Get all eggs for a specific nest
$router->get('/api/admin/eggs/pterodactyl/(.*)/eggs', function ($nestId): void {
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

// Get all eggs from all nests
$router->get('/api/admin/eggs/pterodactyl', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $eggs = Eggs::getAllEggs();

        $appInstance->OK('All Pterodactyl eggs', [
            'eggs' => $eggs,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

// Get a specific egg by ID
$router->get('/api/admin/eggs/pterodactyl/(.*)', function ($eggId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $egg = Eggs::getEggById((int) $eggId);

        if ($egg) {
            $appInstance->OK('Pterodactyl egg', [
                'egg' => $egg,
            ]);
        } else {
            $appInstance->BadRequest('Egg not found', ['error_code' => 'ERROR_EGG_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/eggs', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $eggs = EggManager::getAll();

        $appInstance->OK('All eggs', [
            'eggs' => $eggs,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/eggs', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $eggs = EggManager::getAll();

        $appInstance->OK('Eggs retrieved successfully', [
            'eggs' => $eggs,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/eggs/(.*)/info', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $egg = EggManager::getById((int) $id);

        if ($egg) {
            $appInstance->OK('Egg retrieved successfully', [
                'egg' => $egg,
            ]);
        } else {
            $appInstance->BadRequest('Egg not found', ['error_code' => 'ERROR_EGG_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/eggs/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['category'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $enabled = $_POST['enabled'] ?? 'false';
            $pterodactylEggId = $_POST['pterodactyl_egg_id'];

            if ($name == '' || $description == '' || $category == '' || $pterodactylEggId == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
            }

            $category = intval($category);
            $pterodactylEggId = intval($pterodactylEggId);

            // Validate category exists
            if (!EggCategories::exists($category)) {
                $appInstance->BadRequest('Invalid category ID', ['error_code' => 'ERROR_INVALID_CATEGORY_ID']);
            }

            // Validate Pterodactyl egg exists
            if (!Eggs::doesEggExist($pterodactylEggId)) {
                $appInstance->BadRequest('Invalid Pterodactyl egg ID', ['error_code' => 'ERROR_INVALID_PTERODACTYL_EGG_ID']);
            }

            // Validate enabled value
            if ($enabled !== 'true' && $enabled !== 'false') {
                $enabled = 'false';
            }

            $id = EggManager::create($name, $description, $category, (int) $pterodactylEggId, $enabled);
            if (!$id) {
                $appInstance->BadRequest('Failed to create egg', ['error_code' => 'ERROR_FAILED_TO_CREATE_EGG']);
            }

            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_egg_create,
                CloudFlareRealIP::getRealIP()
            );

            global $eventManager;
            $eventManager->emit(EggsEvent::onCreateEgg(), [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'enabled' => $enabled,
                'pterodactyl_egg_id' => $pterodactylEggId,
            ]);

            $appInstance->OK('Egg created', [
                'egg' => [
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
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

$router->post('/api/admin/eggs/(.*)/update', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['category']) && isset($_POST['enabled'])) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $enabled = $_POST['enabled'];
            $pterodactylEggId = $_POST['pterodactyl_egg_id'];

            if ($name == '' || $description == '' || $category == '' || $enabled == '' || $pterodactylEggId == '') {
                $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

            }

            if (!EggManager::exists($id)) {
                $appInstance->BadRequest('Egg not found', ['error_code' => 'ERROR_EGG_NOT_FOUND']);

            }

            $category = intval($category);
            $pterodactylEggId = intval($pterodactylEggId);

            // Validate category exists
            if (!EggCategories::exists($category)) {
                $appInstance->BadRequest('Invalid category ID', ['error_code' => 'ERROR_INVALID_CATEGORY_ID']);

            }

            // Validate Pterodactyl egg exists
            if (!Eggs::doesEggExist($pterodactylEggId)) {
                $appInstance->BadRequest('Invalid Pterodactyl egg ID', ['error_code' => 'ERROR_INVALID_PTERODACTYL_EGG_ID']);
            }

            // Validate enabled value
            if ($enabled !== 'true' && $enabled !== 'false') {
                $enabled = 'false';
            }

            $updated = EggManager::update($id, $name, $description, $category, $pterodactylEggId, $enabled);
            if (!$updated) {
                $appInstance->BadRequest('Failed to update egg', ['error_code' => 'ERROR_FAILED_TO_UPDATE_EGG']);

            }

            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_egg_update,
                CloudFlareRealIP::getRealIP()
            );

            global $eventManager;
            $eventManager->emit(EggsEvent::onUpdateEgg(), [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'enabled' => $enabled,
                'pterodactyl_egg_id' => $pterodactylEggId,
            ]);

            $appInstance->OK('Egg updated', [
                'egg' => [
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'enabled' => $enabled,
                    'id' => $id,
                    'pterodactyl_egg_id' => $pterodactylEggId,
                ],
            ]);
        } else {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/eggs/(.*)/delete', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!EggManager::exists($id)) {
            $appInstance->BadRequest('Egg not found', ['error_code' => 'ERROR_EGG_NOT_FOUND']);
        }

        global $eventManager;
        $eventManager->emit(EggsEvent::onDeleteEgg(), [
            'id' => $id,
        ]);

        // TODO: Check if the egg is used by any servers

        $deleted = EggManager::delete($id);
        if (!$deleted) {
            $appInstance->BadRequest('Failed to delete egg', ['error_code' => 'ERROR_FAILED_TO_DELETE_EGG']);
        }

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_egg_delete,
            CloudFlareRealIP::getRealIP()
        );

        $appInstance->OK('Egg deleted', [
            'egg' => [
                'id' => $id,
            ],
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/eggs/category/(.*)', function ($categoryId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $categoryId = (int) $categoryId;

        // Check if category exists
        if (!EggCategories::exists($categoryId)) {
            $appInstance->BadRequest('Category not found', ['error_code' => 'ERROR_CATEGORY_NOT_FOUND']);

        }

        $eggs = EggManager::getByCategoryId($categoryId);

        $appInstance->OK('Eggs retrieved successfully', [
            'category_id' => $categoryId,
            'eggs' => $eggs,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
