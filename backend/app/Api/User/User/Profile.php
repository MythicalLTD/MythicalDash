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

    if ($config->getDBSetting(ConfigInterface::ALLOW_PUBLIC_PROFILES, 'false') === 'false') {
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
