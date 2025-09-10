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
use MythicalDash\Services\Pterodactyl\Admin\Resources\NestsResource;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;

class Eggs extends NestsResource
{
    /**
     * Get all eggs for a specific nest from Pterodactyl.
     *
     * @param int $nestId The ID of the nest to get eggs for
     * @param bool $forceRefresh Whether to force refresh the data
     *
     * @return array The list of eggs
     */
    public static function getEggs(int $nestId, bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);
        try {
            $eggsResource = new NestsResource(
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );
            $eggsData = $eggsResource->listEggs($nestId, 1, 555);
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

            return $eggs;
        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Eggs#getEggs] Failed to fetch eggs: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Eggs#getEggs] Unexpected error: ' . $e->getMessage(), false);
        }

        return [];
    }

    /**
     * Get all eggs from all nests.
     *
     * @param bool $forceRefresh Whether to force refresh the data
     *
     * @return array The list of all eggs
     */
    public static function getAllEggs(bool $forceRefresh = false): array
    {
        $nests = Nests::getNests($forceRefresh);
        $allEggs = [];
        foreach ($nests as $nest) {
            if (isset($nest['id'])) {
                $eggs = self::getEggs($nest['id'], true);
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
     * @param bool $forceRefresh Whether to force refresh the data
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
     * @param bool $forceRefresh Whether to force refresh the data
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
    }

    /**
     * Clear all eggs cache.
     */
    public static function clearAllEggsCache(): void
    {
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
