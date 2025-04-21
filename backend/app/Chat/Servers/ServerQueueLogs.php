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

class ServerQueueLogs extends Database
{
    public const TABLE_NAME = 'mythicaldash_servers_queue_logs';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get all server queue logs.
     *
     * @return array The list of server queue logs
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
            self::db_Error('Failed to get server queue logs: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get server queue logs by build ID.
     *
     * @param int $buildId The build ID
     *
     * @return array The list of logs for the specified build
     */
    public static function getByBuild(int $buildId): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' WHERE build = :build AND deleted = "false"';
            $stmt = $dbConn->prepare($query);
            $stmt->bindParam(':build', $buildId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get server queue logs by build: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a specific log entry by ID.
     *
     * @param int $id The ID of the log entry
     *
     * @return array|null The log entry or null if not found
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
            self::db_Error('Failed to get server queue log: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create a new server queue log entry.
     *
     * @param int $buildId The ID of the build
     * @param string $log The log content
     *
     * @return int|false The ID of the newly created log entry, or false on failure
     */
    public static function create(
        int $buildId,
        string $log,
    ): int|false {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' 
                (`build`, `log`) 
                VALUES (:build, :log)');

            $stmt->bindParam(':build', $buildId);
            $stmt->bindParam(':log', $log);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create server queue log: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update a server queue log entry.
     *
     * @param int $id The ID of the log entry
     * @param string $log The new log content
     *
     * @return bool True on success, false on failure
     */
    public static function update(int $id, string $log): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET log = :log, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':log', $log);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update server queue log: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a server queue log entry (soft delete).
     *
     * @param int $id The ID of the log entry to delete
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
            self::db_Error('Failed to delete server queue log: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Set log entry locked status.
     *
     * @param int $id The ID of the log entry
     * @param bool $locked Whether to lock or unlock the entry
     *
     * @return bool True on success, false on failure
     */
    public static function setLocked(int $id, bool $locked): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $lockedValue = $locked ? 'true' : 'false';

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET locked = :locked, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':locked', $lockedValue);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to set server queue log locked status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Set log entry purge status.
     *
     * @param int $id The ID of the log entry
     * @param bool $purge Whether to mark for purging
     *
     * @return bool True on success, false on failure
     */
    public static function setPurge(int $id, bool $purge): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $purgeValue = $purge ? 'true' : 'false';

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET purge = :purge, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':purge', $purgeValue);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to set server queue log purge status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update log entry expiration date.
     *
     * @param int $id The ID of the log entry
     * @param string $expiresAt The new expiration date in MySQL datetime format
     *
     * @return bool True on success, false on failure
     */
    public static function updateExpiration(int $id, string $expiresAt): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET expires_at = :expires_at, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':expires_at', $expiresAt);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update server queue log expiration: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all expired logs that should be purged.
     *
     * @return array The list of expired log entries
     */
    public static function getExpired(): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' 
                WHERE deleted = "false" 
                AND purge = "true" 
                AND expires_at < NOW()';

            $stmt = $dbConn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get expired server queue logs: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if a log entry exists.
     *
     * @param int $id The ID of the log entry
     *
     * @return bool True if exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';

            $stmt = $dbConn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if server queue log exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Save logs from a server job.
     *
     * @param int $buildId The ID of the build
     * @param array $logs Array of log messages
     * @param bool $isPurge Whether to mark for purging
     * @param string|null $expirationDate Custom expiration date (null for default)
     *
     * @return int|false The ID of the newly created log entry, or false on failure
     */
    public static function saveJobLogs(int $buildId, array $logs, bool $isPurge = false, ?string $expirationDate = null): int|false
    {
        try {
            // Join log messages with newlines
            $logText = !empty($logs) ? implode("\n", $logs) : '';

            // Create the log entry
            $logId = self::create($buildId, $logText);

            if ($logId !== false) {
                // Set purge status if needed
                if ($isPurge) {
                    self::setPurge($logId, true);
                }

                // Set custom expiration date if provided
                if ($expirationDate !== null) {
                    self::updateExpiration($logId, $expirationDate);
                }
            }

            return $logId;
        } catch (\Exception $e) {
            self::db_Error('Failed to save job logs: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Append new log entries to an existing log.
     *
     * @param int $id The ID of the log entry
     * @param array|string $newLogs Array of log messages or a single string
     *
     * @return bool True on success, false on failure
     */
    public static function appendLogs(int $id, $newLogs): bool
    {
        try {
            // Get existing log
            $existingLog = self::getById($id);

            if ($existingLog === null) {
                return false;
            }

            // Format new logs
            if (is_array($newLogs)) {
                $newLogsText = implode("\n", $newLogs);
            } else {
                $newLogsText = (string) $newLogs;
            }

            // Combine existing and new logs
            $combinedLogs = $existingLog['log'] . "\n" . $newLogsText;

            // Update the log entry
            return self::update($id, $combinedLogs);
        } catch (\Exception $e) {
            self::db_Error('Failed to append logs: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Create a log entry for a failed server operation.
     *
     * @param int $buildId The ID of the build
     * @param array $logs Array of log messages
     * @param string $errorMessage The error message
     *
     * @return int|false The ID of the newly created log entry, or false on failure
     */
    public static function logFailure(int $buildId, array $logs, string $errorMessage): int|false
    {
        try {
            // Add error message to logs
            $logs[] = 'ERROR: ' . $errorMessage;

            // Save logs with default 30-day expiration
            $expirationDate = date('Y-m-d H:i:s', strtotime('+30 days'));

            return self::saveJobLogs($buildId, $logs, true, $expirationDate);
        } catch (\Exception $e) {
            self::db_Error('Failed to log failure: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the latest log for a build.
     *
     * @param int $buildId The ID of the build
     *
     * @return array|null The latest log entry or null if not found
     */
    public static function getLatestByBuild(int $buildId): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $query = 'SELECT * FROM ' . self::getTableName() . ' 
                WHERE build = :build AND deleted = "false" 
                ORDER BY created_at DESC LIMIT 1';

            $stmt = $dbConn->prepare($query);
            $stmt->bindParam(':build', $buildId);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get latest log for build: ' . $e->getMessage());

            return null;
        }
    }
}
