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

namespace MythicalDash\Chat\Images;

use MythicalDash\Chat\Database;

class Image
{
    private const TABLE_NAME = 'mythicaldash_image_db';

    /**
     * Create a new image record.
     *
     * @param string $name The name of the image
     * @param string $image The image data (base64 encoded)
     *
     * @return int The ID of the newly created image
     */
    public static function create(string $name, string $image): int
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('INSERT INTO ' . self::TABLE_NAME . ' (name, image) VALUES (?, ?)');
            $stmt->execute([$name, $image]);

            return (int) $pdo->lastInsertId();
        } catch (\Exception $e) {
            Database::db_Error('Failed to create image: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get an image by ID.
     *
     * @param int $id The image ID
     *
     * @return array|null The image data or null if not found
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
            Database::db_Error('Failed to get image: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all images.
     *
     * @param bool $includeDeleted Whether to include deleted images
     *
     * @return array Array of images
     */
    public static function getAll(bool $includeDeleted = false): array
    {
        try {
            $pdo = Database::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::TABLE_NAME;
            if (!$includeDeleted) {
                $sql .= ' WHERE deleted = "false"';
            }
            $stmt = $pdo->query($sql);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Database::db_Error('Failed to get all images: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Update an image.
     *
     * @param int $id The image ID
     * @param string $name The new name
     * @param string $image The new image data
     *
     * @return bool Whether the update was successful
     */
    public static function update(int $id, string $name, string $image): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('UPDATE ' . self::TABLE_NAME . ' SET name = ?, image = ? WHERE id = ? AND deleted = "false"');

            return $stmt->execute([$name, $image, $id]);
        } catch (\Exception $e) {
            Database::db_Error('Failed to update image: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete an image (soft delete).
     *
     * @param int $id The image ID
     *
     * @return bool Whether the deletion was successful
     */
    public static function delete(int $id): bool
    {
        try {
            Database::markRecordAsDeleted(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to delete image: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Restore a deleted image.
     *
     * @param int $id The image ID
     *
     * @return bool Whether the restoration was successful
     */
    public static function restore(int $id): bool
    {
        try {
            Database::restoreRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to restore image: ' . $e->getMessage());

            return false;
        }
    }

    public static function doesNameAlreadyExist(string $name): bool
    {
        try {
            $pdo = Database::getPdoConnection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::TABLE_NAME . ' WHERE name = ? AND deleted = "false"');
            $stmt->execute([$name]);

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if image name exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Lock an image.
     *
     * @param int $id The image ID
     *
     * @return bool Whether the locking was successful
     */
    public static function lock(int $id): bool
    {
        try {
            Database::lockRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to lock image: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Unlock an image.
     *
     * @param int $id The image ID
     *
     * @return bool Whether the unlocking was successful
     */
    public static function unlock(int $id): bool
    {
        try {
            Database::unlockRecord(self::TABLE_NAME, $id);

            return true;
        } catch (\Exception $e) {
            Database::db_Error('Failed to unlock image: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if an image is locked.
     *
     * @param int $id The image ID
     *
     * @return bool Whether the image is locked
     */
    public static function isLocked(int $id): bool
    {
        try {
            return Database::isLocked(self::TABLE_NAME, $id);
        } catch (\Exception $e) {
            Database::db_Error('Failed to check image lock status: ' . $e->getMessage());

            return false;
        }
    }

    public static function exists(int $id): bool
    {
        return self::get($id) !== null;
    }
}
