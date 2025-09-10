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

namespace MythicalDash\Chat\J4RServers;

use MythicalDash\Chat\Database;

class J4RServers extends Database
{
    public const TABLE_NAME = 'mythicaldash_j4r_servers';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get list of all non-deleted J4R servers.
     *
     * @return array List of J4R servers
     */
    public static function getList(): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get J4R server list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get list of all non-deleted and non-locked J4R servers.
     *
     * @return array List of available J4R servers
     */
    public static function getAvailableList(): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false" AND locked = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get available J4R server list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new J4R server.
     *
     * @param string $name The server name
     * @param string $inviteCode The Discord invite code
     * @param int $coins The coins reward amount
     * @param string|null $serverId The Discord server ID for verification
     * @param string|null $description The server description
     * @param string|null $iconUrl The server icon URL
     *
     * @return int|false The ID of the newly created server, or false on failure
     */
    public static function create(
        string $name,
        string $inviteCode,
        int $coins,
        ?string $serverId = null,
        ?string $description = null,
        ?string $iconUrl = null,
    ): int|false {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::getTableName() . ' 
                (name, invite_code, server_id, description, icon_url, coins) 
                VALUES (:name, :invite_code, :server_id, :description, :icon_url, :coins)';

            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':invite_code', $inviteCode);
            $stmt->bindParam(':server_id', $serverId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':icon_url', $iconUrl);
            $stmt->bindParam(':coins', $coins);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create J4R server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update an existing J4R server.
     *
     * @param int $id The server ID
     * @param string $name The server name
     * @param string $inviteCode The Discord invite code
     * @param int $coins The coins reward amount
     * @param string|null $serverId The Discord server ID for verification
     * @param string|null $description The server description
     * @param string|null $iconUrl The server icon URL
     *
     * @return bool True on success, false on failure
     */
    public static function update(
        int $id,
        string $name,
        string $inviteCode,
        int $coins,
        ?string $serverId = null,
        ?string $description = null,
        ?string $iconUrl = null,
    ): bool {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' 
                SET name = :name, invite_code = :invite_code, server_id = :server_id, 
                    description = :description, icon_url = :icon_url, coins = :coins 
                WHERE id = :id AND deleted = "false"';

            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':invite_code', $inviteCode);
            $stmt->bindParam(':server_id', $serverId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':icon_url', $iconUrl);
            $stmt->bindParam(':coins', $coins);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update J4R server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a J4R server (soft delete).
     *
     * @param int $id The server ID
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete J4R server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a J4R server by ID.
     *
     * @param int $id The server ID
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

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get J4R server by ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get a J4R server by invite code.
     *
     * @param string $inviteCode The Discord invite code
     *
     * @return array|null The server data or null if not found
     */
    public static function getByInviteCode(string $inviteCode): ?array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE invite_code = :invite_code AND deleted = "false" AND locked = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':invite_code', $inviteCode);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get J4R server by invite code: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if a J4R server exists by ID.
     *
     * @param int $id The server ID
     *
     * @return bool True if exists, false otherwise
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
            self::db_Error('Failed to check if J4R server exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a J4R server exists by invite code.
     *
     * @param string $inviteCode The Discord invite code
     *
     * @return bool True if exists, false otherwise
     */
    public static function existsByInviteCode(string $inviteCode): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE invite_code = :invite_code AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':invite_code', $inviteCode);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to check if J4R server exists by invite code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a J4R server by Discord server ID.
     *
     * @param string $serverId The Discord server ID
     *
     * @return array|null The server data or null if not found
     */
    public static function getByServerId(string $serverId): ?array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' 
                WHERE server_id = :server_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':server_id', $serverId);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get J4R server by server ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if a J4R server exists by Discord server ID.
     *
     * @param string $serverId The Discord server ID to check
     *
     * @return bool True if exists, false otherwise
     */
    public static function existsByServerId(string $serverId): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' 
                WHERE server_id = :server_id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':server_id', $serverId);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to check if J4R server exists by server ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Lock a J4R server.
     *
     * @param int $id The server ID
     *
     * @return bool True on success, false on failure
     */
    public static function lock(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET locked = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to lock J4R server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Unlock a J4R server.
     *
     * @param int $id The server ID
     *
     * @return bool True on success, false on failure
     */
    public static function unlock(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET locked = "false" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to unlock J4R server: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the total count of J4R servers.
     *
     * @return int The total count
     */
    public static function getCount(): int
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get J4R server count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get the count of available (non-locked) J4R servers.
     *
     * @return int The available count
     */
    public static function getAvailableCount(): int
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE deleted = "false" AND locked = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get available J4R server count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get J4R servers sorted by a specific column.
     *
     * @param string $column The column to sort by
     * @param string $order The sort order (ASC or DESC)
     *
     * @return array The sorted servers
     */
    public static function getAllSortedBy(string $column, string $order = 'DESC'): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false" ORDER BY ' . $column . ' ' . $order;
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get sorted J4R servers: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get J4R servers with pagination.
     *
     * @param int $limit The number of servers to return
     * @param int $offset The offset
     *
     * @return array The paginated servers
     */
    public static function getPaginated(int $limit, int $offset = 0): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false" ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get paginated J4R servers: ' . $e->getMessage());

            return [];
        }
    }
}
