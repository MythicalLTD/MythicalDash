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
use MythicalDash\Chat\User\Session;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Plugins\Events\Events\AfkEvent;

$router->post('/api/user/earn/afk/work', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $s = new Session($appInstance);
    $uuid = $s->getInfo(UserColumns::UUID, false);
    if ($config->getDBSetting(ConfigInterface::AFK_ENABLED, 'false') === 'false') {
        App::NotFound('AFK is not enabled', []);
    }
    global $eventManager;

    $coins = $s->getInfo(UserColumns::CREDITS, false);
    $afkTime = $s->getInfo(UserColumns::MINUTES_AFK, false);

    $coinsToAward = (int) $config->getDBSetting(ConfigInterface::AFK_MIN_PER_COIN, 1);

    // Always increment AFK time by 1 minute per request
    $newAfkTime = $afkTime + 1;

    // Update user stats
    $s->setInfo(UserColumns::MINUTES_AFK, $newAfkTime, false);
    
    // Add credits atomically to prevent race conditions
    $newTotalCoins = $coins;
    if ($coinsToAward > 0) {
        if ($s->addCreditsAtomic($coinsToAward)) {
            $newTotalCoins = $coins + $coinsToAward;
        } else {
            // If adding credits failed, log the error but don't fail the entire request
            $appInstance->getLogger()->error('Failed to add AFK credits atomically for user: ' . $uuid);
            $newTotalCoins = $coins; // Keep original amount
        }
    }

    $eventManager->emit(
        $coinsToAward > 0 ? AfkEvent::onAfk() : AfkEvent::onAfkEarly(),
        [
            'user' => $uuid,
            'coins_awarded' => $coinsToAward,
            'time_spent' => 1,
            'total_coins' => $newTotalCoins,
            'total_afk_time' => $newAfkTime,
        ]
    );

    App::OK('AFK stats updated', [
        'coins_awarded' => $coinsToAward,
        'total_coins' => $newTotalCoins,
        'total_afk_time' => $newAfkTime,
    ]);
});
