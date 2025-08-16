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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Chat\User;

use MythicalDash\Chat\Database;

class Permissions extends Database
{
    public const TABLE_NAME = 'mythicaldash_roles_permissions';

    /**
     * Get the list of permissions.
     */
    public static function getList(): array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE deleted = \'false\'');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get list of permissions: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get a permission by ID.
     *
     * @param int $id The permission ID
     *
     * @return array|null The permission data or null if not found
     */
    public static function getPermission(int $id): ?array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = \'false\'');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get permission: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create a new permission.
     *
     * @param int $role_id The role ID
     * @param string $permission The permission name
     * @param string $granted Whether the permission is granted ('true' or 'false')
     *
     * @return bool True if the permission was created, false otherwise
     */
    public static function createPermission(int $role_id, string $permission, string $granted): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('INSERT INTO ' . self::TABLE_NAME . ' (role_id, permission, granted) VALUES (:role_id, :permission, :granted)');
            $stmt->bindParam(':role_id', $role_id, \PDO::PARAM_INT);
            $stmt->bindParam(':permission', $permission);
            $stmt->bindParam(':granted', $granted);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to create permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update a permission.
     *
     * @param int $id The permission ID
     * @param int $role_id The role ID
     * @param string $permission The permission name
     * @param string $granted Whether the permission is granted ('true' or 'false')
     *
     * @return bool True if the permission was updated, false otherwise
     */
    public static function updatePermission(int $id, int $role_id, string $permission, string $granted): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET role_id = :role_id, permission = :permission, granted = :granted WHERE id = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $role_id, \PDO::PARAM_INT);
            $stmt->bindParam(':permission', $permission);
            $stmt->bindParam(':granted', $granted);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a permission.
     *
     * @param int $id The permission ID
     *
     * @return bool True if the permission was deleted, false otherwise
     */
    public static function deletePermission(int $id): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET deleted = \'true\' WHERE id = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete permission: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get permissions for a specific role.
     *
     * @param int $role_id The role ID
     *
     * @return array The permissions for the role
     */
    public static function getPermissionsByRole(int $role_id): array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE role_id = :role_id AND deleted = \'false\'');
            $stmt->bindParam(':role_id', $role_id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get permissions by role: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if a role has a specific permission.
     *
     * @param int $role_id The role ID
     * @param string $permission The permission name
     *
     * @return bool True if the role has the permission, false otherwise
     */
    public static function hasPermission(int $role_id, string $permission): bool
    {
        // Check if the role has root permission
        try {
            $root = \MythicalDash\Permissions::ADMIN_ROOT;
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT granted FROM ' . self::TABLE_NAME . ' WHERE role_id = :role_id AND permission = :permission AND deleted = \'false\'');
            $stmt->bindParam(':role_id', $role_id, \PDO::PARAM_INT);
            $stmt->bindParam(':permission', $root);
            $stmt->execute();

            $result = $stmt->fetchColumn();
            if ($result === 'true') {
                return true;
            }
        } catch (\Exception $e) {
            self::db_Error('Failed to check root permission: ' . $e->getMessage());
        }

        // Check for the specific permission requested
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT granted FROM ' . self::TABLE_NAME . ' WHERE role_id = :role_id AND permission = :permission AND deleted = \'false\'');
            $stmt->bindParam(':role_id', $role_id, \PDO::PARAM_INT);
            $stmt->bindParam(':permission', $permission);
            $stmt->execute();

            $result = $stmt->fetchColumn();

            return $result === 'true';
        } catch (\Exception $e) {
            self::db_Error('Failed to check permission: ' . $e->getMessage());

            return false;
        }
    }
}
