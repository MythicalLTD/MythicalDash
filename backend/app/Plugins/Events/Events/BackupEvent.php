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

class BackupEvent implements PluginEvent
{
    public static function onCreateBackup(): string
    {
        return 'backup::onCreateBackup';
    }

    public static function onUpdateBackup(): string
    {
        return 'backup::onUpdateBackup';
    }

    public static function onDeleteBackup(): string
    {
        return 'backup::onDeleteBackup';
    }
}
