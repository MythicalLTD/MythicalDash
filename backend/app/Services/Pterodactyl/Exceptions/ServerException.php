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

namespace MythicalDash\Services\Pterodactyl\Exceptions;

class ServerException extends PterodactylException
{
    public static function serverOffline(): self
    {
        return new self('The server is currently offline.');
    }

    public static function transferInProgress(): self
    {
        return new self('A server transfer is currently in progress.');
    }

    public static function backupInProgress(): self
    {
        return new self('A backup operation is currently in progress.');
    }

    public static function powerActionInProgress(): self
    {
        return new self('A power action is currently in progress.');
    }

    public static function installationInProgress(): self
    {
        return new self('Server installation is currently in progress.');
    }

    public static function diskSpaceExceeded(): self
    {
        return new self('Server has exceeded its allocated disk space.');
    }

    public static function reinstallInProgress(): self
    {
        return new self('A server reinstall is currently in progress.');
    }
}
