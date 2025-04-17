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

class UserEvent implements PluginEvent
{
    public static function onUserUpdate(): string
    {
        return 'user:update';
    }

    public static function onUserDelete(): string
    {
        return 'user:delete';
    }

    public static function newSupportPin(): string
    {
        return 'user:new_support_pin';
    }

    public static function resetApiKey(): string
    {
        return 'user:reset_api_key';
    }
}
