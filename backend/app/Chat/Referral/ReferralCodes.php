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

namespace MythicalDash\Chat\Referral;

use MythicalDash\Chat\Database;

class ReferralCodes extends Database
{
    public const TABLE_NAME = 'mythicaldash_referral_codes';

    /**
     * Get the table name for referral codes.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get list of all non-deleted referral codes.
     *
     * @return array List of referral codes
     */
    public static function getList(): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get list of referral codes: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new referral code.
     *
     * @param string $user The user UUID
     * @param string $code The referral code
     *
     * @return int|false The ID of newly created code, or false on failure
     */
    public static function create(string $user, string $code): int|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' (user, code) VALUES (:user, :code)');
            $stmt->bindParam(':user', $user);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create referral code:  ("' . $user . '", "' . $code . '")' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update a referral code.
     *
     * @param int $id The ID of the referral code
     * @param string $code The new code
     *
     * @return bool True if update successful, false otherwise
     */
    public static function update(int $id, string $code): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET code = :code WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':code', $code);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update referral code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a referral code (soft delete).
     *
     * @param int $id The ID of the referral code
     *
     * @return bool True if deletion successful, false otherwise
     */
    public static function delete(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete referral code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a referral code by ID.
     *
     * @param int $id The ID of the referral code
     *
     * @return array|false The referral code data or false if not found
     */
    public static function getById(int $id): array|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get referral code by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a referral code by code.
     *
     * @param string $code The referral code
     *
     * @return array|false The referral code data or false if not found
     */
    public static function getByCode(string $code): array|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE code = :code AND deleted = "false"');
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get referral code by code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get referral codes by user.
     *
     * @param string $user The user UUID
     *
     * @return array List of referral codes
     */
    public static function getByUser(string $user): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE user = :user AND deleted = "false"');
            $stmt->bindParam(':user', $user);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get referral codes by user: ' . $e->getMessage());

            return [];
        }
    }
}
