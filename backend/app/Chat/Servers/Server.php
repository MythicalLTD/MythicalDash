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

class Server extends Database
{
    public const TABLE_NAME = 'mythicaldash_servers';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get list of all non-deleted servers.
     *
     * @return array List of servers
     */
    public static function getList(): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get server list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new server.
     *
     * @param int $pterodactylId The Pterodactyl server ID
     * @param int|null $build The build ID
     * @param string $user The user UUID
     *
     * @return int|false The ID of the newly created server, or false on failure
     */
    public static function create(
        int $pterodactylId,
        ?int $build,
        string $user,
    ): int|false {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::getTableName() . ' 
                (pterodactyl_id, build, user) 
					VALUES (:pterodactyl_id, :build, :user)';

            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':pterodactyl_id', $pterodactylId);
            $stmt->bindParam(':build', $build);
            $stmt->bindParam(':user', $user);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the expiration date of a server.
     *
     * @param int $id The ID of the server
     *
     * @return string The expiration date
     */
    public static function getExpirationDate(int $id): string
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT expires_at FROM ' . self::getTableName() . ' WHERE pterodactyl_id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchColumn() ?? '';
        } catch (\Exception $e) {
            self::db_Error('Failed to get expiration date: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Get the expiration timestamp of a server.
     *
     * @param int $id The ID of the server
     *
     * @return int|null The expiration timestamp or null if not found
     */
    public static function getExpirationTimestamp(int $id): ?int
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT UNIX_TIMESTAMP(expires_at) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $timestamp = $stmt->fetchColumn();

            return $timestamp === false ? null : (int) $timestamp;
        } catch (\Exception $e) {
            self::db_Error('Failed to get expiration timestamp: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Update an existing server.
     *
     * @param int $id The ID of the server to update
     * @param string $expiresAt The expiration date
     * @param string $purge Whether the server is marked for purge ("true" or "false")
     *
     * @return bool True on success, false on failure
     */
    public static function update(
        int $id,
        int $expiresAt,
        string $purge = 'false',
    ): bool {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET 
                expires_at = FROM_UNIXTIME(:expires_at),
                `purge` = :purge 
                WHERE id = :id AND deleted = "false"';

            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':purge', $purge, \PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a server (soft delete).
     *
     * @param int $id The ID of the server to delete
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a server exists by ID.
     *
     * @param int $id The ID of the server to check
     *
     * @return bool True if the server exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to check if server exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get server by ID.
     *
     * @param int $id The ID of the server to get
     *
     * @return array|null The server data or null if not found
     */
    public static function getById(int $id): ?array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get server by ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get servers by user UUID.
     *
     * @param string $user The user UUID
     *
     * @return array List of servers
     */
    public static function getByUser(string $user): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE user = :user AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':user', $user);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get servers by user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get server by Pterodactyl ID.
     *
     * @param int $pterodactylId The Pterodactyl server ID
     *
     * @return array|null The server data or null if not found
     */
    public static function getByPterodactylId(int $pterodactylId): ?array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE pterodactyl_id = :pterodactyl_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':pterodactyl_id', $pterodactylId);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get server by Pterodactyl ID: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if a server exists by Pterodactyl ID.
     *
     * @param int $pterodactylId The Pterodactyl server ID
     *
     * @return bool True if the server exists, false otherwise
     */
    public static function doesServerExistByPterodactylId(int $pterodactylId): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE pterodactyl_id = :pterodactyl_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':pterodactyl_id', $pterodactylId);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to check if server exists by Pterodactyl ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a server by Pterodactyl ID.
     *
     * @param int $pterodactylId The Pterodactyl server ID
     *
     * @return bool True on success, false on failure
     */
    public static function deleteServerByPterodactylId(int $pterodactylId): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'DELETE FROM ' . self::getTableName() . ' WHERE pterodactyl_id = :pterodactyl_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':pterodactyl_id', $pterodactylId);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete server by Pterodactyl ID: ' . $e->getMessage());

            return false;
        }
    }
}
