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

class AnnouncementsEvent implements PluginEvent
{
    public static function onCreateAnnouncement(): string
    {
        return 'announcements::onCreateAnnouncement';
    }

    public static function onUpdateAnnouncement(): string
    {
        return 'announcements::onUpdateAnnouncement';
    }

    public static function onDeleteAnnouncement(): string
    {
        return 'announcements::onDeleteAnnouncement';
    }

    public static function onAnnouncementsAddTag(): string
    {
        return 'announcements::onAnnouncementsAddTag';
    }

    public static function onAnnouncementsRemoveTag(): string
    {
        return 'announcements::onAnnouncementsRemoveTag';
    }

    public static function onAnnouncementsAddAttachment(): string
    {
        return 'announcements::onAnnouncementsAddAttachment';
    }

    public static function onAnnouncementsRemoveAttachment(): string
    {
        return 'announcements::onAnnouncementsRemoveAttachment';
    }
}
