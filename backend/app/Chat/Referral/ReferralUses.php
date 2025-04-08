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

class ReferralUses extends Database
{
    public const TABLE_NAME = 'mythicaldash_referral_uses';

    /**
     * Get the table name for referral uses.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get list of all non-deleted referral uses.
     *
     * @return array List of referral uses
     */
    public static function getList(): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get list of referral uses: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get list of all non-deleted referral codes by user.
     *
     * @return array List of referral codes
     */
    public static function getListByReferralCode(int $referralCodeId): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE referral_code_id = :referral_code_id AND deleted = "false"');
            $stmt->bindParam(':referral_code_id', $referralCodeId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get list of referral codes by user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new referral use.
     *
     * @param int $referralCodeId The ID of the referral code
     * @param string $referredUserId The UUID of the referred user
     *
     * @return int|false The ID of newly created use, or false on failure
     */
    public static function create(int $referralCodeId, string $referredUserId): int|false
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' (referral_code_id, referred_user_id) VALUES (:referral_code_id, :referred_user_id)');
            $stmt->bindParam(':referral_code_id', $referralCodeId, \PDO::PARAM_INT);
            $stmt->bindParam(':referred_user_id', $referredUserId);
            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create referral use: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a referral use (soft delete).
     *
     * @param int $id The ID of the referral use
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
            self::db_Error('Failed to delete referral use: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a referral use by ID.
     *
     * @param int $id The ID of the referral use
     *
     * @return array|false The referral use data or false if not found
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
            self::db_Error('Failed to get referral use by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get referral uses by referral code ID.
     *
     * @param int $referralCodeId The ID of the referral code
     *
     * @return array List of referral uses
     */
    public static function getByReferralCodeId(int $referralCodeId): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE referral_code_id = :referral_code_id AND deleted = "false"');
            $stmt->bindParam(':referral_code_id', $referralCodeId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get referral uses by referral code ID: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get referral uses by referred user ID.
     *
     * @param string $referredUserId The UUID of the referred user
     *
     * @return array List of referral uses
     */
    public static function getByReferredUserId(string $referredUserId): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE referred_user_id = :referred_user_id AND deleted = "false"');
            $stmt->bindParam(':referred_user_id', $referredUserId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get referral uses by referred user ID: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if a user has already used a referral code.
     *
     * @param string $referredUserId The UUID of the referred user
     * @param int $referralCodeId The ID of the referral code
     *
     * @return bool True if the user has used the code, false otherwise
     */
    public static function hasUserUsedCode(string $referredUserId, int $referralCodeId): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE referred_user_id = :referred_user_id AND referral_code_id = :referral_code_id AND deleted = "false"');
            $stmt->bindParam(':referred_user_id', $referredUserId);
            $stmt->bindParam(':referral_code_id', $referralCodeId, \PDO::PARAM_INT);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to check if user has used referral code: ' . $e->getMessage());

            return false;
        }
    }
}
