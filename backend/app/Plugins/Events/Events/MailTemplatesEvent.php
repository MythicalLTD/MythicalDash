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

class MailTemplatesEvent implements PluginEvent
{
    public static function onCreateMailTemplate(): string
    {
        return 'mailTemplates::onCreateMailTemplate';
    }

    public static function onUpdateMailTemplate(): string
    {
        return 'mailTemplates::onUpdateMailTemplate';
    }

    public static function onDeleteMailTemplate(): string
    {
        return 'mailTemplates::onDeleteMailTemplate';
    }
}
