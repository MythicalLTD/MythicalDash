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
