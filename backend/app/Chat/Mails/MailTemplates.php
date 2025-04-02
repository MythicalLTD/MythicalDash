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

namespace MythicalDash\Chat\Mails;

use MythicalDash\Chat\Database;

class MailTemplates extends Database
{
    public const TABLE_NAME = 'mythicaldash_mail_templates';

    /**
     * Get the table name for mail templates.
     *
     * @return string The table name
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Create a new mail template.
     *
     * @param string $name The name of the mail template
     * @param string $content The content of the mail template
     * @param string $active Whether the template is active
     *
     * @return int|false The ID of the newly created mail template, or false on failure
     */
    public static function create(
        string $name,
        string $content,
        string $active = 'false',
    ): int|false {
        try {
            $dbConn = self::getPdoConnection();

            $sql = 'INSERT INTO ' . self::getTableName() . ' (name, content, active) VALUES (:name, :content, :active)';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':active', $active);
            $stmt->execute();

            return (int) $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create mail template: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update an existing mail template.
     *
     * @param int $id The ID of the mail template to update
     * @param string $name The new name of the mail template
     * @param string $content The new content of the mail template
     * @param string $active Whether the template should be active
     *
     * @return bool True on success, false on failure
     */
    public static function update(
        int $id,
        string $name,
        string $content,
        string $active = 'true',
    ): bool {
        try {
            if (!self::exists($id)) {
                self::db_Error('Mail template does not exist but tried to update it: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();

            // Get existing template to preserve values not being updated
            $existingTemplate = self::get($id);
            if (!$existingTemplate) {
                return false;
            }

            $sql = 'UPDATE ' . self::getTableName() . ' SET name = :name, content = :content, active = :active WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':active', $active);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update mail template: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a mail template exists by ID.
     *
     * @param int $id The ID of the mail template to check
     *
     * @return bool True if the mail template exists, false otherwise
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
            self::db_Error('Failed to check if mail template exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if a mail template exists by name.
     *
     * @param string $name The name of the mail template to check
     *
     * @return bool True if the mail template exists, false otherwise
     */
    public static function existsByName(string $name): bool
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' WHERE name = :name AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if mail template exists by name: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a mail template (soft delete).
     *
     * @param int $id The ID of the mail template to delete
     *
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Mail template does not exist but tried to delete it: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $sql = 'UPDATE ' . self::getTableName() . ' SET deleted = "true" WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete mail template: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all mail templates.
     *
     * @param bool $activeOnly Whether to get only active templates
     *
     * @return array The list of mail templates
     */
    public static function getAll(bool $activeOnly = false): array
    {
        try {
            $dbConn = self::getPdoConnection();
            $sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE deleted = "false"';

            if ($activeOnly) {
                $sql .= ' AND active = "true"';
            }

            $sql .= ' ORDER BY id ASC';
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all mail templates: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a mail template by ID.
     *
     * @param int $id The ID of the mail template to get
     *
     * @return array|null The mail template data or null if not found
     */
    public static function get(int $id): ?array
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Mail template does not exist but tried to get it: ' . $id);

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
            self::db_Error('Failed to get mail template: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Set a mail template as active or inactive.
     *
     * @param int $id The ID of the mail template
     * @param bool $active Whether to set the template as active or inactive
     *
     * @return bool True on success, false on failure
     */
    public static function setActive(int $id, bool $active): bool
    {
        try {
            if (!self::exists($id)) {
                self::db_Error('Mail template does not exist but tried to update its active status: ' . $id);

                return false;
            }

            $dbConn = self::getPdoConnection();
            $activeStr = $active ? 'true' : 'false';
            $sql = 'UPDATE ' . self::getTableName() . ' SET active = :active WHERE id = :id AND deleted = "false"';
            $stmt = $dbConn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':active', $activeStr);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to set mail template active status: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get active mail templates.
     *
     * @return array The list of active mail templates
     */
    public static function getActiveTemplates(): array
    {
        return self::getAll(true);
    }
}
