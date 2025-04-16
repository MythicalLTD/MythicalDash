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
use MythicalDash\Plugins\PluginConfig;
use MythicalDash\Plugins\PluginSettings;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\PluginsSettingsEvent;

$router->get('/api/admin/plugins/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $plugins = $pluginManager->getLoadedMemoryPlugins();
        $pluginsList = [];
        foreach ($plugins as $plugin) {
            $info = PluginConfig::getConfig($plugin);
            $pluginsList[$plugin] = $info;
        }
        $appInstance->OK('Plugins fetched successfully', ['plugins' => $pluginsList]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/plugins/(.*)/config', function ($identifier): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $plugins = $pluginManager->getLoadedMemoryPlugins();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {

        if (in_array($identifier, $plugins)) {
            $info = PluginConfig::getConfig($identifier);
            $settings = PluginSettings::getSettings($identifier);
            $settingsList = [];
            foreach ($settings as $setting) {
                $settingsList[$setting['key']] = $appInstance->decrypt($setting['value']);
            }
            $appInstance->OK('Plugin config fetched successfully', ['config' => $info, 'plugin' => $info, 'settings' => $settingsList]);
        } else {
            $appInstance->NotFound('Plugin not found', ['error_code' => 'PLUGIN_NOT_FOUND', 'identifier' => $identifier, 'plugins' => $plugins]);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->post('/api/admin/plugins/(.*)/settings/set', function ($identifier): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();

    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (!Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);

        return;
    }

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
        $valueNonEncrypted = $value;
        $value = $appInstance->encrypt($value);
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
            'value' => $valueNonEncrypted,
        ]);
        $appInstance->OK('Setting updated successfully', [
            'identifier' => $identifier,
            'key' => $key,
            'value' => $valueNonEncrypted,
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

    if (!Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);

        return;
    }

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
