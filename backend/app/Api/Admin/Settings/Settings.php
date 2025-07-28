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
