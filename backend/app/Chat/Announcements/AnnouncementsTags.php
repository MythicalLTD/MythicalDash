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

class AnnouncementsTags extends Database
{
    public const TABLE_NAME = 'mythicaldash_announcements_tags';

    /**
     * Create a new announcement tag.
     */
    public static function create(int $announcementId, string $tag): int
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (announcements, tag) VALUES (:announcementId, :tag)';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':announcementId', $announcementId);
            $stmt->bindParam(':tag', $tag);
            $stmt->execute();

            return $con->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create announcement tag: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Delete an announcement tag.
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
            self::db_Error('Failed to delete announcement tag: ' . $e->getMessage());
        }
    }

    /**
     * Get all announcement tags.
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
            self::db_Error('Failed to get all announcement tags: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if an announcement tag exists.
     *
     * @param int $id The id of the announcement tag
     *
     * @return bool True if the announcement tag exists, false otherwise
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
            self::db_Error('Failed to check if announcement tag exists: ' . $e->getMessage());

            return false;
        }
    }
}
