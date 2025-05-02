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

use MySQLDump;
use MythicalDash\App;
use MythicalDash\Chat\Database;
use MySQLImport;

class Backup
{
    private static function getNextBackupId()
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        if (!file_exists($backupStorageDir)) {
            return 1;
        }

        $files = glob($backupStorageDir . '/backup_*.zip');
        if (empty($files)) {
            return 1;
        }

        $maxId = 0;
        foreach ($files as $file) {
            if (preg_match('/backup_(\d+)\.zip$/', $file, $matches)) {
                $id = (int)$matches[1];
                $maxId = max($maxId, $id);
            }
        }

        return $maxId + 1;
    }

    public static function takeBackup()
    {
		$appInstance = App::getInstance(true,false);
		$appInstance->loadEnv();
        $db = new Database(
            $_ENV['DATABASE_HOST'],
            $_ENV['DATABASE_DATABASE'],
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD']
        );

		$mysqli = $db->getMysqli();
		$dump = new MySQLDump($mysqli);
		/**
		 * Tables to exclude from the backup
		 */
		$dump->tables['mythicaldash_users_email_verification'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_users_activities'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_shareus'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_servers_queue_logs'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_linkvertise'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_linkpays'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_gyanilinks'] = MySQLDump::CREATE;
		$dump->tables['mythicaldash_users_mails'] = MySQLDump::CREATE;
		/**
		 * Tables to include in the backup
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
		$zipFile = $backupStorageDir . '/backup_' . $backupId . '.zip';
		$zip = new \ZipArchive();
		if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
			// Add database dump
			$zip->addFile($dbDumpFile, 'export.sql.gz');
			
			// Add .env file if it exists
			if (file_exists($backupDir . '/.env')) {
				$zip->addFile($backupDir . '/.env', '.env');
			}
			
			$zip->close();
			
			// Recursive cleanup function
			$cleanup = function($dir) use (&$cleanup) {
				if (!is_dir($dir)) {
					return;
				}
				
				$files = array_diff(scandir($dir), array('.', '..'));
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
        $backupPath = $backupStorageDir . '/backup_' . (int)$id . '.zip';

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
            if ($zip->open($backupPath) !== TRUE) {
                throw new \Exception('Failed to open backup file');
            }
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
            $cleanup = function($dir) use (&$cleanup) {
                if (!is_dir($dir)) {
                    return;
                }
                
                $files = array_diff(scandir($dir), array('.', '..'));
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
        $files = glob($backupStorageDir . '/backup_*.zip');
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/backup_(\d+)\.zip$/', $filename, $matches)) {
                $id = (int)$matches[1];
                $backups[] = [
                    'id' => $id,
                    'filename' => $filename,
                    'path' => $file,
                    'size' => self::formatSize(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }

        // Sort backups by ID, newest first
        usort($backups, function($a, $b) {
            return $b['id'] - $a['id'];
        });

        return $backups;
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

    public static function deleteBackup($id)
    {
        $backupStorageDir = __DIR__ . '/../../storage/backups';
        $backupPath = $backupStorageDir . '/backup_' . (int)$id . '.zip';

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

}
