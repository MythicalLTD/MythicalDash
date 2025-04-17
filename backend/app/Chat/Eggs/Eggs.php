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

class Eggs extends Database
{
    /**
     * Get the table name for eggs.
     */
    public static function getTableName(): string
    {
        return 'mythicaldash_eggs';
    }

    /**
     * Create a new egg.
     *
     * @param string $name The name of the egg
     * @param string $description The description of the egg
     * @param int $categoryId The category ID this egg belongs to
     * @param int $pterodactylEggId The Pterodactyl egg ID
     * @param string $enabled Whether the egg is enabled ("true" or "false")
     *
     * @return int|false The ID of the newly created egg, or false on failure
     */
    public static function create(string $name, string $description, int $categoryId, int $pterodactylEggId, string $enabled = 'false'): int|false
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTableName() . ' 
                (name, description, category, pterodactyl_egg_id, enabled) 
                VALUES (:name, :description, :category, :pterodactyl_egg_id, :enabled)');

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $categoryId);
            $stmt->bindParam(':pterodactyl_egg_id', $pterodactylEggId);
            $stmt->bindParam(':enabled', $enabled);

            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create egg: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update an existing egg.
     *
     * @param int $id The ID of the egg to update
     * @param string $name The new name of the egg
     * @param string $description The new description of the egg
     * @param int $categoryId The new category ID
     * @param int $pterodactylEggId The new Pterodactyl egg ID
     * @param string $enabled The new enabled status ("true" or "false")
     *
     * @return bool True on success, false on failure
     */
    public static function update(int $id, string $name, string $description, int $categoryId, int $pterodactylEggId, string $enabled = 'false'): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('UPDATE ' . self::getTableName() . ' 
                SET name = :name, description = :description, category = :category, 
                pterodactyl_egg_id = :pterodactyl_egg_id, enabled = :enabled, updated_at = NOW() 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $categoryId);
            $stmt->bindParam(':pterodactyl_egg_id', $pterodactylEggId);
            $stmt->bindParam(':enabled', $enabled);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update egg: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete an egg (soft delete).
     *
     * @param int $id The ID of the egg to delete
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
            self::db_Error('Failed to delete egg: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get an egg by ID.
     *
     * @param int $id The ID of the egg to get
     *
     * @return array|null The egg data or null if not found
     */
    public static function getById(int $id): ?array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
                WHERE id = :id AND deleted = "false"');

            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get egg: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Check if an egg exists by ID.
     *
     * @param int $id The ID of the egg to check
     *
     * @return bool True if the egg exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        return self::getById($id) !== null;
    }

    /**
     * Get all eggs.
     *
     * @return array The list of eggs
     */
    public static function getAll(): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
                WHERE deleted = "false"');

            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all eggs: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get eggs by category ID.
     *
     * @param int $categoryId The category ID to get eggs for
     *
     * @return array The list of eggs in the category
     */
    public static function getByCategoryId(int $categoryId): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
                WHERE category = :category_id AND deleted = "false"');

            $stmt->bindParam(':category_id', $categoryId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get eggs by category: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get enabled eggs by category ID.
     *
     * @param int $categoryId The category ID to get eggs for
     *
     * @return array The list of enabled eggs in the category
     */
    public static function getEnabledByCategoryId(int $categoryId): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
                WHERE category = :category_id AND enabled = "true" AND deleted = "false"');

            $stmt->bindParam(':category_id', $categoryId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get enabled eggs by category: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get eggs by Pterodactyl egg ID.
     *
     * @param int $pterodactylEggId The Pterodactyl egg ID to search for
     *
     * @return array The list of eggs with the given Pterodactyl egg ID
     */
    public static function getByPterodactylEggId(int $pterodactylEggId): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTableName() . ' 
                WHERE pterodactyl_egg_id = :pterodactyl_egg_id AND deleted = "false"');

            $stmt->bindParam(':pterodactyl_egg_id', $pterodactylEggId);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get eggs by Pterodactyl egg ID: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if an egg exists with the given Pterodactyl egg ID.
     *
     * @param int $pterodactylEggId The Pterodactyl egg ID to check
     *
     * @return bool True if an egg exists with the given Pterodactyl egg ID, false otherwise
     */
    public static function existsByPterodactylEggId(int $pterodactylEggId): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT COUNT(*) FROM ' . self::getTableName() . ' 
                WHERE pterodactyl_egg_id = :pterodactyl_egg_id AND deleted = "false"');

            $stmt->bindParam(':pterodactyl_egg_id', $pterodactylEggId);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if egg exists by Pterodactyl egg ID: ' . $e->getMessage());

            return false;
        }
    }
}
