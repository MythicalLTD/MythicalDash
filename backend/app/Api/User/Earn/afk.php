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

$router->post('/api/user/earn/afk/work', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();
    $s = new Session($appInstance);

    if ($config->getSetting(ConfigInterface::AFK_ENABLED, 'false') === 'false') {
        App::NotFound('AFK is not enabled', []);
    }

    $coins = $s->getInfo(UserColumns::CREDITS, false);
    $afkTime = $s->getInfo(UserColumns::MINUTES_AFK, false);
    $lastSeen = $s->getInfo(UserColumns::LAST_SEEN_AFK, false);

    $minToEarn = $config->getSetting(ConfigInterface::AFK_MIN_PER_COIN, 1);

    $dateTime = new DateTime();
    $currentTimestamp = $dateTime->getTimestamp();

    try {
        if ($lastSeen <= $currentTimestamp) {
            // Calculate time difference in minutes
            $timeDiff = floor(($currentTimestamp - $lastSeen) / 60);

            if ($timeDiff >= $minToEarn) {
                // Calculate how many coins to award
                $coinsToAward = floor($timeDiff / $minToEarn);

                // Update user's coins and AFK time
                $s->setInfo(UserColumns::CREDITS, $coins + $coinsToAward, false);
                $s->setInfo(UserColumns::MINUTES_AFK, $afkTime + $minToEarn, false);
                $s->setInfo(UserColumns::LAST_SEEN_AFK, $currentTimestamp, false);

                App::OK('Coins and AFK time added successfully', [
                    'coins_awarded' => $coinsToAward,
                    'time_spent' => $timeDiff,
                    'total_coins' => $coins + $coinsToAward,
                    'total_afk_time' => $afkTime + $timeDiff,
                ]);
            } else {
                App::OK('Not enough time spent AFK to earn coins', [
                    'time_spent' => $timeDiff,
                    'minutes_needed' => $minToEarn,
                ]);
            }
        } else {
            App::BadRequest('Invalid last seen timestamp', []);
        }
    } catch (Exception $e) {
        App::BadRequest('Failed to add coins and AFK time', []);
    }
});
