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

namespace MythicalDash\Hooks;

use MySQLImport;
use MythicalDash\App;
use MythicalDash\Chat\Database;

class Backup
{
    private const PASSWORD_HASH = 'ZHNndm9fbXl0aGljYWxkYXNoXzIwMjUhIkA=';

    public static function takeBackup()
    {
        $appInstance = App::getInstance(true, false);
        $appInstance->loadEnv();
        $db = new Database(
            $_ENV['DATABASE_HOST'],
            $_ENV['DATABASE_DATABASE'],
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD']
        );

        $mysqli = $db->getMysqli();
        $dump = new \MySQLDump($mysqli);
        /**
         * Tables to exclude from the backup.
         */
        $dump->tables['mythicaldash_shareus'] = \MySQLDump::CREATE;
        $dump->tables['mythicaldash_linkvertise'] = \MySQLDump::CREATE;
        $dump->tables['mythicaldash_linkpays'] = \MySQLDump::CREATE;
        $dump->tables['mythicaldash_gyanilinks'] = \MySQLDump::CREATE;
        /**
         * Tables to include in the backup.
         */

        // Create a temporary directory for our backup files
        $backupDir = sys_get_temp_dir() . '/mythicaldash_backup_' . time();
        mkdir($backupDir);

        // Create backups directory if it doesn't exist
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        if (!file_exists($backupStorageDir)) {
            mkdir($backupStorageDir, 0755, true);
        }

        // Save database dump
        $dbDumpFile = $backupDir . '/export.sql.gz';
        $dump->save($dbDumpFile);

        // Copy .env file
        $envFile = __DIR__ . '/../../storage/.env';
        if (file_exists($envFile)) {
            copy($envFile, $backupDir . '/.env');
        }

        // Create zip archive with incremental ID
        $backupId = self::getNextBackupId();
        $zipFile = $backupStorageDir . '/backup_' . $backupId . '.mydb';
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // Set password for the ZIP file
            $zip->setPassword(base64_decode(self::PASSWORD_HASH));

            // Add database dump
            $zip->addFile($dbDumpFile, 'export.sql.gz');

            // Add .env file if it exists
            if (file_exists($backupDir . '/.env')) {
                $zip->addFile($backupDir . '/.env', '.env');
            }

            // Set encryption for all files
            $zip->setEncryptionName('export.sql.gz', \ZipArchive::EM_AES_256);
            if (file_exists($backupDir . '/.env')) {
                $zip->setEncryptionName('.env', \ZipArchive::EM_AES_256);
            }

            $zip->close();

            // Recursive cleanup function
            $cleanup = function ($dir) use (&$cleanup) {
                if (!is_dir($dir)) {
                    return;
                }

                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $cleanup($path);
                    } else {
                        unlink($path);
                    }
                }
                rmdir($dir);
            };

            // Clean up the temporary directory
            $cleanup($backupDir);
        }
    }

    public static function restoreBackup($id)
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        $backupPath = $backupStorageDir . '/backup_' . (int) $id . '.mydb';

        // Validate the backup file exists
        if (!file_exists($backupPath) || !is_file($backupPath)) {
            throw new \Exception('Backup file not found');
        }

        // Create temporary directory for extraction
        $tempDir = sys_get_temp_dir() . '/mythicaldash_restore_' . time();
        if (!mkdir($tempDir)) {
            throw new \Exception('Failed to create temporary directory');
        }

        try {
            // Extract the backup
            $zip = new \ZipArchive();
            if ($zip->open($backupPath) !== true) {
                throw new \Exception('Failed to open backup file');
            }
            // Set password for extraction
            $zip->setPassword(base64_decode(self::PASSWORD_HASH));
            $zip->extractTo($tempDir);
            $zip->close();

            // Get the app instance and load environment
            $appInstance = App::getInstance(true, false);
            $appInstance->loadEnv();

            // Restore database
            $dbDumpFile = $tempDir . '/export.sql.gz';
            if (file_exists($dbDumpFile)) {
                $db = new Database(
                    $_ENV['DATABASE_HOST'],
                    $_ENV['DATABASE_DATABASE'],
                    $_ENV['DATABASE_USER'],
                    $_ENV['DATABASE_PASSWORD']
                );
                try {
                    $pdo = $db->getPdo();
                    $pdo->query('SET foreign_key_checks = 0');
                    $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                    foreach ($tables as $table) {
                        $pdo->query("DROP TABLE `$table`");
                    }
                    $pdo->query('SET foreign_key_checks = 1');
                } catch (\Exception $e) {
                    throw new \Exception('Failed to rebuild the database: ' . $e->getMessage());
                }
                // Use MySQLImport to restore the database
                $import = new \MySQLImport($db->getMysqli());
                $import->load($dbDumpFile);
            }

            // Restore .env file if it exists
            $envFile = $tempDir . '/.env';
            if (file_exists($envFile)) {
                copy($envFile, __DIR__ . '/../../storage/.env');
            }

            // Clean up
            $cleanup = function ($dir) use (&$cleanup) {
                if (!is_dir($dir)) {
                    return;
                }

                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $cleanup($path);
                    } else {
                        unlink($path);
                    }
                }
                rmdir($dir);
            };

            $cleanup($tempDir);

            return true;
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($cleanup) && isset($tempDir)) {
                $cleanup($tempDir);
            }
            throw $e;
        }
    }

    public static function getBackups()
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        if (!file_exists($backupStorageDir)) {
            return [];
        }

        $backups = [];
        $files = glob($backupStorageDir . '/backup_*.mydb');

        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/backup_(\d+)\.mydb$/', $filename, $matches)) {
                $id = (int) $matches[1];
                $backups[] = [
                    'id' => $id,
                    'filename' => $filename,
                    'path' => $file,
                    'size' => self::formatSize(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
        }

        // Sort backups by ID, newest first
        usort($backups, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        return $backups;
    }

    public static function deleteBackup($id)
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        $backupPath = $backupStorageDir . '/backup_' . (int) $id . '.mydb';

        // Validate the backup file exists and is within the backup directory
        if (!file_exists($backupPath) || !is_file($backupPath)) {
            throw new \Exception('Backup file not found');
        }

        // Try to delete the file
        if (!unlink($backupPath)) {
            throw new \Exception('Failed to delete backup file');
        }

        return true;
    }

    private static function getNextBackupId()
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        if (!file_exists($backupStorageDir)) {
            return 1;
        }

        $files = glob($backupStorageDir . '/backup_*.mydb');
        if (empty($files)) {
            return 1;
        }

        $maxId = 0;
        foreach ($files as $file) {
            if (preg_match('/backup_(\d+)\.mydb$/', $file, $matches)) {
                $id = (int) $matches[1];
                $maxId = max($maxId, $id);
            }
        }

        return $maxId + 1;
    }

    private static function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
