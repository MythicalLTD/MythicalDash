<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class TransferManager extends WingsClient
{
    /**
     * Start a server transfer.
     *
     * @param string $serverId Server identifier
     * @param array $options Transfer options including target node
     *
     * @return array Transfer details
     */
    public function startTransfer(string $serverId, array $options): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/transfer", [
            'json' => $options,
        ]);
    }

    /**
     * Get transfer status.
     *
     * @param string $serverId Server identifier
     *
     * @return array Transfer status details
     */
    public function getTransferStatus(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/transfer");
    }

    /**
     * Cancel a server transfer.
     *
     * @param string $serverId Server identifier
     *
     * @return array Response data
     */
    public function cancelTransfer(string $serverId): array
    {
        return $this->request('DELETE', "/api/servers/{$serverId}/transfer");
    }
}
