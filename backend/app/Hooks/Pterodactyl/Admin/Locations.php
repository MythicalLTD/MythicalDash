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
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource;

class Locations extends LocationsResource
{
    /**
     * Get all locations from Pterodactyl.
     *
     * @param bool $forceRefresh Whether to force refresh the data
     *
     * @return array The list of locations
     */
    public static function getLocations(bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);
        try {
            $locationsResource = new LocationsResource(
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );
            $locationsData = $locationsResource->listLocations(1, 150);
            $locations = [];
            if (isset($locationsData['data']) && is_array($locationsData['data'])) {
                foreach ($locationsData['data'] as $location) {
                    if (isset($location['attributes'])) {
                        $attr = $location['attributes'];
                        $locations[] = [
                            'id' => $attr['id'] ?? null,
                            'short' => $attr['short'] ?? null,
                            'long' => $attr['long'] ?? null,
                            'created_at' => $attr['created_at'] ?? null,
                            'updated_at' => $attr['updated_at'] ?? null,
                        ];
                    }
                }
            }

            return $locations;
        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Locations#getLocations] Failed to fetch locations: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Locations#getLocations] Unexpected error: ' . $e->getMessage(), false);
        }

        return [];
    }

    /**
     * Check if a location exists by ID.
     *
     * @param int $locationId The ID of the location to check
     *
     * @return bool True if the location exists, false otherwise
     */
    public static function doesLocationExist(int $locationId, bool $forceRefresh = false): bool
    {
        $locations = self::getLocations($forceRefresh);
        foreach ($locations as $location) {
            if (isset($location['id']) && $location['id'] == $locationId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get location by ID.
     *
     * @param int $locationId The ID of the location to get
     *
     * @return array|null The location data or null if not found
     */
    public static function getLocationById(int $locationId): ?array
    {
        $locations = self::getLocations();

        foreach ($locations as $location) {
            if (isset($location['id']) && $location['id'] == $locationId) {
                return $location;
            }
        }

        return null;
    }

    /**
     * Get location name by ID.
     *
     * @param int $locationId The ID of the location
     *
     * @return string The location name or "Unknown" if not found
     */
    public static function getLocationNameById(int $locationId): string
    {
        $location = self::getLocationById($locationId);

        if ($location && isset($location['long'])) {
            return $location['long'];
        }

        return 'Unknown';
    }

    /**
     * Clear the locations cache.
     */
    public static function clearLocationsCache(): void
    {
    }

    /**
     * Get location ID mapping for all locations.
     *
     * @return array Associative array of location IDs to names
     */
    public static function getLocationIdMapping(): array
    {
        $locations = self::getLocations();
        $mapping = [];

        foreach ($locations as $location) {
            if (isset($location['id']) && isset($location['long'])) {
                $mapping[$location['id']] = $location['long'];
            }
        }

        return $mapping;
    }
}
