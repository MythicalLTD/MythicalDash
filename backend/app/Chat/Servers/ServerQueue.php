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

namespace MythicalDash\Chat\Servers;

use MythicalDash\Chat\Database;

class ServerQueue extends Database
{
    public const TABLE_NAME = 'mythicaldash_servers_queue';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get all server queue items.
     *
     * @return array The list of server queue items
     */
    public static function getAll(): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"';

            $stmt = $dbConn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get server queue items: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all server queue items by user.
     *
     * @param string $user The user ID
     *
     * @return array The list of server queue items
     */
    public static function getByUser(string $user): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' WHERE user = :user AND deleted = "false"';
            $stmt = $dbConn->prepare($query);
            $stmt->bindParam(':user', $user);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get server queue items by user: ' . $e->getMessage());

            return [];
        }
    }

    public static function hasAtLeastOnePendingItem(string $user): bool
    {
        $items = self::getByUser($user);
        foreach ($items as $item) {
            if ($item['status'] == 'pending') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a specific server queue item by ID.
     *
     * @param int $id The ID of the server queue item
     *
     * @return array|null The server queue item or null if not found
     */
    public static function getById(int $id): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';

            $stmt = $dbConn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get server queue item: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create a new server queue item.
     *
     * @param string $name The name of the server
     * @param string $description The description of the server
     * @param int $ram The RAM allocation in MB
     * @param int $disk The disk allocation in MB
     * @param int $cpu The CPU allocation percentage
     * @param int $ports The number of ports
     * @param int $databases The number of databases
     * @param int $backups The number of backups
     * @param int $location The location ID
     * @param int $user The user ID
     * @param int $nest The nest ID
     * @param int $egg The egg ID
     * @param string $status The status of the server (pending, building, failed)
     *
     * @return int|false The ID of the newly created item, or false on failure
     */
    public static function create(
        string $name,
        string $description,
        int $ram,
        int $disk,
        int $cpu,
        int $ports,
        int $databases,
        int $backups,
        int $location,
        string $user,
        int $nest,
        int $egg,
        string $status = 'pending',
    ): int|false {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' 
                (`name`, `description`, `ram`, `disk`, `cpu`, `ports`, `databases`, `backups`, `location`, `user`, `nest`, `egg`, `status`) 
                VALUES (:name, :description, :ram, :disk, :cpu, :ports, :databases, :backups, :location, :user, :nest, :egg, :status)');

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':ram', $ram);
            $stmt->bindParam(':disk', $disk);
            $stmt->bindParam(':cpu', $cpu);
            $stmt->bindParam(':ports', $ports);
            $stmt->bindParam(':databases', $databases);
            $stmt->bindParam(':backups', $backups);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':user', $user);
            $stmt->bindParam(':nest', $nest);
            $stmt->bindParam(':egg', $egg);
            $stmt->bindParam(':status', $status);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create server queue item: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update a server queue item's status.
     *
     * @param int $id The ID of the server queue item
     * @param string $status The new status (pending, building, failed)
     *
     * @return bool True on success, false on failure
     */
    public static function updateStatus(int $id, string $status): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET status = :status, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update server queue item status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a server queue item (soft delete).
     *
     * @param int $id The ID of the server queue item to delete
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET deleted = "true", updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete server queue item: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get server queue statistics.
     *
     * @return array The statistics
     */
    public static function getStats(): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "building" THEN 1 ELSE 0 END) as building,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
                FROM ' . self::getTableName() . ' 
                WHERE deleted = "false"');

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'pending' => 0,
                'building' => 0,
                'failed' => 0,
            ];
        } catch (\Exception $e) {
            self::db_Error('Failed to get server queue stats: ' . $e->getMessage());

            return [
                'total' => 0,
                'pending' => 0,
                'building' => 0,
                'failed' => 0,
            ];
        }
    }

    /**
     * Check if a server queue item exists.
     *
     * @param int $id The ID of the server queue item
     *
     * @return bool True if the item exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if server queue item exists: ' . $e->getMessage());

            return false;
        }
    }
}
