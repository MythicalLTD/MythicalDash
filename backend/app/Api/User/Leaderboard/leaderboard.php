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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\User\Leaderboard;
use MythicalDash\Chat\interface\LeaderboardTypes;

$router->add('/api/user/leaderboard/(.*)', function ($type) {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    new Session($appInstance);

    $config = $appInstance->getConfig();
    if (!$config->getDBSetting(MythicalDash\Config\ConfigInterface::LEADERBOARD_ENABLED, 'false') == 'false') {
        $appInstance->BadRequest('Leaderboard is disabled', ['error_code' => 'LEADERBOARD_DISABLED']);
    }

    $limit = $config->getDBSetting(MythicalDash\Config\ConfigInterface::LEADERBOARD_LIMIT, 15);
    if (!in_array($type, LeaderboardTypes::getLeaderboardTypes())) {
        $appInstance->BadRequest('Invalid type', ['error_code' => 'INVALID_TYPE']);
    }

    $leaderboard = Leaderboard::getLeaderboard($limit, $type);

    $appInstance->OK('Here you go, cuz i heard you want some leaderboard!', ['leaderboard' => $leaderboard]);
});
