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

namespace MythicalDash\Chat\Announcements;

use MythicalDash\Chat\Database;

class AnnouncementsAssets extends Database
{
    public const TABLE_NAME = 'mythicaldash_announcements_assets';

    /**
     * Create a new announcement asset.
     *
     * @param int $announcementId The id of the announcement
     * @param string $images The images of the announcement
     *
     * @return int The id of the announcement asset
     */
    public static function create(int $announcementId, string $images): int
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (announcements, images) VALUES (:announcementId, :images)';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':announcementId', $announcementId);
            $stmt->bindParam(':images', $images);
            $stmt->execute();

            return $con->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create announcement asset: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Delete an announcement asset.
     *
     * @param int $id The id of the announcement asset
     */
    public static function delete(int $id): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'UPDATE ' . self::TABLE_NAME . ' SET deleted = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete announcement asset: ' . $e->getMessage());
        }
    }

    /**
     * Get all announcement assets.
     *
     * @param int $id The id of the announcement
     *
     * @return array The announcement assets
     */
    public static function getAll(int $id): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE announcements = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all announcement assets: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if an announcement asset exists.
     *
     * @param int $id The id of the announcement asset
     *
     * @return bool True if the announcement asset exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if announcement asset exists: ' . $e->getMessage());

            return false;
        }
    }
}
