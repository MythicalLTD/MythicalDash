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

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Chat\User\Session;
use MythicalDash\Middleware\PermissionMiddleware;

$router->add('/api/admin/cors', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROOT, $session);
    if (isset($_GET['target']) && !empty($_GET['target'])) {
        $target = $_GET['target'];

        // Validate URL
        if (!filter_var($target, FILTER_VALIDATE_URL)) {
            $appInstance->BadRequest('Invalid target URL', ['error_code' => 'INVALID_URL']);

            return;
        }

        // Set CORS headers to allow the original request
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // Initialize cURL to fetch the target URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Add appropriate user agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'MythicalDash-Proxy/1.0');

        // Execute the request
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);

        // Check for errors
        if ($error) {
            curl_close($ch);
            $appInstance->InternalServerError('Failed to fetch target URL: ' . $error, [
                'error_code' => 'FETCH_FAILED',
                'target' => $target,
            ]);

            return;
        }

        // Get content type
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        // Set appropriate content type
        if ($contentType) {
            header('Content-Type: ' . $contentType);
        }

        // Output the response directly
        echo $response;
        exit; // Exit to prevent any other output

    }
    $appInstance->BadRequest('Valid target URL is required', ['error_code' => 'TARGET_REQUIRED']);

});
