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

namespace MythicalDash\Hooks\Pterodactyl\Admin;

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Pterodactyl\Admin\Resources\NodesResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class Nodes extends NodesResource
{
    /**
     * Get node information by ID.
     *
     * @param int $nodeId The ID of the node
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The node information
     */
    public static function getNodeInfo(int $nodeId, bool $forceRefresh = false): array
    {
        return self::getNodeData($nodeId);
    }

    /**
     * Get location ID from node ID.
     *
     * @param int $nodeId The ID of the node
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return int|null The location ID or null if not found
     */
    public static function getLocationIdFromNode(int $nodeId, bool $forceRefresh = false): ?int
    {
        $nodeData = self::getNodeData($nodeId);

        return $nodeData['attributes']['location_id'] ?? null;
    }

    /**
     * Clear the node cache.
     *
     * @param int $nodeId The ID of the node to clear cache for
     */
    public static function clearNodeCache(int $nodeId): void
    {
    }

    /**
     * Clear all node caches.
     */
    public static function clearAllCaches(): void
    {
    }

    /**
     * Get node data from cache or API.
     *
     * @param int $nodeId The ID of the node
     *
     * @return array The node data
     */
    private static function getNodeData(int $nodeId): array
    {
        $appInstance = App::getInstance(true);
        try {
            $nodeResource = new NodesResource(
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );
            $nodeInfo = $nodeResource->getNode($nodeId);

            return [
                'attributes' => $nodeInfo['attributes'] ?? [],
            ];
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Node not found', false);
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Failed to fetch node data', false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Unexpected error', false);
        }

        return ['attributes' => []];
    }
}
