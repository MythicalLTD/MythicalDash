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

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class InstallationManager extends WingsClient
{
    /**
     * Get installation status.
     *
     * @param string $serverId Server identifier
     *
     * @return array Installation status details
     */
    public function getStatus(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/install");
    }

    /**
     * Start server installation.
     *
     * @param string $serverId Server identifier
     * @param array $options Installation options
     *
     * @return array Installation details
     */
    public function startInstallation(string $serverId, array $options): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/install", [
            'json' => $options,
        ]);
    }

    /**
     * Cancel server installation.
     *
     * @param string $serverId Server identifier
     *
     * @return array Response data
     */
    public function cancelInstallation(string $serverId): array
    {
        return $this->request('DELETE', "/api/servers/{$serverId}/install");
    }
}
