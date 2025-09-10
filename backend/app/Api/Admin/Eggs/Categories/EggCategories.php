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
use MythicalDash\Chat\Eggs\EggCategories;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Eggs;
use MythicalDash\Hooks\Pterodactyl\Admin\Nests;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\EggCategoriesEvent;

$router->get('/api/admin/egg-categories/pterodactyl-nests', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_LIST, $session);
    $nests = Nests::getNests();

    if (empty($nests)) {
        $appInstance->BadRequest('No nests found', ['error_code' => 'ERROR_NO_NESTS_FOUND']);
    } else {
        $appInstance->OK('Pterodactyl nests', [
            'nests' => $nests,
        ]);
    }
});

$router->get('/api/admin/egg-categories/pterodactyl-nests/(.*)/eggs', function ($nestId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_LIST, $session);
    $eggs = Eggs::getEggs((int) $nestId);

    if (empty($eggs)) {
        $appInstance->BadRequest('No eggs found', ['error_code' => 'ERROR_NO_EGGS_FOUND']);
    } else {
        $appInstance->OK('Pterodactyl eggs', [
            'eggs' => $eggs,
        ]);
    }
});

$router->get('/api/admin/egg-categories', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_LIST, $session);
    $categories = EggCategories::getCategories();

    if (empty($categories)) {
        $appInstance->BadRequest('No categories found', ['error_code' => 'ERROR_NO_CATEGORIES_FOUND']);
    } else {
        $appInstance->OK('Egg Categories', [
            'categories' => $categories,
        ]);
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

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_CREATE, $session);
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['pterodactyl_nest_id']) && isset($_POST['image_id'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $pterodactyl_nest_id = $_POST['pterodactyl_nest_id'];
        $enabled = isset($_POST['enabled']) ? filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN) : true;
        $image_id = $_POST['image_id'];
        if ($name == '' || $description == '' || $pterodactyl_nest_id == '' || $image_id == '') {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

            return;
        }

        if ($image_id == 'null') {
            $image_id = null;
        } else {
            $image_id = intval($image_id);
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

        $id = EggCategories::create($name, $description, $pterodactyl_nest_id, $enabled, $image_id);
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
});

$router->post('/api/admin/egg-categories/(.*)/update', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_EDIT, $session);
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['enabled']) && isset($_POST['image_id'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $enabled = $_POST['enabled'];
        $image_id = $_POST['image_id'];
        if ($name == '' || $description == '' || $enabled == '' || $image_id == '') {
            $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);

            return;
        }

        if ($image_id == 'null') {
            $image_id = null;
        } else {
            $image_id = intval($image_id);
        }

        if (!EggCategories::exists($id)) {
            $appInstance->BadRequest('Egg category not found', ['error_code' => 'ERROR_CATEGORY_NOT_FOUND']);

            return;
        }

        $updated = EggCategories::update($id, $name, $description, $enabled, $image_id);
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
});

$router->post('/api/admin/egg-categories/(.*)/delete', function ($id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_NESTS_DELETE, $session);
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

});
