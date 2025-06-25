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
use MythicalDash\Chat\User\Session;
use MythicalDash\Hooks\MythicalCloud;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Middleware\PermissionMiddleware;

// Get all backups
$router->get('/api/admin/cloud/backups', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MYTHICALCLOUD_VIEW, $session);
    try {
        $licenseKey = $appInstance->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');
        if (!$licenseKey) {
            throw new Exception('Mythical Cloud license key not configured');
        }

        $backups = MythicalCloud::getBackups($licenseKey);
        if ($backups === null) {
            throw new Exception('Failed to retrieve backups');
        }

        $appInstance->OK('Backups retrieved successfully', $backups);
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'CLOUD_ERROR']);
    }

});

// Download backup
$router->get('/api/admin/cloud/backup/(.*)/download', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MYTHICALCLOUD_DOWNLOAD, $session);
    try {
        $licenseKey = $appInstance->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');

        if (!$licenseKey) {
            throw new Exception('Mythical Cloud license key not configured');
        }

        $backupDir = __DIR__ . '/../../../../storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $savePath = $backupDir . '/backup_' . $backupId . '.mydb';
        $backupContent = MythicalCloud::downloadBackup($licenseKey, $backupId, $savePath);
        if ($backupContent === false) {
            throw new Exception('Failed to download backup');
        }

        $appInstance->OK('Backup downloaded successfully', []);
        exit;
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'CLOUD_ERROR']);
    }

});

// Delete specific backup
$router->post('/api/admin/cloud/backup/(.*)/delete', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROOT, $session);
    try {
        $licenseKey = $appInstance->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');

        if (!$licenseKey) {
            throw new Exception('Mythical Cloud license key not configured');
        }

        $result = MythicalCloud::deleteBackup($licenseKey, $backupId);
        if ($result === null) {
            throw new Exception('Failed to delete backup');
        }

        $appInstance->OK('Backup deleted successfully', $result);
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'CLOUD_ERROR']);
    }
});

// Get specific backup info
$router->get('/api/admin/cloud/backup/(.*)', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MYTHICALCLOUD_VIEW, $session);
    try {
        $licenseKey = $appInstance->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');

        if (!$licenseKey) {
            throw new Exception('Mythical Cloud license key not configured');
        }

        $backupInfo = MythicalCloud::getBackupInfo($licenseKey, $backupId);
        if ($backupInfo === null) {
            throw new Exception('Failed to retrieve backup info');
        }

        $appInstance->OK('Backup info retrieved successfully', $backupInfo);
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'CLOUD_ERROR']);
    }

});

// Delete all backups
$router->post('/api/admin/cloud/backups/wipe', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROOT, $session);
    try {
        $licenseKey = $appInstance->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL');

        if (!$licenseKey) {
            throw new Exception('Mythical Cloud license key not configured');
        }

        $result = MythicalCloud::deleteAllBackups($licenseKey);
        if ($result === null) {
            throw new Exception('Failed to delete all backups');
        }

        $appInstance->OK('All backups deleted successfully', $result);
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'CLOUD_ERROR']);
    }

});
