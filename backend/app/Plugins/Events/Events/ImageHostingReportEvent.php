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

class ImageHostingReportEvent implements PluginEvent
{
    public static function onImageHostingReport(): string
    {
        return 'image_hosting_report::onImageHostingReport';
    }

    public static function onImageReportUpdated(): string
    {
        return 'image_hosting_report::onImageReportUpdated';
    }

    public static function onImageReportDeleted(): string
    {
        return 'image_hosting_report::onImageReportDeleted';
    }

    public static function onImageReportResolved(): string
    {
        return 'image_hosting_report::onImageReportResolved';
    }

    public static function onImageReportDismissed(): string
    {
        return 'image_hosting_report::onImageReportDismissed';
    }
}
