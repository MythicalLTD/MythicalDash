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

namespace MythicalDash\Hooks;

use MythicalDash\App;

class MythicalCloud
{
    /**
     * Upload a backup to the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     * @param string $backupPath The path to the backup file
     * @param string $osType The operating system type
     * @param string $kernelVersion The kernel version
     * @param string $cpuArchitecture The CPU architecture
     *
     * @return array|null Returns the response data on success, null on failure
     */
    public static function uploadBackup(
        string $licenseKey,
        string $backupPath,
        string $osType = 'Windows',
        string $kernelVersion = '10.0',
        string $cpuArchitecture = 'x64',
    ): ?array {
        try {
            if (!file_exists($backupPath)) {
                throw new \Exception('Backup file not found');
            }

            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'PUT',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/cloud/backup",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                    'multipart' => [
                        [
                            'name' => 'backup',
                            'contents' => fopen($backupPath, 'r'),
                            'filename' => basename($backupPath),
                        ],
                        [
                            'name' => 'os_type',
                            'contents' => $osType,
                        ],
                        [
                            'name' => 'kernel_version',
                            'contents' => $kernelVersion,
                        ],
                        [
                            'name' => 'cpu_architecture',
                            'contents' => $cpuArchitecture,
                        ],
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('Failed to decode response JSON');
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('API error: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload backup to cloud: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get list of backups and storage information from the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     *
     * @return array|null Returns the response data on success, null on failure
     */
    public static function getBackups(string $licenseKey): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'GET',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/backups",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('Failed to decode response JSON');
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('API error: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get backups from cloud: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get detailed information about a specific backup from the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     * @param string|int $backupId The ID of the backup to retrieve information for
     *
     * @return array|null Returns the response data on success, null on failure
     */
    public static function getBackupInfo(string $licenseKey, $backupId): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'GET',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/backup/{$backupId}/info",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('Failed to decode response JSON');
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('API error: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get backup info from cloud: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Delete a backup from the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     * @param string|int $backupId The ID of the backup to delete
     *
     * @return array|null Returns the response data on success, null on failure
     */
    public static function deleteBackup(string $licenseKey, $backupId): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'DELETE',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/backup/{$backupId}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('Failed to decode response JSON');
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('API error: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to delete backup from cloud: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Delete all backups from the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     *
     * @return array|null Returns the response data on success, null on failure
     */
    public static function deleteAllBackups(string $licenseKey): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'DELETE',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/backups",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('Failed to decode response JSON');
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('API error: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to delete all backups from cloud: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Download a backup from the Mythical Cloud service.
     *
     * @param string $licenseKey The license key for authentication
     * @param string|int $backupId The ID of the backup to download
     * @param string $savePath Optional path to save the backup file. If not provided, returns the file contents
     *
     * @return string|bool Returns the file contents if $savePath is not provided, true if saved successfully, false on failure
     */
    public static function downloadBackup(string $licenseKey, $backupId, string $savePath)
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request(
                'GET',
                "https://mymythicalid.mythical.systems/api/system/cloud/license/{$licenseKey}/backup/{$backupId}/download",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'MythicalDash/1.0',
                    ],
                ]
            );

            $contents = $response->getBody()->getContents();

            if (file_put_contents($savePath, $contents) === false) {
                throw new \Exception('Failed to save backup file');
            }

            return true;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to download backup from cloud: ' . $e->getMessage());

            return false;
        }
    }
}
