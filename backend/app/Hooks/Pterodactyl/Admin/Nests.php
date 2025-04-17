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
use MythicalDash\Services\Pterodactyl\Admin\Resources\NestsResource;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;

class Nests extends NestsResource
{
    /**
     * Cache directory for storing nests data.
     */
    private const CACHE_DIR = APP_CACHE_DIR . '/pterodactyl/nests';

    /**
     * Cache TTL in seconds (30 minutes).
     */
    private const CACHE_TTL = 1800;

    /**
     * Get all nests from Pterodactyl with caching.
     *
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The list of nests
     */
    public static function getNests(bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);

        // Create cache directory if it doesn't exist
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $cacheFile = self::CACHE_DIR . '/nests.json';

        // Check if cache exists and is valid
        if ($forceRefresh) {
            self::clearNestsCache();
        }

        if (file_exists($cacheFile) && !$forceRefresh) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if ($cacheData && isset($cacheData['timestamp'])
                && (time() - $cacheData['timestamp']) < self::CACHE_TTL) {
                return $cacheData['nests'];
            }
        }

        // Cache doesn't exist or is invalid, fetch from API
        try {
            $nestsResource = new NestsResource(
                $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $nestsData = $nestsResource->listNests(1, 150);

            // Process and format nests data
            $nests = [];
            if (isset($nestsData['data']) && is_array($nestsData['data'])) {
                foreach ($nestsData['data'] as $nest) {
                    if (isset($nest['attributes'])) {
                        $attr = $nest['attributes'];
                        $nests[] = [
                            'id' => $attr['id'] ?? null,
                            'uuid' => $attr['uuid'] ?? null,
                            'author' => $attr['author'] ?? null,
                            'name' => $attr['name'] ?? null,
                            'description' => $attr['description'] ?? null,
                            'created_at' => $attr['created_at'] ?? null,
                            'updated_at' => $attr['updated_at'] ?? null,
                        ];
                    }
                }
            }

            // Cache the results
            $cacheData = [
                'timestamp' => time(),
                'nests' => $nests,
            ];
            file_put_contents($cacheFile, json_encode($cacheData));

            return $nests;

        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nests#getNests] Failed to fetch nests: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nests#getNests] Unexpected error: ' . $e->getMessage(), false);
        }

        // Return empty array if there was an error
        return [];
    }

    /**
     * Check if a nest exists by ID.
     *
     * @param int $nestId The ID of the nest to check
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return bool True if the nest exists, false otherwise
     */
    public static function doesNestExist(int $nestId, bool $forceRefresh = false): bool
    {
        $nests = self::getNests($forceRefresh);
        foreach ($nests as $nest) {
            if (isset($nest['id']) && $nest['id'] == $nestId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get nest by ID.
     *
     * @param int $nestId The ID of the nest to get
     *
     * @return array|null The nest data or null if not found
     */
    public static function getNestById(int $nestId): ?array
    {
        $nests = self::getNests();

        foreach ($nests as $nest) {
            if (isset($nest['id']) && $nest['id'] == $nestId) {
                return $nest;
            }
        }

        return null;
    }

    /**
     * Get nest name by ID.
     *
     * @param int $nestId The ID of the nest
     *
     * @return string The nest name or "Unknown" if not found
     */
    public static function getNestNameById(int $nestId): string
    {
        $nest = self::getNestById($nestId);

        if ($nest && isset($nest['name'])) {
            return $nest['name'];
        }

        return 'Unknown';
    }

    /**
     * Clear the nests cache.
     */
    public static function clearNestsCache(): void
    {
        $cacheFile = self::CACHE_DIR . '/nests.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Get nest ID mapping for all nests.
     *
     * @return array Associative array of nest IDs to names
     */
    public static function getNestIdMapping(): array
    {
        $nests = self::getNests();
        $mapping = [];

        foreach ($nests as $nest) {
            if (isset($nest['id']) && isset($nest['name'])) {
                $mapping[$nest['id']] = $nest['name'];
            }
        }

        return $mapping;
    }
}
