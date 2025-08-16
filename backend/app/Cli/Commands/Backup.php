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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;

class Backup extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();

        if (count($args) > 0) {
            $subcommand = $args[1] ?? null;

            if (!$subcommand) {
                self::getInstance()->send('&cPlease provide a subcommand!');

                return;
            }

            switch ($subcommand) {
                case 'take':
                    // Install an addon.
                    self::takeBackup($app);
                    break;
                case 'rm':
                    // Uninstall an addon.
                    self::removeBackup($args);
                    break;
                case 'ls':
                    self::listBackups();
                    break;
                case 'restore':
                    self::restoreBackup($args);
                    break;
                default:
                    self::getInstance()->send('&cInvalid subcommand!');
                    break;
            }
        } else {
            self::getInstance()->send('&cPlease provide a subcommand!');
        }
    }

    public static function takeBackup(App $app): void
    {
        $app->send('&aTaking backup...');
        \MythicalDash\Hooks\Backup::takeBackup();
        $app->send('&aBackup created successfully!');
    }

    public static function removeBackup(array $args): void
    {
        $app = App::getInstance();

        if (!isset($args[2])) {
            $app->send('&cPlease provide a backup ID to remove!');

            return;
        }

        $backupId = (int) $args[2];
        try {
            \MythicalDash\Hooks\Backup::deleteBackup($backupId);
            $app->send('&aBackup #' . $backupId . ' has been removed successfully!');
        } catch (\Exception $e) {
            $app->send('&cFailed to remove backup: ' . $e->getMessage());
        }
    }

    public static function listBackups(): void
    {
        $app = App::getInstance();

        try {
            $backups = \MythicalDash\Hooks\Backup::getBackups();

            if (empty($backups)) {
                $app->send('&7No backups found.');

                return;
            }

            $app->send('&7Available backups:');
            $app->send('&7----------------------------------------');

            foreach ($backups as $backup) {
                $app->send(sprintf(
                    '&e#%d &7- &a%s &7- &b%s &7- &d%s',
                    $backup['id'],
                    $backup['filename'],
                    $backup['size'],
                    $backup['created_at']
                ));
            }

            $app->send('&7----------------------------------------');
        } catch (\Exception $e) {
            $app->send('&cFailed to list backups: ' . $e->getMessage());
        }
    }

    public static function restoreBackup(array $args): void
    {
        $app = App::getInstance();

        if (!isset($args[2])) {
            $app->send('&cPlease provide a backup ID to restore!');

            return;
        }

        $backupId = (int) $args[2];

        // Confirm restoration
        $app->send('&cWARNING: This will overwrite your current database and .env file!');
        $app->send('&7Are you sure you want to restore backup #' . $backupId . '? [y/N]');
        $answer = strtolower(trim(readline('> ')));

        if ($answer !== 'y') {
            $app->send('&cRestore cancelled.');

            return;
        }

        try {
            $app->send('&7Restoring backup #' . $backupId . '...');
            \MythicalDash\Hooks\Backup::restoreBackup($backupId);
            $app->send('&aBackup restored successfully!');
            $app->send('&7You may need to restart the application for all changes to take effect.');
        } catch (\Exception $e) {
            $app->send('&cFailed to restore backup: ' . $e->getMessage());
        }
    }

    public static function getDescription(): string
    {
        return 'Mange the backups of the server';
    }

    public static function getSubCommands(): array
    {
        return [
            'take' => 'Take a backup of the server',
            'rm' => 'Remove a backup of the server',
            'ls' => 'List all backups of the server',
            'restore' => 'Restore a backup of the server',
        ];
    }
}
