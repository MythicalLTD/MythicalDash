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

namespace MythicalDash\Chat\Eggs;

use MythicalDash\Chat\Database;

class EggCategories extends Database
{
    public const TABLE_NAME = 'mythicaldash_eggs_categories';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Get all egg categories.
     *
     * @return array The egg categories
     */
    public static function getCategories(): array
    {
        $dbConn = Database::getPdoConnection();
        $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new egg category.
     *
     * @param string $name The name of the category
     * @param string $description The description of the category
     * @param int $pterodactylNestId The Pterodactyl nest ID
     * @param bool $enabled Whether the category is enabled
     * @param bool $locked Whether the category is locked
     *
     * @return int The ID of the category
     */
    public static function create(string $name, string $description, int $pterodactylNestId, bool $enabled = true, bool $locked = false): int
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' 
                (name, description, pterodactyl_nest_id, enabled) 
                VALUES (:name, :description, :pterodactyl_nest_id, :enabled)');

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':pterodactyl_nest_id', $pterodactylNestId);
            $stmt->bindParam(':enabled', $enabled, \PDO::PARAM_BOOL);

            $stmt->execute();

            return $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create egg category: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Update an existing egg category.
     *
     * @param int $id The ID of the category to update
     * @param string $name The new name of the category
     * @param string $description The new description of the category
     * @param string $enabled The new enabled status
     *
     * @return bool True if the category was updated successfully, false otherwise
     */
    public static function update(int $id, string $name, string $description, string $enabled): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Egg category does not exist but tried to update it: ' . $id);

                return false;
            }

            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET name = :name, description = :description, enabled = :enabled, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':enabled', $enabled);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update egg category: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Soft delete a category.
     *
     * @param int $id The ID of the category to delete
     *
     * @return bool True if the category was deleted successfully, false otherwise
     */
    public static function delete(int $id): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Egg category does not exist but tried to delete it: ' . $id);

                return false;
            }

            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' SET deleted = "true", updated_at = NOW() WHERE id = :id');
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete egg category: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a category by ID.
     *
     * @param int $id The ID of the category to get
     *
     * @return array|null The category data or null if not found
     */
    public static function get(int $id): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get egg category: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if a category exists.
     *
     * @param int $id The ID of the category to check
     *
     * @return bool True if the category exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"');
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if egg category exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a category exists by Pterodactyl nest ID.
     *
     * @param int $pterodactylNestId The Pterodactyl nest ID to check
     *
     * @return bool True if the category exists, false otherwise
     */
    public static function existsByPterodactylNestId(int $pterodactylNestId): bool
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' 
                WHERE pterodactyl_nest_id = :pterodactyl_nest_id AND deleted = "false"');
            $stmt->bindParam(':pterodactyl_nest_id', $pterodactylNestId);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if egg category exists by Pterodactyl nest ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all enabled categories.
     *
     * @return array The enabled categories
     */
    public static function getEnabledCategories(): array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' WHERE enabled = 1 AND deleted = "false"');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get enabled egg categories: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a category by Pterodactyl nest ID.
     *
     * @param int $pterodactylNestId The Pterodactyl nest ID to get
     *
     * @return array|null The category data or null if not found
     */
    public static function getByPterodactylNestId(int $pterodactylNestId): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
				WHERE pterodactyl_nest_id = :pterodactyl_nest_id AND deleted = "false"');

            $stmt->bindParam(':pterodactyl_nest_id', $pterodactylNestId);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get egg category by Pterodactyl nest ID: ' . $e->getMessage());

            return null;
        }
    }
}
