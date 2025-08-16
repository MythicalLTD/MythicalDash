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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Hooks\Backup;
use MythicalDash\Hooks\MythicalCloud;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Plugins\Events\Events\BackupEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/backups/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_BACKUPS_LIST, $session);
    $backups = Backup::getBackups();
    $appInstance->OK('Backups fetched successfully', ['backups' => $backups]);
});

$router->get('/api/admin/backup/(.*)/restore', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_ROOT, $session);
    UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_restore, CloudFlareRealIP::getRealIP(), 'Restored backup ' . $backupId);
    $backup = Backup::restoreBackup($backupId);

    if ($backup) {
        $appInstance->OK('Backup restored successfully', ['backup' => $backup]);
    } else {
        $appInstance->InternalServerError('Failed to restore backup', ['error_code' => 'BACKUP_RESTORE_FAILED']);
    }
});

$router->post('/api/admin/backup/(.*)/upload-to-cloud', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MYTHICALCLOUD_UPLOAD, $session);
    try {

        try {
            if (Backup::exists($backupId)) {
                $bkPath = __DIR__ . '/../../../../storage/backups/backup_' . $backupId . '.mydb';
            } else {
                throw new Exception('Backup not found');
            }
        } catch (Exception $e) {
            $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'BACKUP_NOT_FOUND']);
        }
        // Get license key for MythicalCloud
        $licenseKey = $appInstance->getConfig()->getDBSetting(ConfigInterface::LICENSE_KEY, 'NULL');
        if (!$licenseKey) {
            throw new Exception('MythicalCloud license key not configured');
        }

        try {
            // Upload the backup to MythicalCloud
            if (file_exists($bkPath)) {
                $result = MythicalCloud::uploadBackup($licenseKey, $bkPath, 'Windows', '10.0', 'x64');
                if ($result === null || !$result) {
                    $appInstance->InternalServerError('Failed to upload backup to MythicalCloud', ['error_code' => 'UPLOAD_ERROR']);
                }
            } else {
                $appInstance->InternalServerError('Backup not found', ['error_code' => 'BACKUP_NOT_FOUND', 'path' => $bkPath]);
            }

        } catch (Exception $e) {
            $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'UPLOAD_ERROR']);
        }
        $appInstance->OK('Backup uploaded to MythicalCloud successfully', $result);
    } catch (Exception $e) {
        $appInstance->InternalServerError($e->getMessage(), ['error_code' => 'UPLOAD_ERROR']);
    }
});

$router->get('/api/admin/backup/(.*)/delete', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager, $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_BACKUPS_DELETE, $session);
    UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_delete, CloudFlareRealIP::getRealIP(), 'Deleted backup ' . $backupId);
    $backup = Backup::deleteBackup($backupId);
    $eventManager->emit(BackupEvent::onDeleteBackup(), ['backupId' => $backupId]);
    if ($backup) {
        $appInstance->OK('Backup deleted successfully', ['backup' => $backup]);
    } else {
        $appInstance->InternalServerError('Failed to delete backup', ['error_code' => 'BACKUP_DELETE_FAILED']);
    }
});

$router->get('/api/admin/backup/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager, $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_BACKUPS_CREATE, $session);
    UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_create, CloudFlareRealIP::getRealIP(), 'Created backup');
    $eventManager->emit(BackupEvent::onCreateBackup(), []);
    $backup = Backup::takeBackup();
    $appInstance->OK('Backup created successfully', ['backup' => $backup]);
});
