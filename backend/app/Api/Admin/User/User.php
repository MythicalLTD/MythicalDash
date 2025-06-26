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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Mails;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Hooks\Pterodactyl\Admin\Servers;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\UserEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;

$router->get('/api/admin/users', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_LIST, $session);
    $user = User::getListWithFilters(['id', 'username', 'first_name', 'last_name', 'email', 'avatar', 'pterodactyl_user_id', 'role', 'last_seen', 'uuid'], ['first_name', 'last_name']);

    $appInstance->OK('Users data retrieved successfully.', [
        'users' => $user,
    ]);

});

$router->get('/api/admin/user/(.*)/info', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_LIST, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId)) {
        $targetUser = User::getTokenFromUUID($userId);
        $userInfo = User::getInfoArray(
            $targetUser,
            [
                UserColumns::USERNAME,
                UserColumns::PASSWORD,
                UserColumns::EMAIL,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::AVATAR,
                UserColumns::CREDITS,
                UserColumns::UUID,
                UserColumns::PTERODACTYL_USER_ID,
                UserColumns::ACCOUNT_TOKEN,
                UserColumns::ROLE_ID,
                UserColumns::FIRST_IP,
                UserColumns::LAST_IP,
                UserColumns::BANNED,
                UserColumns::SUPPORT_PIN,
                UserColumns::VERIFIED,
                UserColumns::TWO_FA_ENABLED,
                UserColumns::TWO_FA_KEY,
                UserColumns::TWO_FA_BLOCKED,
                UserColumns::DELETED,
                UserColumns::LAST_SEEN,
                UserColumns::FIRST_SEEN,
                UserColumns::BACKGROUND,
                UserColumns::DISK_LIMIT,
                UserColumns::MEMORY_LIMIT,
                UserColumns::CPU_LIMIT,
                UserColumns::SERVER_LIMIT,
                UserColumns::BACKUP_LIMIT,
                UserColumns::DATABASE_LIMIT,
                UserColumns::ALLOCATION_LIMIT,
                UserColumns::MINUTES_AFK,
                UserColumns::LAST_SEEN_AFK,
            ],
            [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::PASSWORD,
            ]
        );

        $activity = UserActivities::get($userInfo[UserColumns::UUID], 1000);
        $mails = Mails::getAll($userInfo[UserColumns::UUID]);

        $appInstance->OK('User info retrieved successfully.', [
            'user' => $userInfo,
            'activity' => $activity,
            'mails' => $mails,
        ]);
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->post('/api/admin/user/(.*)/update', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_EDIT, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId)) {
        if (isset($_POST['column']) && isset($_POST['value']) && isset($_POST['encrypted'])) {
            $column = $_POST['column'];
            $value = $_POST['value'];
            $encrypted = $_POST['encrypted'];
            if ($encrypted === 'true') {
                $encrypted = true;
            } else {
                $encrypted = false;
            }
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_user_update,
                CloudFlareRealIP::getRealIP(),
                "Updated user $userId"
            );

            global $eventManager;
            $eventManager->emit(UserEvent::onUserUpdate(), [
                'user' => $userId,
            ]);
            $token = User::getTokenFromUUID($userId);
            if (User::updateInfo($token, $column, $value, $encrypted)) {
                $appInstance->OK('User updated successfully.', ['error_code' => 'USER_UPDATED']);
            } else {
                $appInstance->InternalServerError('Failed to update user', ['error_code' => 'USER_UPDATE_FAILED']);
            }
        } else {
            $appInstance->BadRequest('Column, value and encrypted are required', ['error_code' => 'COLUMN_VALUE_ENCRYPTED_REQUIRED']);
        }
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->post('/api/admin/user/(.*)/delete', function ($userId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_USERS_DELETE, $session);
    if (empty($userId)) {
        $appInstance->BadRequest('User ID is required', ['error_code' => 'USER_ID_REQUIRED']);
    }
    if (User::exists(UserColumns::UUID, $userId)) {
        global $eventManager;
        $eventManager->emit(UserEvent::onUserDelete(), [
            'user' => $userId,
        ]);
        $token = User::getTokenFromUUID($userId);
        User::delete($token);
        foreach (Servers::getUserServersList(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false)) as $server) {
            Servers::deletePterodactylServer($server['id']);
        }
        $pteroUsers = new UsersResource(
            $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
            $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
        );
        $pteroUsers->deleteUser(User::getInfo($token, UserColumns::PTERODACTYL_USER_ID, false));

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$admin_user_delete,
            CloudFlareRealIP::getRealIP(),
            "Deleted user $userId"
        );

        $appInstance->OK('User deleted successfully.', ['error_code' => 'USER_DELETED']);
    } else {
        $appInstance->NotFound('User not found', ['error_code' => 'USER_NOT_FOUND']);
    }

});

$router->get('/api/admin/user/support-pin/(.*)', function ($supportPin): void {
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ANNOUNCEMENTS_LIST, $session);
    if (empty($supportPin)) {
        $appInstance->BadRequest('Support pin is required', ['error_code' => 'SUPPORT_PIN_REQUIRED']);
    }
    if (User::checkSupportPin($supportPin)) {
        $uuid = User::convertPinToUUID($supportPin);
        $appInstance->OK('Support pin entered successfully.', ['error_code' => 'SUPPORT_PIN_ENTERED', 'uuid' => $uuid]);
    } else {
        $appInstance->BadRequest('Support pin is incorrect', ['error_code' => 'SUPPORT_PIN_INCORRECT']);
    }

});
