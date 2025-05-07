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
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\RedirectLinks\RedirectLink;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/redirect-links', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $redirectLinks = RedirectLink::getAll();
        $appInstance->OK('Redirect links fetched successfully', ['redirect_links' => $redirectLinks]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/redirect-links/create', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/redirect-links/(.*)/update', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/redirect-links/(.*)/delete', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/redirect-links/(.*)', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!RedirectLink::exists($id)) {
            $appInstance->BadRequest('Redirect link not found', ['error_code' => 'REDIRECT_LINK_NOT_FOUND']);

            return;
        }

        $redirectLink = RedirectLink::get($id);

        $appInstance->OK('Redirect link fetched successfully', ['redirect_link' => $redirectLink]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
