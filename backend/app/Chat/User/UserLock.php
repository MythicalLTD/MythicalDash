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

namespace MythicalDash\Chat\User;

use MythicalDash\Chat\Database;

class UserLock
{
    private const TABLE_NAME = 'mythicaldash_users';
    private const LOCK_TIMEOUT = 30; // 30 seconds lock timeout

    /**
     * Attempts to acquire a lock for a user by UUID.
     * This prevents race conditions in credit operations.
     *
     * @param string $userUuid The user's UUID
     *
     * @return bool True if lock was acquired, false otherwise
     */
    public static function acquireLock(string $userUuid): bool
    {
        try {
            $pdo = Database::getPdoConnection();

            // Start transaction
            $pdo->beginTransaction();

            // Check if user is already locked
            $stmt = $pdo->prepare('SELECT locked, last_seen FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid AND deleted = "false"');
            $stmt->execute([':uuid' => $userUuid]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();

                return false;
            }

            // Check if user is locked and if lock has expired
            if ($user['locked'] === 'true') {
                $lastSeen = new \DateTime($user['last_seen']);
                $now = new \DateTime();
                $timeDiff = $now->getTimestamp() - $lastSeen->getTimestamp();

                // If lock is older than timeout, consider it expired
                if ($timeDiff > self::LOCK_TIMEOUT) {
                    // Force unlock expired lock
                    $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET locked = "false" WHERE uuid = :uuid');
                    $stmt->execute([':uuid' => $userUuid]);
                } else {
                    $pdo->rollBack();

                    return false; // User is still locked
                }
            }

            // Acquire lock
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET locked = "true", last_seen = NOW() WHERE uuid = :uuid');
            $stmt->execute([':uuid' => $userUuid]);

            // Commit transaction
            $pdo->commit();

            return true;
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Database::db_Error('Failed to acquire user lock: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Releases a lock for a user by UUID.
     *
     * @param string $userUuid The user's UUID
     *
     * @return bool True if lock was released, false otherwise
     */
    public static function releaseLock(string $userUuid): bool
    {
        try {
            $pdo = Database::getPdoConnection();

            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET locked = "false" WHERE uuid = :uuid AND deleted = "false"');
            $result = $stmt->execute([':uuid' => $userUuid]);

            return $result;
        } catch (\Exception $e) {
            Database::db_Error('Failed to release user lock: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Checks if a user is currently locked.
     *
     * @param string $userUuid The user's UUID
     *
     * @return bool True if user is locked, false otherwise
     */
    public static function isLocked(string $userUuid): bool
    {
        try {
            $pdo = Database::getPdoConnection();

            $stmt = $pdo->prepare('SELECT locked, last_seen FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid AND deleted = "false"');
            $stmt->execute([':uuid' => $userUuid]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // Check if lock has expired
            if ($user['locked'] === 'true') {
                $lastSeen = new \DateTime($user['last_seen']);
                $now = new \DateTime();
                $timeDiff = $now->getTimestamp() - $lastSeen->getTimestamp();

                // If lock is older than timeout, consider it expired
                if ($timeDiff > self::LOCK_TIMEOUT) {
                    // Auto-release expired lock
                    self::releaseLock($userUuid);

                    return false;
                }

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check user lock status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Executes a callback function with user lock protection.
     * This ensures that only one operation can be performed on a user at a time.
     *
     * @param string $userUuid The user's UUID
     * @param callable $callback The function to execute
     *
     * @throws \Exception If lock cannot be acquired or callback fails
     *
     * @return mixed The result of the callback function
     */
    public static function executeWithLock(string $userUuid, callable $callback)
    {
        $lockAcquired = false;

        try {
            // Try to acquire lock with retry mechanism
            $maxRetries = 3;
            $retryDelay = 100000; // 100ms in microseconds

            for ($i = 0; $i < $maxRetries; ++$i) {
                if (self::acquireLock($userUuid)) {
                    $lockAcquired = true;
                    break;
                }

                if ($i < $maxRetries - 1) {
                    usleep($retryDelay);
                    $retryDelay *= 2; // Exponential backoff
                }
            }

            if (!$lockAcquired) {
                throw new \Exception('Failed to acquire user lock after ' . $maxRetries . ' attempts');
            }

            // Execute the callback function
            $result = $callback();

            return $result;
        } finally {
            // Always release the lock
            if ($lockAcquired) {
                self::releaseLock($userUuid);
            }
        }
    }
}
