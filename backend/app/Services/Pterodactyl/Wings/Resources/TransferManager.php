<?php

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class TransferManager extends WingsClient
{
    /**
     * Start a server transfer
     *
     * @param string $serverId Server identifier
     * @param array $options Transfer options including target node
     * @return array Transfer details
     */
    public function startTransfer(string $serverId, array $options): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/transfer", [
            'json' => $options,
        ]);
    }

    /**
     * Get transfer status
     *
     * @param string $serverId Server identifier
     * @return array Transfer status details
     */
    public function getTransferStatus(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/transfer");
    }

    /**
     * Cancel a server transfer
     *
     * @param string $serverId Server identifier
     * @return array Response data
     */
    public function cancelTransfer(string $serverId): array
    {
        return $this->request('DELETE', "/api/servers/{$serverId}/transfer");
    }
} 