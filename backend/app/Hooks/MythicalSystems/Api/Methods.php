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

namespace MythicalDash\Hooks\MythicalSystems\Api;

interface Methods
{
    public const get = 'GET';
    public const post = 'POST';
    public const put = 'PUT';
    public const delete = 'DELETE';
    public const head = 'HEAD';
    public const options = 'OPTIONS';
    public const patch = 'PATCH';
    public const trace = 'TRACE';
    public const connect = 'CONNECT';
}
