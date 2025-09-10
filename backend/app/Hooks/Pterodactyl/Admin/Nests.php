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

class Nests extends NestsResource
{
    /**
     * Get all nests from Pterodactyl.
     *
     * @param bool $forceRefresh Whether to force refresh the data
     *
     * @return array The list of nests
     */
    public static function getNests(bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);
        try {
            $nestsResource = new NestsResource(
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );
            $nestsData = $nestsResource->listNests(1, 150);
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

            return $nests;
        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nests#getNests] Failed to fetch nests: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Nests#getNests] Unexpected error: ' . $e->getMessage(), false);
        }

        return [];
    }

    /**
     * Check if a nest exists by ID.
     *
     * @param int $nestId The ID of the nest to check
     * @param bool $forceRefresh Whether to force refresh the data
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
