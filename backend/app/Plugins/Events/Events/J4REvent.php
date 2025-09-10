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
