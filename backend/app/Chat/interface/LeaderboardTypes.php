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

namespace MythicalDash\Chat\interface;

class LeaderboardTypes
{
    public static string $COINS = 'coins';
    public static string $SERVERS = 'servers';
    public static string $MINUTES_AFK = 'minutes_afk';
    public static string $LINKVERTISE = 'linkvertise';
    public static string $SHAREUS = 'shareus';
    public static string $GYANILINKS = 'gyanilinks';
    public static string $LINKPAYS = 'linkpays';
    public static string $REFERRALS = 'referrals';

    public static function getLeaderboardTypes(): array
    {
        return [
            self::$COINS,
            self::$SERVERS,
            self::$MINUTES_AFK,
            self::$LINKVERTISE,
            self::$SHAREUS,
            self::$GYANILINKS,
            self::$LINKPAYS,
            self::$REFERRALS,
        ];
    }
}
