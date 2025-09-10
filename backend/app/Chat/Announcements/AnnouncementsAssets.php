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
