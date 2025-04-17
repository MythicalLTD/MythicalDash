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
     * Cache directory for storing node data.
     */
    private const CACHE_DIR = APP_CACHE_DIR . '/pterodactyl/nodes';

    /**
     * Cache TTL in seconds (2 minutes).
     */
    private const CACHE_TTL = 120;

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
        return self::getNodeData($nodeId, $forceRefresh);
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
        $nodeData = self::getNodeData($nodeId, $forceRefresh);

        return $nodeData['attributes']['location_id'] ?? null;
    }

    /**
     * Clear the node cache.
     *
     * @param int $nodeId The ID of the node to clear cache for
     */
    public static function clearNodeCache(int $nodeId): void
    {
        $cacheFile = self::CACHE_DIR . '/node_' . $nodeId . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Clear all node caches.
     */
    public static function clearAllCaches(): void
    {
        if (is_dir(self::CACHE_DIR)) {
            $files = glob(self::CACHE_DIR . '/*.json');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Get node data from cache or API.
     *
     * @param int $nodeId The ID of the node
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The node data
     */
    private static function getNodeData(int $nodeId, bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);

        // Create cache directory if it doesn't exist
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $cacheFile = self::CACHE_DIR . '/node_' . $nodeId . '.json';

        // Check if cache exists and is valid
        if ($forceRefresh) {
            self::clearNodeCache($nodeId);
        }

        if (file_exists($cacheFile) && !$forceRefresh) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if ($cacheData && isset($cacheData['timestamp'])
                && (time() - $cacheData['timestamp']) < self::CACHE_TTL) {
                return $cacheData;
            }
        }

        $nodeResource = new NodesResource(
            App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
            App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
        );

        try {
            $nodeInfo = $nodeResource->getNode($nodeId);
            $nodeData = [
                'timestamp' => time(),
                'attributes' => $nodeInfo['attributes'] ?? [],
            ];

            // Cache the results
            file_put_contents($cacheFile, json_encode($nodeData));

            return $nodeData;

        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Node not found', false);
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Failed to fetch node data', false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nodes#getNodeData] Unexpected error', false);
        }

        return ['timestamp' => time(), 'attributes' => []];
    }
}
