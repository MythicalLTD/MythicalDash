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

class Eggs extends NestsResource
{
    /**
     * Cache directory for storing eggs data.
     */
    private const CACHE_DIR = APP_CACHE_DIR . '/pterodactyl/eggs';

    /**
     * Cache TTL in seconds (30 minutes).
     */
    private const CACHE_TTL = 1800;

    /**
     * Get all eggs for a specific nest from Pterodactyl with caching.
     *
     * @param int $nestId The ID of the nest to get eggs for
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The list of eggs
     */
    public static function getEggs(int $nestId, bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);

        // Create cache directory if it doesn't exist
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $cacheFile = self::CACHE_DIR . '/eggs_nest_' . $nestId . '.json';

        // Check if cache exists and is valid
        if ($forceRefresh) {
            self::clearEggsCache($nestId);
        }

        if (file_exists($cacheFile) && !$forceRefresh) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if ($cacheData && isset($cacheData['timestamp'])
                && (time() - $cacheData['timestamp']) < self::CACHE_TTL) {
                return $cacheData['eggs'];
            }
        }

        // Cache doesn't exist or is invalid, fetch from API
        try {
            $eggsResource = new NestsResource(
                $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $eggsData = $eggsResource->listEggs($nestId, 1, 555);

            // Process and format eggs data
            $eggs = [];
            if (isset($eggsData['data']) && is_array($eggsData['data'])) {
                foreach ($eggsData['data'] as $egg) {
                    if (isset($egg['attributes'])) {
                        $attr = $egg['attributes'];
                        $eggs[] = [
                            'id' => $attr['id'] ?? null,
                            'uuid' => $attr['uuid'] ?? null,
                            'name' => $attr['name'] ?? null,
                            'nest' => $attr['nest'] ?? null,
                            'author' => $attr['author'] ?? null,
                            'description' => $attr['description'] ?? null,
                            'docker_image' => $attr['docker_image'] ?? null,
                            'config' => $attr['config'] ?? null,
                            'startup' => $attr['startup'] ?? null,
                            'script' => $attr['script'] ?? null,
                            'created_at' => $attr['created_at'] ?? null,
                            'updated_at' => $attr['updated_at'] ?? null,
                        ];
                    }
                }
            }

            // Cache the results
            $cacheData = [
                'timestamp' => time(),
                'eggs' => $eggs,
            ];
            file_put_contents($cacheFile, json_encode($cacheData));

            return $eggs;

        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Eggs#getEggs] Failed to fetch eggs: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Eggs#getEggs] Unexpected error: ' . $e->getMessage(), false);
        }

        // Return empty array if there was an error
        return [];
    }

    /**
     * Get all eggs from all nests.
     *
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The list of all eggs
     */
    public static function getAllEggs(bool $forceRefresh = false): array
    {
        $nests = Nests::getNests($forceRefresh);
        $allEggs = [];

        foreach ($nests as $nest) {
            if (isset($nest['id'])) {
                $eggs = self::getEggs($nest['id'], $forceRefresh);
                foreach ($eggs as $egg) {
                    $allEggs[] = $egg;
                }
            }
        }

        return $allEggs;
    }

    /**
     * Get a specific egg by ID.
     *
     * @param int $eggId The ID of the egg to get
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array|null The egg data or null if not found
     */
    public static function getEggById(int $eggId, bool $forceRefresh = false): ?array
    {
        $allEggs = self::getAllEggs($forceRefresh);

        foreach ($allEggs as $egg) {
            if (isset($egg['id']) && $egg['id'] == $eggId) {
                return $egg;
            }
        }

        return null;
    }

    /**
     * Check if an egg exists by ID.
     *
     * @param int $eggId The ID of the egg to check
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return bool True if the egg exists, false otherwise
     */
    public static function doesEggExist(int $eggId, bool $forceRefresh = false): bool
    {
        return self::getEggById($eggId, $forceRefresh) !== null;
    }

    /**
     * Get egg name by ID.
     *
     * @param int $eggId The ID of the egg
     *
     * @return string The egg name or "Unknown" if not found
     */
    public static function getEggNameById(int $eggId): string
    {
        $egg = self::getEggById($eggId);

        if ($egg && isset($egg['name'])) {
            return $egg['name'];
        }

        return 'Unknown';
    }

    /**
     * Clear the eggs cache for a specific nest.
     *
     * @param int $nestId The ID of the nest
     */
    public static function clearEggsCache(int $nestId): void
    {
        $cacheFile = self::CACHE_DIR . '/eggs_nest_' . $nestId . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Clear all eggs cache.
     */
    public static function clearAllEggsCache(): void
    {
        $nests = Nests::getNests();
        foreach ($nests as $nest) {
            if (isset($nest['id'])) {
                self::clearEggsCache($nest['id']);
            }
        }
    }

    /**
     * Get egg ID mapping for all eggs.
     *
     * @return array Associative array of egg IDs to names
     */
    public static function getEggIdMapping(): array
    {
        $allEggs = self::getAllEggs();
        $mapping = [];

        foreach ($allEggs as $egg) {
            if (isset($egg['id']) && isset($egg['name'])) {
                $mapping[$egg['id']] = $egg['name'];
            }
        }

        return $mapping;
    }
}
