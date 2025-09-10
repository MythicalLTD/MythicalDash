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

namespace MythicalDash\Chat\ProxyList;

use MythicalDash\Chat\Database;

class ProxyList
{
    private const TABLE_NAME = 'mythicaldash_proxylist';

    /**
     * Check if an IP is in the proxy list.
     *
     * @param string $ip The IP address to check
     *
     * @return bool Whether the IP is in the proxy list
     */
    public static function exists(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE ip = ? AND deleted = "false"');
            $stmt->execute([$ip]);

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if proxy exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Add a proxy to the list.
     *
     * @param string $ip The IP address to add
     *
     * @return bool Whether the addition was successful
     */
    public static function create(string $ip): bool
    {
        try {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return false;
            }

            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('INSERT INTO ' . self::TABLE_NAME . ' (ip) VALUES (?)');

            return $stmt->execute([$ip]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to add proxy: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all active proxies.
     *
     * @param bool $includeDeleted Whether to include deleted proxies
     *
     * @return array Array of proxies
     */
    public static function getAll(bool $includeDeleted = false): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME;
            if (!$includeDeleted) {
                $sql .= ' WHERE deleted = "false"';
            }
            $stmt = $pdo->query($sql);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get all proxies: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get proxy count.
     *
     * @param bool $includeDeleted Whether to include deleted proxies
     *
     * @return int The number of proxies
     */
    public static function getCount(bool $includeDeleted = false): int
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME;
            if (!$includeDeleted) {
                $sql .= ' WHERE deleted = "false"';
            }
            $stmt = $pdo->query($sql);

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to get proxy count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Delete a proxy (soft delete).
     *
     * @param string $ip The IP address to delete
     *
     * @return bool Whether the deletion was successful
     */
    public static function delete(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET deleted = "true" WHERE ip = ?');

            return $stmt->execute([$ip]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to delete proxy: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Restore a deleted proxy.
     *
     * @param string $ip The IP address to restore
     *
     * @return bool Whether the restoration was successful
     */
    public static function restore(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET deleted = "false" WHERE ip = ?');

            return $stmt->execute([$ip]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to restore proxy: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Lock a proxy.
     *
     * @param string $ip The IP address to lock
     *
     * @return bool Whether the locking was successful
     */
    public static function lock(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET locked = "true" WHERE ip = ?');

            return $stmt->execute([$ip]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to lock proxy: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Unlock a proxy.
     *
     * @param string $ip The IP address to unlock
     *
     * @return bool Whether the unlocking was successful
     */
    public static function unlock(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET locked = "false" WHERE ip = ?');

            return $stmt->execute([$ip]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to unlock proxy: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a proxy is locked.
     *
     * @param string $ip The IP address to check
     *
     * @return bool Whether the proxy is locked
     */
    public static function isLocked(string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT locked FROM ' . self::TABLE_NAME . ' WHERE ip = ?');
            $stmt->execute([$ip]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result && $result['locked'] === 'true';
        } catch (\Exception $e) {
            Database::db_Error('Failed to check proxy lock status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get proxy details.
     *
     * @param string $ip The IP address to get details for
     *
     * @return array|null The proxy details or null if not found
     */
    public static function get(string $ip): ?array
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE ip = ?');
            $stmt->execute([$ip]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get proxy details: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Clean up old deleted proxies (older than 30 days).
     *
     * @return int Number of proxies cleaned up
     */
    public static function cleanup(): int
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('DELETE FROM ' . self::TABLE_NAME . ' WHERE deleted = "true" AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Database::db_Error('Failed to cleanup old proxies: ' . $e->getMessage());

            return 0;
        }
    }
}
