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

namespace MythicalDash\Chat\Locations;

use MythicalDash\Chat\Database;

class Locations extends Database
{
    public const TABLE_NAME = 'mythicaldash_locations';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get all locations.
     *
     * @return array The locations
     */
    public static function getLocations(): array
    {
        $dbConn = Database::getPdoConnection();
        $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a location by Pterodactyl location ID.
     *
     * @param int $pterodactylLocationId The Pterodactyl location ID to get
     *
     * @return array|null The location data or null if not found
     */
    public static function getLocationByPterodactylLocationId(int $pterodactylLocationId): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE pterodactyl_location_id = :pterodactyl_location_id AND deleted = "false"');
            $stmt->bindParam(':pterodactyl_location_id', $pterodactylLocationId);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get location by Pterodactyl location ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create a new location.
     *
     * @param string $name The name of the location
     * @param string $description The description of the location
     * @param int $pterodactylLocationId The Pterodactyl location ID
     * @param string $nodeIp The IP address of the node
     * @param string $status The status of the location
     * @param int $slots The number of slots available for the location
     *
     * @return int The ID of the location
     */
    public static function create(string $name, string $description, int $pterodactylLocationId, string $nodeIp, string $status = 'active', int $slots = 15): int
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' (name, description, pterodactyl_location_id, node_ip, status, slots) VALUES (:name, :description, :pterodactyl_location_id, :node_ip, :status, :slots)');
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':pterodactyl_location_id', $pterodactylLocationId);
            $stmt->bindParam(':node_ip', $nodeIp);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':slots', $slots);

            $stmt->execute();

            return $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create location: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Update an existing location.
     *
     * @param int $id The ID of the location to update
     * @param string $name The new name of the location
     * @param string $description The new description of the location
     * @param string $nodeIp The new IP address of the node
     * @param string $status The new status of the location
     * @param int $slots The new number of slots available for the location
     *
     * @return bool True if the location was updated successfully, false otherwise
     */
    public static function update(int $id, string $name, string $description, string $nodeIp, string $status, int $slots = 15): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Location does not exist but tried to update it: ' . $id);

                return false;
            }

            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET name = :name, description = :description, node_ip = :node_ip, status = :status, slots = :slots WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':node_ip', $nodeIp);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':slots', $slots);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update location: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a location.
     *
     * @param int $id The ID of the location to delete
     *
     * @return bool True if the location was deleted successfully, false otherwise
     */
    public static function delete(int $id): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Location does not exist but tried to delete it: ' . $id);

                return false;
            }

            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('DELETE FROM ' . self::getTableName() . ' WHERE id = :id');
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete location: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a location by ID.
     *
     * @param int $id The ID of the location to get
     *
     * @return array|null The location data or null if not found
     */
    public static function get(int $id): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get location: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if a location exists.
     *
     * @param int $id The ID of the location to check
     *
     * @return bool True if the location exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if location exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a location exists by Pterodactyl location ID.
     *
     * @param int $pterodactylLocationId The Pterodactyl location ID to check
     *
     * @return bool True if the location exists, false otherwise
     * @return bool If the location exists
     */
    public static function existsByPterodactylLocationId(int $pterodactylLocationId): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE pterodactyl_location_id = :pterodactyl_location_id');
            $stmt->bindParam(':pterodactyl_location_id', $pterodactylLocationId);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if location exists by Pterodactyl location ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all locations with a specific status.
     *
     * @param string $status The status to filter by
     *
     * @return array The locations with the specified status
     */
    public static function getLocationsByStatus(string $status): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE status = :status');
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get locations by status: ' . $e->getMessage());

            return [];
        }
    }
}
