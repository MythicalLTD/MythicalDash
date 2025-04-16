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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;

$router->get('/api/user/profile/(.*)', function ($uuid) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    new Session($appInstance);
    $config = $appInstance->getConfig();

    if ($config->getSetting(ConfigInterface::ALLOW_PUBLIC_PROFILES, 'false') === 'false') {
        $appInstance->BadRequest('Public profiles are not enabled!', ['error_code' => 'PUBLIC_PROFILES_NOT_ENABLED']);
    }

    if (!User::exists(UserColumns::UUID, $uuid)) {
        $appInstance->BadRequest('User not found!', ['error_code' => 'USER_NOT_FOUND']);
    }

    $user = User::getInfoArray(
        User::getTokenFromUUID($uuid),
        [
            UserColumns::USERNAME,
            UserColumns::VERIFIED,
            UserColumns::BANNED,
            UserColumns::AVATAR,
            UserColumns::CREDITS,
            UserColumns::UUID,
            UserColumns::ROLE_ID,
            UserColumns::DELETED,
            UserColumns::LAST_SEEN,
            UserColumns::FIRST_SEEN,
            UserColumns::BACKGROUND,
            UserColumns::MINUTES_AFK,
            UserColumns::LAST_SEEN_AFK,
            UserColumns::DISK_LIMIT,
            UserColumns::MEMORY_LIMIT,
            UserColumns::CPU_LIMIT,
            UserColumns::SERVER_LIMIT,
            UserColumns::BACKUP_LIMIT,
            UserColumns::DATABASE_LIMIT,
            UserColumns::ALLOCATION_LIMIT,

            UserColumns::DISCORD_LINKED,
            UserColumns::DISCORD_USERNAME,
            UserColumns::DISCORD_ID,

            UserColumns::GITHUB_LINKED,
            UserColumns::GITHUB_USERNAME,
            UserColumns::GITHUB_ID,
        ],
        [

        ]
    );
    if ($user['deleted'] === 'true') {
        $appInstance->BadRequest('User is deleted!', ['error_code' => 'USER_DELETED']);
    }

    $appInstance->OK('User Profile', [
        'user' => $user,
    ]);
});
