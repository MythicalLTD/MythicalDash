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
