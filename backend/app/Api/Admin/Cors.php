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

use MythicalDash\App;
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\User\Session;

$router->add('/api/admin/cors', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(MythicalDash\Chat\columns\UserColumns::ROLE_ID, false))) {
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

    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
