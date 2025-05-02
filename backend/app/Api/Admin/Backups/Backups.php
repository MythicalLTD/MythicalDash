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
use MythicalDash\Hooks\Backup;
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\BackupEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/admin/backups/list', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        $backups = Backup::getBackups();
        $appInstance->OK('Backups fetched successfully', ['backups' => $backups]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/backup/(.*)/restore', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_restore, CloudFlareRealIP::getRealIP(), 'Restored backup ' . $backupId);
        $backup = Backup::restoreBackup($backupId);

        if ($backup) {
            $appInstance->OK('Backup restored successfully', ['backup' => $backup]);
        } else {
            $appInstance->InternalServerError('Failed to restore backup', ['error_code' => 'BACKUP_RESTORE_FAILED']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/backup/(.*)/delete', function (string $backupId): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager, $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_delete, CloudFlareRealIP::getRealIP(), 'Deleted backup ' . $backupId);
        $backup = Backup::deleteBackup($backupId);
        $eventManager->emit(BackupEvent::onDeleteBackup(), ['backupId' => $backupId]);
        if ($backup) {
            $appInstance->OK('Backup deleted successfully', ['backup' => $backup]);
        } else {
            $appInstance->InternalServerError('Failed to delete backup', ['error_code' => 'BACKUP_DELETE_FAILED']);
        }
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});

$router->get('/api/admin/backup/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    global $pluginManager, $eventManager;
    $session = new MythicalDash\Chat\User\Session($appInstance);
    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
        UserActivities::add($session->getInfo(UserColumns::UUID, false), UserActivitiesTypes::$admin_backup_create, CloudFlareRealIP::getRealIP(), 'Created backup');
        $eventManager->emit(BackupEvent::onCreateBackup(), []);
        $backup = Backup::takeBackup();
        $appInstance->OK('Backup created successfully', ['backup' => $backup]);
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
