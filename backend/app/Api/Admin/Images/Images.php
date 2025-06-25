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
use MythicalDash\Permissions;
use MythicalDash\Chat\Images\Image;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/images', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGES_LIST, $session);
    $images = Image::getAll();
    $appInstance->OK('Images fetched successfully', ['images' => $images]);
});

$router->post('/api/admin/images/create', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGES_CREATE, $session);
    if (!isset($_FILES['image'])) {
        $appInstance->BadRequest('Image is required', ['error_code' => 'ERROR_IMAGE_REQUIRED']);

        return;
    }

    if (!isset($_POST['name']) || empty($_POST['name'])) {
        $appInstance->BadRequest('Name is required', ['error_code' => 'ERROR_NAME_REQUIRED']);

        return;
    }

    if (Image::doesNameAlreadyExist($_POST['name'])) {
        $appInstance->BadRequest('Name already exists', ['error_code' => 'ERROR_NAME_ALREADY_EXISTS']);

        return;
    }

    $image = $_FILES['image'];

    // Validate file type and size
    $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($image['type'], $allowedTypes)) {
        $appInstance->BadRequest('Invalid file type. Only PNG, JPG and GIF allowed', ['error_code' => 'ERROR_INVALID_FILE_TYPE']);

        return;
    }

    if ($image['size'] > $maxSize) {
        $appInstance->BadRequest('File too large. Maximum size is 2MB', ['error_code' => 'ERROR_FILE_TOO_LARGE']);

        return;
    }

    if ($image['error'] !== UPLOAD_ERR_OK) {
        $appInstance->BadRequest('File upload failed', ['error_code' => 'ERROR_UPLOAD_FAILED']);

        return;
    }

    try {
        // Create date-based folder structure
        $currentDate = date('Y-m-d');
        $uploadPath = APP_PUBLIC . '/attachments/images/' . $currentDate;

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($image['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }

        $relativePath = 'images/' . $currentDate . '/' . $filename;
        $imageId = Image::create($_POST['name'], '/attachments/' . $relativePath);

        if ($imageId === 0) {
            $appInstance->InternalServerError('Failed to create image record', ['error_code' => 'ERROR_FAILED_TO_CREATE_IMAGE']);

            return;
        }

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_image_create,
            CloudFlareRealIP::getRealIP(),
            "Created image $imageId"
        );

        $appInstance->OK('Image uploaded successfully', [
            'image' => [
                'id' => $imageId,
                'path' => '/attachments/' . $relativePath,
            ],
        ]);

    } catch (Exception $e) {
        $appInstance->InternalServerError('Failed to process image', ['error_code' => 'ERROR_PROCESSING_IMAGE']);

        return;
    }
});

$router->post('/api/admin/images/(.*)/delete', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGES_DELETE, $session);
    if (!Image::exists($id)) {
        $appInstance->BadRequest('Image not found', ['error_code' => 'IMAGE_NOT_FOUND']);

        return;
    }

    // Get image info before deleting
    $image = Image::get($id);

    // Delete the image file if exists
    if ($image && file_exists(APP_PUBLIC . $image['image'])) {
        unlink(APP_PUBLIC . $image['image']);
    }

    Image::delete($id);

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$admin_image_delete,
        CloudFlareRealIP::getRealIP(),
        "Deleted image $id"
    );

    $appInstance->OK('Image deleted successfully', ['id' => $id]);
});

$router->get('/api/admin/images/(.*)', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_IMAGES_LIST, $session);
    if (!Image::exists($id)) {
        $appInstance->BadRequest('Image not found', ['error_code' => 'IMAGE_NOT_FOUND']);

        return;
    }

    $image = Image::get($id);

    $appInstance->OK('Image fetched successfully', ['image' => $image]);
});
