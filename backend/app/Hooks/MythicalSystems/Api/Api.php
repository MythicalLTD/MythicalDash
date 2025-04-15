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
