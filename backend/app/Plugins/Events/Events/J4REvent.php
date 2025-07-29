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

namespace MythicalDash\Plugins\Events\Events;

use MythicalDash\Plugins\Events\PluginEvent;

class J4REvent implements PluginEvent
{
    // Admin Events
    public static function onJ4RServerCreated(): string
    {
        return 'j4r_server::onJ4RServerCreated';
    }

    public static function onJ4RServerUpdated(): string
    {
        return 'j4r_server::onJ4RServerUpdated';
    }

    public static function onJ4RServerDeleted(): string
    {
        return 'j4r_server::onJ4RServerDeleted';
    }

    public static function onJ4RServerLocked(): string
    {
        return 'j4r_server::onJ4RServerLocked';
    }

    public static function onJ4RServerUnlocked(): string
    {
        return 'j4r_server::onJ4RServerUnlocked';
    }

    // User Events
    public static function onJ4RCheckInitiated(): string
    {
        return 'j4r_user::onJ4RCheckInitiated';
    }

    public static function onJ4RServerJoined(): string
    {
        return 'j4r_user::onJ4RServerJoined';
    }

    public static function onJ4RRewardsClaimed(): string
    {
        return 'j4r_user::onJ4RRewardsClaimed';
    }
}
