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
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\RedirectLinks\RedirectLink;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/redirect-links', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDIRECT_LINKS_LIST, $session);
    $redirectLinks = RedirectLink::getAll();
    $appInstance->OK('Redirect links fetched successfully', ['redirect_links' => $redirectLinks]);
});

$router->post('/api/admin/redirect-links/create', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDIRECT_LINKS_CREATE, $session);
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        $appInstance->BadRequest('Name is required', ['error_code' => 'ERROR_NAME_REQUIRED']);

        return;
    }

    if (!isset($_POST['link']) || empty($_POST['link'])) {
        $appInstance->BadRequest('Link is required', ['error_code' => 'ERROR_LINK_REQUIRED']);

        return;
    }

    if (RedirectLink::doesNameAlreadyExist($_POST['name'])) {
        $appInstance->BadRequest('Name already exists', ['error_code' => 'ERROR_NAME_ALREADY_EXISTS']);

        return;
    }

    // Validate URL format
    if (!filter_var($_POST['link'], FILTER_VALIDATE_URL)) {
        $appInstance->BadRequest('Invalid URL format', ['error_code' => 'ERROR_INVALID_URL']);

        return;
    }

    $redirectLinkId = RedirectLink::create($_POST['name'], $_POST['link']);

    if ($redirectLinkId === 0) {
        $appInstance->InternalServerError('Failed to create redirect link record', ['error_code' => 'ERROR_FAILED_TO_CREATE_REDIRECT_LINK']);

        return;
    }

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_redirect_link_create,
        CloudFlareRealIP::getRealIP(),
        "Created redirect link $redirectLinkId"
    );

    $appInstance->OK('Redirect link created successfully', [
        'redirect_link' => [
            'id' => $redirectLinkId,
            'name' => $_POST['name'],
            'link' => $_POST['link'],
        ],
    ]);
});

$router->post('/api/admin/redirect-links/(.*)/update', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDIRECT_LINKS_EDIT, $session);
    if (!RedirectLink::exists($id)) {
        $appInstance->BadRequest('Redirect link not found', ['error_code' => 'REDIRECT_LINK_NOT_FOUND']);

        return;
    }

    if (!isset($_POST['name']) || empty($_POST['name'])) {
        $appInstance->BadRequest('Name is required', ['error_code' => 'ERROR_NAME_REQUIRED']);

        return;
    }

    if (!isset($_POST['link']) || empty($_POST['link'])) {
        $appInstance->BadRequest('Link is required', ['error_code' => 'ERROR_LINK_REQUIRED']);

        return;
    }

    // Validate URL format
    if (!filter_var($_POST['link'], FILTER_VALIDATE_URL)) {
        $appInstance->BadRequest('Invalid URL format', ['error_code' => 'ERROR_INVALID_URL']);

        return;
    }

    if (!RedirectLink::update($id, $_POST['name'], $_POST['link'])) {
        $appInstance->InternalServerError('Failed to update redirect link', ['error_code' => 'ERROR_FAILED_TO_UPDATE_REDIRECT_LINK']);

        return;
    }

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_redirect_link_update,
        CloudFlareRealIP::getRealIP(),
        "Updated redirect link $id"
    );

    $appInstance->OK('Redirect link updated successfully', [
        'redirect_link' => [
            'id' => $id,
            'name' => $_POST['name'],
            'link' => $_POST['link'],
        ],
    ]);
});

$router->post('/api/admin/redirect-links/(.*)/delete', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDIRECT_LINKS_DELETE, $session);
    if (!RedirectLink::exists($id)) {
        $appInstance->BadRequest('Redirect link not found', ['error_code' => 'REDIRECT_LINK_NOT_FOUND']);

        return;
    }

    if (!RedirectLink::delete($id)) {
        $appInstance->InternalServerError('Failed to delete redirect link', ['error_code' => 'ERROR_FAILED_TO_DELETE_REDIRECT_LINK']);

        return;
    }

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_redirect_link_delete,
        CloudFlareRealIP::getRealIP(),
        "Deleted redirect link $id"
    );

    $appInstance->OK('Redirect link deleted successfully', ['id' => $id]);
});

$router->get('/api/admin/redirect-links/(.*)', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_REDIRECT_LINKS_LIST, $session);
    if (!RedirectLink::exists($id)) {
        $appInstance->BadRequest('Redirect link not found', ['error_code' => 'REDIRECT_LINK_NOT_FOUND']);

        return;
    }

    $redirectLink = RedirectLink::get($id);

    $appInstance->OK('Redirect link fetched successfully', ['redirect_link' => $redirectLink]);
});
