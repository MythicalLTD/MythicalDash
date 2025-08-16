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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Chat\columns;

class ImageReportColumns
{
    public const ID = 'id';
    public const IMAGE_ID = 'image_id';
    public const IMAGE_URL = 'image_url';
    public const REASON = 'reason';
    public const DETAILS = 'details';
    public const REPORTER_IP = 'reporter_ip';
    public const USER_AGENT = 'user_agent';
    public const REPORTED_AT = 'reported_at';
    public const STATUS = 'status';
    public const ADMIN_NOTES = 'admin_notes';
    public const RESOLVED_AT = 'resolved_at';
    public const RESOLVED_BY = 'resolved_by';
    public const DELETED = 'deleted';

    /**
     * @return string[]
     */
    public static function getColumns(): array
    {
        return [
            self::ID,
            self::IMAGE_ID,
            self::IMAGE_URL,
            self::REASON,
            self::DETAILS,
            self::REPORTER_IP,
            self::USER_AGENT,
            self::REPORTED_AT,
            self::STATUS,
            self::ADMIN_NOTES,
            self::RESOLVED_AT,
            self::RESOLVED_BY,
            self::DELETED,
        ];
    }
}
