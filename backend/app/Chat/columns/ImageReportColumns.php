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
