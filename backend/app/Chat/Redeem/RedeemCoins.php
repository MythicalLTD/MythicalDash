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

namespace MythicalDash\Chat\Redeem;

use MythicalDash\Chat\Database;

class RedeemCoins extends Database
{
    public const TABLE_NAME = 'mythicaldash_redeem_codes';

    /**
     * Get the table name for redeem codes.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get list of all non-deleted redeem codes.
     *
     * @return array List of redeem codes
     */
    public static function getList(): array
    {
        $dbConn = Database::getPdoConnection();
        $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new redeem code.
     *
     * @param string $code The redeem code
     * @param int $coins Number of coins for this code
     * @param int $uses Number of times code can be used
     * @param bool $enabled Whether code is enabled
     *
     * @return int|false The ID of newly created code, or false on failure
     */
    public static function create(string $code, int $coins, int $uses = 1, bool $enabled = false): int
    {
        $enabled = $enabled ? 'true' : 'false';
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' (code, coins, uses, enabled) VALUES (:code, :coins, :uses, :enabled)');
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':coins', $coins);
            $stmt->bindParam(':uses', $uses);
            $stmt->bindParam(':enabled', $enabled);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create redeem code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update an existing redeem code.
     *
     * @param int $id ID of code to update
     * @param string $code New redeem code
     * @param int $coins New number of coins
     * @param int $uses New number of uses
     * @param bool $enabled New enabled status
     *
     * @return bool True if update successful, false otherwise
     */
    public static function update(int $id, string $code, int $coins, int $uses = 1, bool $enabled = false): bool
    {
        $enabled = $enabled ? 'true' : 'false';
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET code = :code, coins = :coins, uses = :uses, enabled = :enabled WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':coins', $coins);
            $stmt->bindParam(':uses', $uses);
            $stmt->bindParam(':enabled', $enabled);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update redeem code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Remove usage from a redeem code.
     *
     * @param int $id ID of code to remove usage from
     *
     * @return bool True if removal successful, false otherwise
     */
    public static function removeUsage(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET uses = uses - 1 WHERE id = :id AND uses > 0');
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to remove usage from redeem code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Soft delete a redeem code.
     *
     * @param int $id ID of code to delete
     *
     * @return bool True if deletion successful, false otherwise
     */
    public static function delete(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id');
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete redeem code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a redeem code exists by ID.
     *
     * @param int $id ID to check
     *
     * @return bool True if code exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if redeem code exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a redeem code exists by code string.
     *
     * @param string $code Code to check
     *
     * @return bool True if code exists, false otherwise
     */
    public static function existsByCode(string $code): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE code = :code AND deleted = "false" AND enabled = "true"');
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if redeem code exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a redeem code by code string.
     *
     * @param string $code Code to get
     *
     * @return array|null Array containing code data, or null if not found
     */
    public static function getByCode(string $code): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE code = :code AND deleted = "false" AND enabled = "true"');
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get redeem code: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get a redeem code by ID.
     *
     * @param int $id ID of code to get
     *
     * @return array|null Array containing code data, or null if not found
     */
    public static function get(int $id): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"');
            $stmt->execute([$id]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get redeem code: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Redeem a code atomically with row-level locking to prevent race conditions.
     * This method checks if the code can be redeemed and decrements usage in a single transaction.
     *
     * @param string $code The redeem code to redeem
     * @param string $userUuid The user UUID redeeming the code
     * @return array|false Array with redemption data on success, false on failure
     */
    public static function redeemCodeAtomic(string $code, string $userUuid): array|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $dbConn->beginTransaction();

            // Use SELECT FOR UPDATE to lock the row and get current code data
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE code = :code AND deleted = "false" AND enabled = "true" FOR UPDATE');
            $stmt->execute([$code]);
            $codeData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$codeData) {
                $dbConn->rollBack();
                return false;
            }

            // Check if code has uses left
            if ((int) $codeData['uses'] <= 0) {
                $dbConn->rollBack();
                return false;
            }

            // Check if user has already redeemed this code
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . RedeemRedeems::getTableName() . ' WHERE code = :code AND user = :user AND deleted = "false" FOR UPDATE');
            $stmt->execute([$codeData['id'], $userUuid]);
            if ($stmt->fetchColumn() > 0) {
                $dbConn->rollBack();
                return false;
            }

            // Decrement usage count
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET uses = uses - 1 WHERE id = :id AND uses > 0');
            $stmt->execute([$codeData['id']]);

            // Create redemption record
            $stmt = $dbConn->prepare('INSERT INTO ' . RedeemRedeems::getTableName() . ' (user, code) VALUES (:user, :code)');
            $stmt->execute([$userUuid, $codeData['id']]);

            $dbConn->commit();

            return [
                'id' => $codeData['id'],
                'coins' => (int) $codeData['coins'],
                'uses_left' => (int) $codeData['uses'] - 1
            ];
        } catch (\Exception $e) {
            if (isset($dbConn)) {
                $dbConn->rollBack();
            }
            self::db_Error('Failed to redeem code atomically: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a code can be redeemed atomically with row-level locking.
     * This method prevents race conditions by locking the row during validation.
     *
     * @param string $code The redeem code to check
     * @param string $userUuid The user UUID to check against
     * @return array|false Array with validation data on success, false on failure
     */
    public static function validateCodeAtomic(string $code, string $userUuid): array|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $dbConn->beginTransaction();

            // Use SELECT FOR UPDATE to lock the row and get current code data
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE code = :code AND deleted = "false" AND enabled = "true" FOR UPDATE');
            $stmt->execute([$code]);
            $codeData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$codeData) {
                $dbConn->rollBack();
                return false;
            }

            // Check if code has uses left
            if ((int) $codeData['uses'] <= 0) {
                $dbConn->rollBack();
                return false;
            }

            // Check if user has already redeemed this code
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . RedeemRedeems::getTableName() . ' WHERE code = :code AND user = :user AND deleted = "false"');
            $stmt->execute([$codeData['id'], $userUuid]);
            $alreadyRedeemed = $stmt->fetchColumn() > 0;

            $dbConn->commit();

            return [
                'id' => $codeData['id'],
                'coins' => (int) $codeData['coins'],
                'uses_left' => (int) $codeData['uses'],
                'already_redeemed' => $alreadyRedeemed,
                'can_redeem' => !$alreadyRedeemed && (int) $codeData['uses'] > 0
            ];
        } catch (\Exception $e) {
            if (isset($dbConn)) {
                $dbConn->rollBack();
            }
            self::db_Error('Failed to validate code atomically: ' . $e->getMessage());
            return false;
        }
    }
}
