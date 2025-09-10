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

namespace MythicalDash\Chat\IPRelationships;

use MythicalDash\Chat\Database;

class IPRelationship
{
    private const TABLE_NAME = 'mythicaldash_ip_relationship';

    /**
     * Create a new IP relationship record.
     *
     * @param string $user The UUID of the user
     * @param string $ip The IP address
     *
     * @return int The ID of the newly created record
     */
    public static function create(string $user, string $ip): int
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('INSERT INTO ' . self::TABLE_NAME . ' (user, ip) VALUES (?, ?)');
            $stmt->execute([$user, $ip]);

            return (int) $pdo->lastInsertId();
        } catch (\Exception $e) {
            Database::db_Error('Failed to create IP relationship: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get an IP relationship by ID.
     *
     * @param int $id The relationship ID
     *
     * @return array|null The relationship data or null if not found
     */
    public static function get(int $id): ?array
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ? AND deleted = "false"');
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get IP relationship: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all IP relationships for a user.
     *
     * @param string $user The UUID of the user
     * @param bool $includeDeleted Whether to include deleted relationships
     *
     * @return array Array of IP relationships
     */
    public static function getByUser(string $user, bool $includeDeleted = false): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE user = ?';
            if (!$includeDeleted) {
                $sql .= ' AND deleted = "false"';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get IP relationships for user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all IP relationships for an IP address.
     *
     * @param string $ip The IP address
     * @param bool $includeDeleted Whether to include deleted relationships
     *
     * @return array Array of IP relationships
     */
    public static function getByIP(string $ip, bool $includeDeleted = false): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ip = ?';
            if (!$includeDeleted) {
                $sql .= ' AND deleted = "false"';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ip]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get IP relationships for IP: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get an IP relationship by IP and not user.
     *
     * @param string $ip The IP address
     * @param string $user The UUID of the user
     *
     * @return array|null The relationship data or null if not found
     */
    public static function getByIpAndNotUser(string $ip, string $user): ?array
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE ip = ? AND user != ? AND deleted = "false"');
            $stmt->execute([$ip, $user]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get IP relationships for IP: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Update an IP relationship.
     *
     * @param int $id The relationship ID
     * @param string $ip The new IP address
     *
     * @return bool Whether the update was successful
     */
    public static function update(int $id, string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET ip = ? WHERE id = ? AND deleted = "false"');

            return $stmt->execute([$ip, $id]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to update IP relationship: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete an IP relationship (soft delete).
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the deletion was successful
     */
    public static function delete(int $id): bool
    {
        try {
            Database::markRecordAsDeleted(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to delete IP relationship: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Restore a deleted IP relationship.
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the restoration was successful
     */
    public static function restore(int $id): bool
    {
        try {
            Database::restoreRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to restore IP relationship: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Lock an IP relationship.
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the locking was successful
     */
    public static function lock(int $id): bool
    {
        try {
            Database::lockRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to lock IP relationship: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Unlock an IP relationship.
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the unlocking was successful
     */
    public static function unlock(int $id): bool
    {
        try {
            Database::unlockRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to unlock IP relationship: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if an IP relationship is locked.
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the relationship is locked
     */
    public static function isLocked(int $id): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT locked FROM ' . self::TABLE_NAME . ' WHERE id = ? AND deleted = "false"');
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result && $result['locked'] === 'true';
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if IP relationship is locked: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if an IP relationship exists.
     *
     * @param int $id The relationship ID
     *
     * @return bool Whether the relationship exists
     */
    public static function exists(int $id): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE id = ? AND deleted = "false"');
            $stmt->execute([$id]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if IP relationship exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a user has a relationship with an IP.
     *
     * @param string $user The UUID of the user
     * @param string $ip The IP address
     *
     * @return bool Whether the relationship exists
     */
    public static function hasRelationship(string $user, string $ip): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE user = ? AND ip = ? AND deleted = "false"');
            $stmt->execute([$user, $ip]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if IP relationship exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all users that share an IP with the given user.
     *
     * @param string $user The UUID of the user to check
     * @param bool $includeDeleted Whether to include deleted relationships
     *
     * @return array Array of user UUIDs that share an IP with the given user
     */
    public static function getSharedIPUsers(string $user, bool $includeDeleted = false): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT DISTINCT r2.user 
                   FROM ' . self::TABLE_NAME . ' r1 
                   JOIN ' . self::TABLE_NAME . ' r2 ON r1.ip = r2.ip 
                   WHERE r1.user = ? AND r2.user != ?';

            if (!$includeDeleted) {
                $sql .= ' AND r1.deleted = "false" AND r2.deleted = "false"';
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user, $user]);

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get shared IP users: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if a user has multiple accounts (shares IPs with other users).
     *
     * @param string $user The UUID of the user to check
     *
     * @return bool Whether the user has multiple accounts
     */
    public static function hasMultipleAccounts(string $user): bool
    {
        return count(self::getSharedIPUsers($user)) > 0;
    }

    /**
     * Get all IPs shared between multiple users.
     *
     * @param string $user The UUID of the user to check
     *
     * @return array Array of IPs that are shared with other users
     */
    public static function getSharedIPs(string $user): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT DISTINCT r1.ip 
                   FROM ' . self::TABLE_NAME . ' r1 
                   JOIN ' . self::TABLE_NAME . ' r2 ON r1.ip = r2.ip 
                   WHERE r1.user = ? AND r2.user != ? AND r1.deleted = "false" AND r2.deleted = "false"';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user, $user]);

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get shared IPs: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Process multiple accounts for a user.
     * This method will:
     * 1. Get all users sharing IPs with the given user
     * 2. Get all shared IPs
     * 3. Return detailed information about the relationships.
     *
     * @param string $user The UUID of the user to process
     *
     * @return array Array containing:
     *               - 'has_multiple_accounts' => bool
     *               - 'shared_users' => array of user UUIDs
     *               - 'shared_ips' => array of shared IPs
     *               - 'relationships' => array of detailed relationship data
     */
    public static function processMultipleAccounts(string $user): array
    {
        $sharedUsers = self::getSharedIPUsers($user);
        $sharedIPs = self::getSharedIPs($user);

        // Use an associative array to deduplicate users and their relationships
        $uniqueRelationships = [];
        foreach ($sharedIPs as $ip) {
            $ipUsers = self::getByIP($ip);
            foreach ($ipUsers as $relationship) {
                if ($relationship['user'] !== $user) {
                    // Use user UUID as key to ensure uniqueness
                    $uniqueRelationships[$relationship['user']] = [
                        'ip' => $ip,
                        'user' => $relationship['user'],
                        'created_at' => $relationship['created_at'],
                        'locked' => $relationship['locked'] === 'true',
                    ];
                }
            }
        }

        // Convert back to indexed array
        $relationships = array_values($uniqueRelationships);

        return [
            'has_multiple_accounts' => count($sharedUsers) > 0,
            'shared_users' => array_values(array_unique($sharedUsers)), // Ensure unique users
            'shared_ips' => array_values(array_unique($sharedIPs)), // Ensure unique IPs
            'relationships' => $relationships,
        ];
    }
}
