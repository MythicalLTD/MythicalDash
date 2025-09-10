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

namespace MythicalDash\Chat\Tickets;

use MythicalDash\Chat\Database;

class Departments extends Database
{
    public const TABLE_NAME = 'mythicaldash_departments';

    /**
     * Get the table name for departments.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Create a new department.
     *
     * @param string $name The name of the department
     * @param string $description The description of the department
     * @param string $open The opening time
     * @param string $close The closing time
     * @param string $enabled Whether the department is enabled ("true" or "false")
     *
     * @return int|false The ID of the newly created department, or false on failure
     */
    public static function create(
        string $name,
        string $description,
        string $open,
        string $close,
        string $enabled,
    ): int|false {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'INSERT INTO ' . self::getTableName() . ' (name, description, time_open, time_close, enabled) VALUES (:name, :description, :open, :close, :enabled)';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':open', $open);
            $stmt->bindParam(':close', $close);
            $stmt->bindParam(':enabled', $enabled);
            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create department: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update an existing department.
     *
     * @param int $id The ID of the department to update
     * @param string $name The new name of the department
     * @param string $description The new description of the department
     * @param string $open The new opening time
     * @param string $close The new closing time
     * @param string $enabled The new enabled status ("true" or "false")
     *
     * @return bool True on success, false on failure
     */
    public static function update(
        int $id,
        string $name,
        string $description,
        string $open,
        string $close,
        string $enabled,
    ): bool {
        try {
            if (!self::exists($id)) {
                self::db_Error('Department does not exist but tried to update it: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET name = :name, description = :description, time_open = :open, time_close = :close, enabled = :enabled WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':open', $open);
            $stmt->bindParam(':close', $close);
            $stmt->bindParam(':enabled', $enabled);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update department: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a department exists by ID.
     *
     * @param int $id The ID of the department to check
     *
     * @return bool True if the department exists, false otherwise
     */
    public static function exists(int $id): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if department exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a department (soft delete).
     *
     * @param int $id The ID of the department to delete
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Department does not exist but tried to delete it: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete department: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all departments.
     *
     * @return array The list of departments
     */
    public static function getAll(): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false" ORDER BY id ASC LIMIT 100';
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all departments: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a department by ID.
     *
     * @param int $id The ID of the department to get
     *
     * @return array|null The department data or null if not found
     */
    public static function getById(int $id): ?array
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Department does not exist but tried to get it: ' . $id);

                return null;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get department: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get enabled departments.
     *
     * @return array The list of enabled departments
     */
    public static function getEnabledDepartments(): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE enabled = "true" AND deleted = "false" ORDER BY id ASC';
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get enabled departments: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Alias for getById for backward compatibility.
     *
     * @param int $id The ID of the department to get
     *
     * @return array|null The department data or null if not found
     */
    public static function get(int $id): ?array
    {
        return self::getById($id);
    }
}
