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

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class BackupManager extends WingsClient
{
    /**
     * List all backups for a server.
     *
     * @param string $serverId Server identifier
     *
     * @return array List of backups
     */
    public function listBackups(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/backups");
    }

    /**
     * Create a new backup.
     *
     * @param string $serverId Server identifier
     * @param array $options Backup options
     *
     * @return array Backup details
     */
    public function createBackup(string $serverId, array $options = []): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/backups", [
            'json' => $options,
        ]);
    }

    /**
     * Get details of a specific backup.
     *
     * @param string $serverId Server identifier
     * @param string $backupId Backup identifier
     *
     * @return array Backup details
     */
    public function getBackup(string $serverId, string $backupId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/backups/{$backupId}");
    }

    /**
     * Delete a backup.
     *
     * @param string $serverId Server identifier
     * @param string $backupId Backup identifier
     *
     * @return array Response data
     */
    public function deleteBackup(string $serverId, string $backupId): array
    {
        return $this->request('DELETE', "/api/servers/{$serverId}/backups/{$backupId}");
    }

    /**
     * Download a backup.
     *
     * @param string $serverId Server identifier
     * @param string $backupId Backup identifier
     *
     * @return array Download URL and details
     */
    public function downloadBackup(string $serverId, string $backupId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/backups/{$backupId}/download");
    }

    /**
     * Restore a backup.
     *
     * @param string $serverId Server identifier
     * @param string $backupId Backup identifier
     *
     * @return array Response data
     */
    public function restoreBackup(string $serverId, string $backupId): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/backups/{$backupId}/restore");
    }
}
