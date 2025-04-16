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
use MythicalDash\Chat\Announcements\Announcements;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Chat\Announcements\AnnouncementsTags;
use MythicalDash\Chat\Announcements\AnnouncementsAssets;
use MythicalDash\Plugins\Events\Events\AnnouncementsEvent;

$router->get('/api/admin/announcements', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $announcements = Announcements::getAll();
        $appInstance->OK('Announcements retrieved successfully.', [
            'announcements' => $announcements,
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/create', function () {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $title = $appInstance->getPostOrNull('title');
        $shortDescription = $appInstance->getPostOrNull('shortDescription');
        $description = $appInstance->getPostOrNull('description');

        $title = trim($title);
        $shortDescription = trim($shortDescription);
        $description = trim($description);

        if ($title === null || $shortDescription === null || $description === null) {
            $appInstance->BadRequest('Invalid request', ['error_code' => 'INVALID_REQUEST']);
        }

        $id = Announcements::create(
            $title,
            $shortDescription,
            $description
        );

        if ($id === 0) {
            $appInstance->InternalServerError('Failed to create announcement', ['error_code' => 'FAILED_TO_CREATE_ANNOUNCEMENT']);
        }

        $eventManager->emit(AnnouncementsEvent::onCreateAnnouncement(), [
            'id' => $id,
            'title' => $title,
            'shortDescription' => $shortDescription,
            'description' => $description,
        ]);

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$announcement_create,
            CloudFlareRealIP::getRealIP(),
            "Created announcement $id"
        );

        $appInstance->OK(
            'Announcement created successfully.',
            [
                'announcement' => [
                    'id' => $id,
                    'title' => $title,
                    'shortDescription' => $shortDescription,
                    'description' => $description,
                ],
            ]
        );
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/(.*)/update', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    global $eventManager;
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $title = $appInstance->getPostOrNull('title');
        $shortDescription = $appInstance->getPostOrNull('shortDescription');
        $description = $appInstance->getPostOrNull('description');
        if ($title === null || $shortDescription === null || $description === null) {
            $appInstance->BadRequest('Invalid request', ['error_code' => 'INVALID_REQUEST']);
        }

        if (!Announcements::exists($id)) {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }

        Announcements::update(
            $id,
            $title,
            $shortDescription,
            $description
        );

        $eventManager->emit(AnnouncementsEvent::onUpdateAnnouncement(), [
            'id' => $id,
            'title' => $title,
            'shortDescription' => $shortDescription,
            'description' => $description,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$announcement_update,
            CloudFlareRealIP::getRealIP(),
            "Updated announcement $id"
        );

        $appInstance->OK('Announcement updated successfully.', [
            'announcement' => [
                'id' => $id,
                'title' => $title,
                'shortDescription' => $shortDescription,
                'description' => $description,
            ],
        ]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/(.*)/tags/add', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    global $eventManager;
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $tag = $appInstance->getPostOrNull('tag');

        if ($tag === null) {
            $appInstance->BadRequest('Invalid request', ['error_code' => 'INVALID_REQUEST']);
        }

        if (!Announcements::exists($id)) {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }
        if (Announcements::existsTag($id, $tag)) {
            $appInstance->BadRequest('Announcement tag already exists', ['error_code' => 'ANNOUNCEMENT_TAG_ALREADY_EXISTS']);
        }
        $tagId = AnnouncementsTags::create($id, $tag);
        if ($tagId === 0) {
            $appInstance->InternalServerError('Failed to create announcement tag', ['error_code' => 'FAILED_TO_CREATE_ANNOUNCEMENT_TAG']);
        }

        $eventManager->emit(AnnouncementsEvent::onAnnouncementsAddTag(), [
            'tag' => $tag,
            'tagId' => $tagId,
            'announcementId' => $id,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$announcement_tag_create,
            CloudFlareRealIP::getRealIP(),
            "Created announcement tag $tagId"
        );

        $appInstance->OK('Announcement tag created successfully.', ['id' => $tagId]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/(.*)/assets/add', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    global $eventManager;
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!Announcements::exists((int) $id)) {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }

        if (!isset($_FILES['attachments'])) {
            $appInstance->BadRequest('Attachments are required', ['error_code' => 'ERROR_ATTACHMENTS_REQUIRED']);

            return;
        }

        $attachments = $_FILES['attachments'];

        // Always treat as single file since frontend sends one at a time
        $attachments = [
            'name' => [$attachments['name']],
            'type' => [$attachments['type']],
            'tmp_name' => [$attachments['tmp_name']],
            'error' => [$attachments['error']],
            'size' => [$attachments['size']],
        ];

        // Validate file types and sizes
        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $maxFiles = 5;

        // Now we can safely check count
        if (count($attachments['name']) > $maxFiles) {
            $appInstance->BadRequest("Maximum {$maxFiles} files allowed", ['error_code' => 'ERROR_TOO_MANY_FILES']);

            return;
        }

        // Validate files first
        for ($i = 0; $i < count($attachments['name']); ++$i) {
            if (!in_array($attachments['type'][$i], $allowedTypes)) {
                $appInstance->BadRequest('Invalid file type. Only PNG, JPG and GIF allowed', ['error_code' => 'ERROR_INVALID_FILE_TYPE']);

                return;
            }

            if ($attachments['size'][$i] > $maxSize) {
                $appInstance->BadRequest('File too large. Maximum size is 2MB', ['error_code' => 'ERROR_FILE_TOO_LARGE']);

                return;
            }

            if ($attachments['error'][$i] !== UPLOAD_ERR_OK) {
                $appInstance->BadRequest('File upload failed', ['error_code' => 'ERROR_UPLOAD_FAILED']);

                return;
            }
        }

        // Upload files
        try {
            // Create date-based folder structure
            $currentDate = date('Y-m-d');
            $uploadPath = APP_PUBLIC . '/attachments/announcements/' . $id . '/' . $currentDate;

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $uploadedFiles = [];
            for ($i = 0; $i < count($attachments['name']); ++$i) {
                $extension = pathinfo($attachments['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadPath . '/' . $filename;

                if (!move_uploaded_file($attachments['tmp_name'][$i], $destination)) {
                    throw new Exception('Failed to move uploaded file');
                }

                // Store attachment info in database
                $relativePath = 'announcements/' . $id . '/' . $currentDate . '/' . $filename;
                $uploadedFiles[] = $relativePath;
            }
            foreach ($uploadedFiles as $file) {
                $ancID = AnnouncementsAssets::create($id, '/attachments/' . $file);
                if ($ancID === 0) {
                    $appInstance->InternalServerError('Failed to create announcement asset', ['error_code' => 'ERROR_FAILED_TO_CREATE_ANNOUNCEMENT_ASSET']);

                    return;
                }
            }

            $eventManager->emit(AnnouncementsEvent::onAnnouncementsAddAttachment(), [
                'announcementId' => $id,
                'files' => $uploadedFiles,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$announcement_asset_create,
                CloudFlareRealIP::getRealIP(),
                "Created announcement asset $id"
            );

            $appInstance->OK(200, [
                'message' => 'Attachments uploaded successfully',
                'files' => $uploadedFiles,

            ]);

        } catch (Exception $e) {
            $appInstance->InternalServerError('Failed to process attachments', ['error_code' => 'ERROR_PROCESSING_ATTACHMENTS']);

            return;
        }

    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/announcements/(.*)/assets', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (Announcements::exists((int) $id)) {
            $assets = AnnouncementsAssets::getAll((int) $id);
            $appInstance->OK('Announcement assets retrieved successfully.', ['assets' => $assets]);
        } else {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/(.*)/assets/(.*)/delete', function ($id, $assetId) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (Announcements::exists((int) $id)) {
            if (AnnouncementsAssets::exists((int) $assetId)) {
                global $eventManager;
                $eventManager->emit(AnnouncementsEvent::onAnnouncementsRemoveAttachment(), [
                    'announcementId' => $id,
                    'assetId' => $assetId,
                ]);
                UserActivities::add(
                    $session->getInfo(UserColumns::UUID, false),
                    UserActivitiesTypes::$announcement_asset_delete,
                    CloudFlareRealIP::getRealIP(),
                    "Deleted announcement asset $assetId"
                );
                AnnouncementsAssets::delete((int) $assetId);
                $appInstance->OK('Announcement asset deleted successfully.', ['id' => $assetId]);
            } else {
                $appInstance->BadRequest('Announcement asset not found', ['error_code' => 'ANNOUNCEMENT_ASSET_NOT_FOUND']);
            }
        } else {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/announcements/(.*)/tags/(.*)/delete', function (int $id, int $tagId) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!Announcements::exists((int) $id)) {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }

        if (!AnnouncementsTags::exists((int) $tagId)) {
            $appInstance->BadRequest('Announcement tag not found', ['error_code' => 'ANNOUNCEMENT_TAG_NOT_FOUND']);
        }

        global $eventManager;
        $eventManager->emit(AnnouncementsEvent::onAnnouncementsRemoveTag(), [
            'announcementId' => $id,
            'tagId' => $tagId,
        ]);
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$announcement_tag_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted announcement tag $tagId"
        );

        AnnouncementsTags::delete((int) $tagId);

        $appInstance->OK('Announcement tag deleted successfully.', ['id' => $tagId]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/announcements/(.*)/tags', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (!Announcements::exists((int) $id)) {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }
        $tags = AnnouncementsTags::getAll((int) $id);
        $appInstance->OK('Announcement tags retrieved successfully.', ['tags' => $tags]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
$router->post('/api/admin/announcements/(.*)/delete', function ($id) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        if (Announcements::exists((int) $id)) {
            global $eventManager;
            $eventManager->emit(AnnouncementsEvent::onDeleteAnnouncement(), [
                'announcementId' => $id,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$announcement_delete,
                CloudFlareRealIP::getRealIP(),
                "Deleted announcement $id"
            );
            Announcements::delete((int) $id);
            $appInstance->OK('Announcement deleted successfully.', ['id' => $id]);
        } else {
            $appInstance->BadRequest('Announcement not found', ['error_code' => 'ANNOUNCEMENT_NOT_FOUND']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
