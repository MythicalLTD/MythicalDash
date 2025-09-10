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
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\SettingsEvent;

$router->post('/api/admin/settings/update', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $config = $appInstance->getConfig();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SETTINGS_EDIT, $session);
    if (isset($_POST['key']) && isset($_POST['value'])) {
        $key = $_POST['key'];
        $value = $_POST['value'];
        if ($value == '' || $value == null || $key == '' || $key == null) {
            $appInstance->BadRequest('Invalid request', ['error_code' => 'INVALID_REQUEST']);
        }

        $setResult = $config->setSetting($key, $value);
        if ($setResult) {
            global $eventManager;
            $eventManager->emit(SettingsEvent::onSettingsUpdated(), [
                'key' => $key,
                'value' => $value,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$admin_settings_update,
                CloudFlareRealIP::getRealIP(),
                "Updated setting $key"
            );
            $appInstance->OK('Settings updated successfully.', []);
        } else {
            $appInstance->InternalServerError('Failed to update settings', ['error_code' => 'SERVICE_UNAVAILABLE']);
        }
    } else {
        $appInstance->BadRequest('Invalid request', ['error_code' => 'INVALID_REQUEST']);
    }

});

$router->post('/api/admin/settings/update/bulk', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $config = $appInstance->getConfig();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SETTINGS_EDIT, $session);

    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);

    if (!$data || !is_array($data)) {
        $appInstance->BadRequest('Invalid JSON body', ['error_code' => 'INVALID_JSON']);

        return;
    }

    $updatedSettings = [];
    $failedSettings = [];
    $successCount = 0;
    $errorCount = 0;

    foreach ($data as $key => $value) {
        if (empty($key) || $key === null) {
            $failedSettings[] = [
                'key' => $key,
                'value' => $value,
                'error' => 'Invalid key',
            ];
            ++$errorCount;
            continue;
        }

        if ($value === '' || $value === null) {
            $failedSettings[] = [
                'key' => $key,
                'value' => $value,
                'error' => 'Invalid value',
            ];
            ++$errorCount;
            continue;
        }

        try {
            $setResult = $config->setSetting($key, $value);
            if ($setResult) {
                $updatedSettings[] = [
                    'key' => $key,
                    'value' => $value,
                ];
                ++$successCount;

                // Log individual setting update
                global $eventManager;
                $eventManager->emit(SettingsEvent::onSettingsUpdated(), [
                    'key' => $key,
                    'value' => $value,
                ]);
                UserActivities::add(
                    $session->getInfo(UserColumns::UUID, false),
                    UserActivitiesTypes::$admin_settings_update,
                    CloudFlareRealIP::getRealIP(),
                    "Updated setting $key"
                );
            } else {
                $failedSettings[] = [
                    'key' => $key,
                    'value' => $value,
                    'error' => 'Failed to update setting',
                ];
                ++$errorCount;
            }
        } catch (Exception $e) {
            $failedSettings[] = [
                'key' => $key,
                'value' => $value,
                'error' => 'Exception: ' . $e->getMessage(),
            ];
            ++$errorCount;
        }
    }

    // Prepare response
    $response = [
        'success_count' => $successCount,
        'error_count' => $errorCount,
        'total_count' => count($data),
        'updated_settings' => $updatedSettings,
        'failed_settings' => $failedSettings,
    ];

    if ($errorCount === 0) {
        $appInstance->OK('All settings updated successfully.', $response);
    } elseif ($successCount === 0) {
        $appInstance->BadRequest('Failed to update any settings', $response);
    } else {
        $appInstance->OK('Settings updated with some errors.', $response);
    }
});

$router->get('/api/admin/settings/get', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_SETTINGS_VIEW, $session);
    $config = $appInstance->getConfig();
    $appInstance->OK('Settings retrieved successfully.', [
        'settings' => $config->dumpSettings(),
    ]);

});
