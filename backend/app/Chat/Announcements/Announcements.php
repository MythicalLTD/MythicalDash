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

class Announcements extends Database
{
    public const TABLE_NAME = 'mythicaldash_announcements';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Create a new announcement.
     */
    public static function create(string $title, string $shortDescription, string $description): int
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (title, shortDescription, description) VALUES (:title, :shortDescription, :description)';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':shortDescription', $shortDescription);
            $stmt->bindParam(':description', $description);
            $stmt->execute();

            return $con->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create announcement: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Update an existing announcement.
     */
    public static function update(int $id, string $title, string $shortDescription, string $description): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'UPDATE ' . self::TABLE_NAME . ' SET title = :title, shortDescription = :shortDescription, description = :description WHERE id = :id';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':shortDescription', $shortDescription);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update announcement: ' . $e->getMessage());
        }
    }

    /**
     * Delete an announcement.
     */
    public static function delete(int $id): void
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'UPDATE ' . self::TABLE_NAME . ' SET deleted = "true" WHERE id = :id';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete announcement: ' . $e->getMessage());
        }
    }

    /**
     * Get an announcement by ID.
     */
    public static function get(int $id)
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (\Exception $e) {
            self::db_Error('Failed to get announcement: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all announcements.
     */
    public static function getAll(): array
    {
        try {
            $announcements = Announcements::getAllSortedBy('date');

            if (empty($announcements)) {
                return [];
            }

            for ($i = 0; $i < count($announcements); ++$i) {
                $announcements[$i]['tags'] = AnnouncementsTags::getAll($announcements[$i]['id']);
                $announcements[$i]['assets'] = AnnouncementsAssets::getAll($announcements[$i]['id']);
            }

            return $announcements;

        } catch (\Exception $e) {
            self::db_Error('Failed to get all announcements: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all announcements sorted by a specific column.
     */
    public static function getAllSortedBy(string $column, string $order = 'DESC'): array
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE deleted = "false" ORDER BY ' . $column . ' ' . $order;
            $stmt = $con->query($sql);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error("Failed to get all announcements sorted by $column: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if an announcement exists.
     *
     * @param int $id The id of the announcement
     *
     * @return bool True if the announcement exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $con = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false"';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if announcement exists: ' . $e->getMessage());

            return false;
        }
    }

    public static function existsTag(int $id, string $tag): bool
    {
        try {
            $con = self::getPdoConnection();

            $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = "false" AND tag = :tag';
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':tag', $tag, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if announcement tag exists: ' . $e->getMessage());

            return false;
        }
    }
}
