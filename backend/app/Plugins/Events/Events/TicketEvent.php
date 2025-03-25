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

class TicketEvent implements PluginEvent
{
    public static function onTicketCreate(): string
    {
        return 'ticket::create';
    }

    public static function onTicketUpdate(): string
    {
        return 'ticket::update';
    }

    public static function onTicketReply(): string
    {
        return 'ticket::reply';
    }

    public static function onTicketView(): string
    {
        return 'ticket::view';
    }

    public static function onTicketAttachmentUpload(): string
    {
        return 'ticket::attachment::upload';
    }
}
