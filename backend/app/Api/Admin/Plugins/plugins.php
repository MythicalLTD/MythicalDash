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
use MythicalDash\Plugins\PluginConfig;
use MythicalDash\Plugins\PluginSettings;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\PluginsSettingsEvent;

$router->get('/api/admin/plugins/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_PLUGINS_LIST, $session);
    $plugins = $pluginManager->getLoadedMemoryPlugins();
    $pluginsList = [];
    foreach ($plugins as $plugin) {
        $info = PluginConfig::getConfig($plugin);
        $pluginsList[$plugin] = $info;
    }
    $appInstance->OK('Plugins fetched successfully', ['plugins' => $pluginsList]);
});

$router->get('/api/admin/plugins/(.*)/config', function ($identifier): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $plugins = $pluginManager->getLoadedMemoryPlugins();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_PLUGINS_LIST, $session);

    if (in_array($identifier, $plugins)) {
        $info = PluginConfig::getConfig($identifier);
        $settings = PluginSettings::getSettings($identifier);
        $settingsList = [];
        foreach ($settings as $setting) {
            $settingsList[$setting['key']] = $setting['value'];
        }
        $appInstance->OK('Plugin config fetched successfully', ['config' => $info, 'plugin' => $info, 'settings' => $settingsList]);
    } else {
        $appInstance->NotFound('Plugin not found', ['error_code' => 'PLUGIN_NOT_FOUND', 'identifier' => $identifier, 'plugins' => $plugins]);
    }
});

$router->post('/api/admin/plugins/(.*)/settings/set', function ($identifier): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();

    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_PLUGINS_EDIT, $session);

    $plugins = $pluginManager->getLoadedMemoryPlugins();
    if (!in_array($identifier, $plugins)) {
        $appInstance->NotFound('Plugin not found', ['error_code' => 'PLUGIN_NOT_FOUND']);

        return;
    }

    if (isset($_POST['key']) && !empty($_POST['key'])) {
        $key = $_POST['key'];
    } else {
        $appInstance->BadRequest('Missing key parameter', ['error_code' => 'MISSING_KEY']);

        return;
    }

    if (isset($_POST['value']) && !empty($_POST['value'])) {
        $value = $_POST['value'];
    } else {
        $appInstance->BadRequest('Missing value parameter', ['error_code' => 'MISSING_VALUE']);

        return;
    }

    try {
        PluginSettings::setSettings($identifier, $key, ['value' => $value]);

        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$plugin_setting_update,
            CloudFlareRealIP::getRealIP(),
            "Updated setting $key for plugin $identifier"
        );
        global $eventManager;
        $eventManager->emit(PluginsSettingsEvent::onPluginSettingUpdate(), [
            'identifier' => $identifier,
            'key' => $key,
            'value' => $value,
        ]);
        $appInstance->OK('Setting updated successfully', [
            'identifier' => $identifier,
            'key' => $key,
            'value' => $value,
        ]);
    } catch (Exception $e) {
        $appInstance->InternalServerError('Failed to update setting', [
            'error_code' => 'SETTING_UPDATE_FAILED',
            'error' => $e->getMessage(),
        ]);
    }
});

$router->post('/api/admin/plugins/(.*)/settings/remove', function ($identifier): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();

    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_PLUGINS_EDIT, $session);

    $plugins = $pluginManager->getLoadedMemoryPlugins();
    if (!in_array($identifier, $plugins)) {
        $appInstance->NotFound('Plugin not found', ['error_code' => 'PLUGIN_NOT_FOUND']);

        return;
    }

    if (isset($_POST['key']) && !empty($_POST['key'])) {
        $key = $_POST['key'];
    } else {
        $appInstance->BadRequest('Missing key parameter', ['error_code' => 'MISSING_KEY']);

        return;
    }

    try {
        UserActivities::add(
            $session->getInfo(UserColumns::UUID, false),
            UserActivitiesTypes::$plugin_setting_delete,
            CloudFlareRealIP::getRealIP(),
            "Removed setting $key from plugin $identifier"
        );
        global $eventManager;
        $eventManager->emit(PluginsSettingsEvent::onPluginSettingDelete(), [
            'identifier' => $identifier,
            'key' => $key,
        ]);
        PluginSettings::deleteSettings($identifier, $key);
        $appInstance->OK('Setting removed successfully', [
            'identifier' => $identifier,
            'key' => $key,
        ]);
    } catch (Exception $e) {
        $appInstance->InternalServerError('Failed to remove setting', [
            'error_code' => 'SETTING_REMOVE_FAILED',
            'error' => $e->getMessage(),
        ]);
    }
});
