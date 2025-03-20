<?php

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class InstallationManager extends WingsClient
{
    /**
     * Get installation status
     *
     * @param string $serverId Server identifier
     * @return array Installation status details
     */
    public function getStatus(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/install");
    }

    /**
     * Start server installation
     *
     * @param string $serverId Server identifier
     * @param array $options Installation options
     * @return array Installation details
     */
    public function startInstallation(string $serverId, array $options): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/install", [
            'json' => $options,
        ]);
    }

    /**
     * Cancel server installation
     *
     * @param string $serverId Server identifier
     * @return array Response data
     */
    public function cancelInstallation(string $serverId): array
    {
        return $this->request('DELETE', "/api/servers/{$serverId}/install");
    }
} 