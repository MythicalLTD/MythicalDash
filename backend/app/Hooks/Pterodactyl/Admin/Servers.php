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
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class Servers extends ServersResource
{
    /**
     * Get the total resources usage for a user.
     *
     * @param int $pterodactylUserId The ID of the user to get the total resources usage for
     *
     * @return array The total resources usage for the user
     */
    public static function getUserTotalResourcesUsage(int $pterodactylUserId, bool $forceRefresh = false): array
    {
        return self::getUserData($pterodactylUserId)['resources'];
    }

    /**
     * Get the list of servers for a user.
     *
     * @param int $pterodactylUserId The ID of the user to get servers for
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The list of servers for the user
     */
    public static function getUserServersList(int $pterodactylUserId, bool $forceRefresh = false): array
    {
        return self::getUserData($pterodactylUserId)['servers'];
    }

    /**
     * Get the server details for a user.
     *
     * @param int $pterodactylUserId The ID of the user to get server details for
     * @param int $serverId The ID of the server to get details for
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The server details
     */
    public static function getUserServerDetails(int $pterodactylUserId, int $serverId, bool $forceRefresh = false): array
    {
        return self::getUserData($pterodactylUserId)['servers'][$serverId] ?? [];
    }

    /**
     * Get the server details for a server ID.
     *
     * @param int $serverId The ID of the server to get details for
     *
     * @return array The server details
     */
    public static function getServerPterodactylDetails(int $serverId): array
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $server = $serversResource->getServer($serverId);

            return $server;
        } catch (ResourceNotFoundException $e) {
            return [];
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#getServerPterodactylDetails] Failed to check server existence: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Clear the user cache.
     *
     * @param int $pterodactylUserId The ID of the user to clear cache for
     */
    public static function clearUserCache(int $pterodactylUserId): void
    {
    }

    /**
     * Clear all user caches.
     */
    public static function clearAllCaches(): void
    {
    }

    /**
     * Check if a server exists in Pterodactyl.
     *
     * @param string $serverIdentifier The server identifier to check
     *
     * @return bool Whether the server exists
     */
    public static function serverExists(string $serverIdentifier): bool
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $serversResource->getServer($serverIdentifier);

            return true;
        } catch (ResourceNotFoundException $e) {
            return false;
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#serverExists] Failed to check server existence: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Suspend a server in Pterodactyl.
     *
     * @param int $serverId The ID of the server to suspend
     */
    public static function performSuspendServer(int $serverId): void
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $serversResource->suspendServer($serverId);
        } catch (ResourceNotFoundException $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#performSuspendServer] Server not found: ' . $e->getMessage());
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#performSuspendServer] Failed to suspend server: ' . $e->getMessage());
        }
    }

    /**
     * Unsuspend a server in Pterodactyl.
     *
     * @param int $serverId The ID of the server to unsuspend
     */
    public static function performUnsuspendServer(int $serverId): void
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $serversResource->unsuspendServer($serverId);
        } catch (ResourceNotFoundException $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#performUnsuspendServer] Server not found: ' . $e->getMessage());
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#performUnsuspendServer] Failed to unsuspend server: ' . $e->getMessage());
        }
    }

    /**
     * Delete a server from Pterodactyl.
     *
     * @param int $serverId The server ID to delete
     * @param bool $force Whether to force delete the server
     *
     * @return array The deletion result
     */
    public static function deletePterodactylServer(int $serverId, bool $force = false): void
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $serversResource->deleteServer($serverId, $force);
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#deleteServer] Failed to delete server: ' . $e->getMessage());
        }
    }

    public static function updatePterodactylServer(int $serverId, array $updateData): array
    {
        $appInstance = App::getInstance(true);

        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            return $serversResource->updateServerBuild($serverId, $updateData);
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServer] Server not found', false);

            return [];
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServer] Failed to update server: ' . $e->getMessage(), false);

            return [];
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServer] Unexpected error: ' . $e->getMessage(), false);

            return [];
        }
    }

    /**
     * Update the details of a server in Pterodactyl.
     *
     * @param int $serverId The ID of the server to update
     * @param array $updateData The data to update the server with
     *
     * @return array The update result
     */
    public static function updatePterodactylServerDetails(int $serverId, array $updateData): array
    {
        $appInstance = App::getInstance(true);

        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            return $serversResource->updateServerDetails($serverId, $updateData);
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServerDetails] Server not found', false);

            return [];
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServerDetails] Failed to update server details: ' . $e->getMessage(), false);

            return [];
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#updatePterodactylServerDetails] Unexpected error: ' . $e->getMessage(), false);

            return [];
        }
    }

    /**
     * Get the server count by location.
     *
     * @param int $locationId The ID of the location to get the server count for
     *
     * @return int The server count
     */
    public static function getServerCountByLocation(int $locationId): int
    {
        $appInstance = App::getInstance(true);

        try {
            $locationResource = new LocationsResource(
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $location = $locationResource->getLocation($locationId);

            if (!isset($location['attributes']) || !isset($location['attributes']['relationships'])
                || !isset($location['attributes']['relationships']['servers'])
                || !isset($location['attributes']['relationships']['servers']['data'])) {
                $appInstance->getLogger()->warning('[Pterodactyl/Admin/Servers#getServerCountByLocation] Server data not found in location attributes');

                return 0;
            }

            return count($location['attributes']['relationships']['servers']['data']);
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getServerCountByLocation] Locations not found: ' . $e->getMessage(), false);

            return 0;
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getServerCountByLocation] Location error:  ' . $e->getMessage(), false);

            return 0;
        }
    }

    public static function getAllServers(int $page = 1, int $perPage = 250): array
    {
        $appInstance = App::getInstance(true);

        try {
            $serversResource = new ServersResource(
                App::getInstance(softBoot: true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            return $serversResource->listServers($page, $perPage);
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getAllServers] User not found: ' . $e->getMessage(), false);

            return [];
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getAllServers] Failed to fetch user data: ' . $e->getMessage(), false);

            return [];
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getAllServers] Unexpected pterodactyl panel error: ' . $e->getMessage(), false);

            return [];
        }
    }

    /**
     * Get user data from cache or API.
     *
     * @param int $pterodactylUserId The ID of the user
     *
     * @return array The user data including servers and resources
     */
    private static function getUserData(int $pterodactylUserId): array
    {
        $appInstance = App::getInstance(true);
        try {
            $userResource = new UsersResource(
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                $appInstance->getConfig()->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );
            $userInfo = $userResource->getUserWithServers($pterodactylUserId);
            $servers = $userInfo['attributes']['relationships']['servers']['data'] ?? [];
            $resources = [
                'memory' => 0,
                'cpu' => 0,
                'disk' => 0,
                'backups' => 0,
                'databases' => 0,
                'allocations' => 0,
                'servers' => count($servers),
            ];
            $serversList = [];
            foreach ($servers as $server) {
                if (!isset($server['attributes'])) {
                    continue;
                }
                $attr = $server['attributes'];
                if (isset($attr['limits'])) {
                    $resources['memory'] += intval($attr['limits']['memory'] ?? 0);
                    $resources['cpu'] += intval($attr['limits']['cpu'] ?? 0);
                    $resources['disk'] += intval($attr['limits']['disk'] ?? 0);
                }
                if (isset($attr['feature_limits'])) {
                    $resources['backups'] += intval($attr['feature_limits']['backups'] ?? 0);
                    $resources['databases'] += intval($attr['feature_limits']['databases'] ?? 0);
                    $resources['allocations'] += intval($attr['feature_limits']['allocations'] ?? 0);
                }
                $serversList[] = [
                    'id' => $attr['id'] ?? null,
                    'identifier' => $attr['identifier'] ?? null,
                    'uuid' => $attr['uuid'] ?? null,
                    'external_id' => $attr['external_id'] ?? null,
                    'name' => $attr['name'] ?? null,
                    'description' => $attr['description'] ?? '',
                    'status' => $attr['status'] ?? null,
                    'suspended' => $attr['suspended'] ?? false,
                    'limits' => $attr['limits'] ?? [],
                    'feature_limits' => $attr['feature_limits'] ?? [],
                    'user' => $attr['user'] ?? null,
                    'node' => $attr['node'] ?? null,
                    'allocation' => $attr['allocation'] ?? null,
                    'nest' => $attr['nest'] ?? null,
                    'egg' => $attr['egg'] ?? null,
                    'created_at' => $attr['created_at'] ?? null,
                    'updated_at' => $attr['updated_at'] ?? null,
                    'container' => [
                        'startup_command' => $attr['container']['startup_command'] ?? '',
                        'image' => $attr['container']['image'] ?? '',
                        'installed' => $attr['container']['installed'] ?? 0,
                        'environment' => $attr['container']['environment'] ?? [],
                    ],
                ];
            }

            return [
                'resources' => $resources,
                'servers' => $serversList,
            ];
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] User not found: ' . $e->getMessage(), false);
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Failed to fetch user data: ' . $e->getMessage(), false);
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Unexpected pterodactyl panel error: ' . $e->getMessage(), false);
        }

        return ['resources' => [], 'servers' => []];
    }
}
