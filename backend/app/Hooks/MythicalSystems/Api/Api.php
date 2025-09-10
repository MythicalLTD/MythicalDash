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

namespace MythicalDash\Hooks\MythicalSystems\Api;

class Api extends ResponseHandler
{
    /**
     * Init the api endpoint!
     */
    public static function init(): void
    {
        header('Content-type: application/json');
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
    }

    /**
     * Get the authorization key from header.
     *
     * @return string|null Return the content of the Authorization header if given!!
     */
    public static function getAuthorizationHeader(): ?string
    {
        $headers = getallheaders();
        if (isset($headers['Authorization']) && !$headers['Authorization'] == null) {
            return $headers['Authorization'];
        }

        return null;

    }

    /**
     * Allow only POST requests.
     */
    public static function allowOnlyGET(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ResponseHandler::MethodNotAllowed('Please use a GET request to access this endpoint!', null);
        }
    }

    /**
     * Allow only POST requests.
     */
    public static function allowOnlyPOST(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHandler::MethodNotAllowed('Please use a POST request to access this endpoint!', null);
        }
    }

    /**
     * Allow only PUT requests.
     */
    public static function allowOnlyPUT(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            ResponseHandler::MethodNotAllowed('Please use a PUT request to access this endpoint!', null);
        }
    }

    /**
     * Allow only PATCH requests.
     */
    public static function allowOnlyPATCH(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            ResponseHandler::MethodNotAllowed('Please use a PATCH request to access this endpoint!', null);
        }
    }

    /**
     * Allow only DELETE requests.
     */
    public static function allowOnlyDELETE(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            ResponseHandler::MethodNotAllowed('Please use a DELETE request to access this endpoint!', null);
        }
    }

    /**
     * Allow only OPTIONS requests.
     */
    public static function allowOnlyOPTIONS(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
            ResponseHandler::MethodNotAllowed('Please use a OPTIONS request to access this endpoint!', null);
        }
    }

    /**
     * Allow only HEAD requests.
     */
    public static function allowOnlyHEAD(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
            ResponseHandler::MethodNotAllowed('Please use a HEAD request to access this endpoint!', null);
        }
    }

    public static function allowOnly(Methods $method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            ResponseHandler::MethodNotAllowed('Please use a ' . $method . ' request to access this endpoint!', null);
        }
    }

    /**
     * Get the request method.
     */
    public static function getRequestMethod(): string|array|null
    {
        if ($_SERVER['REQUEST_METHOD'] == null) {
            return null;
        } elseif ($_SERVER['REQUEST_METHOD'] == '') {
            return null;
        }

        return $_SERVER['REQUEST_METHOD'];

    }
}
