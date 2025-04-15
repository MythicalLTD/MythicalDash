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
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class Servers extends ServersResource
{
    /**
     * Cache directory for storing user data.
     */
    private const CACHE_DIR = APP_CACHE_DIR . '/pterodactyl/users';

    /**
     * Cache TTL in seconds (2 minutes).
     */
    private const CACHE_TTL = 120;

    /**
     * Get the total resources usage for a user.
     *
     * @param int $pterodactylUserId The ID of the user to get the total resources usage for
     *
     * @return array The total resources usage for the user
     */
    public static function getUserTotalResourcesUsage(int $pterodactylUserId, bool $forceRefresh = false): array
    {
        return self::getUserData($pterodactylUserId, $forceRefresh)['resources'];
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
        return self::getUserData($pterodactylUserId, $forceRefresh)['servers'];
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
        return self::getUserData($pterodactylUserId, $forceRefresh)['servers'][$serverId] ?? [];
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $server = $serversResource->getServer($serverId);

            return $server;
        } catch (ResourceNotFoundException $e) {
            return [];
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#serverExists] Failed to check server existence: ' . $e->getMessage());

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
        $cacheFile = self::CACHE_DIR . '/user_' . $pterodactylUserId . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Clear all user caches.
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
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
    public static function deletePterodactylServer(int $serverId, bool $force = false): array
    {
        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            return $serversResource->deleteServer($serverId, $force);
        } catch (\Throwable $e) {
            App::getInstance(true)->getLogger()->error('[Pterodactyl/Admin/Servers#deleteServer] Failed to delete server: ' . $e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function updatePterodactylServer(int $serverId, array $updateData): array
    {
        $appInstance = App::getInstance(true);

        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
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
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            $location = $locationResource->getLocation($locationId);

            return count($location['attributes']['relationships']['servers']['data'], COUNT_NORMAL);
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getServerCountByLocation] Locations not found: ' . $e->getMessage(), false);

            return 0;
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getServerCountByLocation] Location error:  ' . $e->getMessage(), false);

            return 0;
        }
    }

    public static function getAllServers(): array
    {
        $appInstance = App::getInstance(true);

        try {
            $serversResource = new ServersResource(
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
                App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
            );

            return $serversResource->listServers();
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] User not found', false);

            return [];
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Failed to fetch user data', false);

            return [];
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Unexpected error', false);

            return [];
        }
    }

    /**
     * Get user data from cache or API.
     *
     * @param int $pterodactylUserId The ID of the user
     * @param bool $forceRefresh Whether to force refresh the cache
     *
     * @return array The user data including servers and resources
     */
    private static function getUserData(int $pterodactylUserId, bool $forceRefresh = false): array
    {
        $appInstance = App::getInstance(true);

        // Create cache directory if it doesn't exist
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $cacheFile = self::CACHE_DIR . '/user_' . $pterodactylUserId . '.json';

        // Check if cache exists and is valid
        if ($forceRefresh) {
            self::clearUserCache($pterodactylUserId);
        }

        if (file_exists($cacheFile) && !$forceRefresh) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (
                $cacheData && isset($cacheData['timestamp'])
                && (time() - $cacheData['timestamp']) < self::CACHE_TTL
            ) {
                return $cacheData;
            }
        }

        $userResource = new UsersResource(
            App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''),
            App::getInstance(true)->getConfig()->getSetting(ConfigInterface::PTERODACTYL_API_KEY, '')
        );

        try {
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

                // Calculate resources
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

                // Build servers list
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

            $userData = [
                'timestamp' => time(),
                'resources' => $resources,
                'servers' => $serversList,
            ];

            // Cache the results
            file_put_contents($cacheFile, json_encode($userData));

            return $userData;

        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] User not found', false);

            return [];
        } catch (PterodactylException|ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Failed to fetch user data', false);

            return [];
        } catch (\Throwable $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/Servers#getUserData] Unexpected error', false);

            return [];
        }
    }
}
