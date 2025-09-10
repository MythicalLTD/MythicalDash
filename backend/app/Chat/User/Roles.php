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

namespace MythicalDash\Chat\User;

use MythicalDash\Chat\Database;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\columns\RolesColumns;
use MythicalDash\Chat\interface\RolesInterface;

class Roles extends Database
{
    public const TABLE_NAME = 'mythicaldash_roles';

    /**
     * Get the list of roles.
     */
    public static function getList(): array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE deleted = \'false\'');
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get list of roles: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get the role info.
     *
     * @param RolesInterface|string $real_name The role name
     * @param RolesColumns|string $info The column name
     *
     * @throws \InvalidArgumentException If the column name is invalid
     *
     * @return string|null The value of the column
     */
    public static function getInfo(RolesInterface|string $real_name, RolesColumns|string $info): ?string
    {
        try {
            if (!in_array($info, RolesColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT ' . $info . ' FROM ' . self::TABLE_NAME . ' WHERE real_name = :real_name');
            $stmt->bindParam(':real_name', $real_name);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to grab the info about the role: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Update the role info.
     *
     * @param RolesColumns|string $info The column name
     * @param string $value The new value
     *
     * @throws \InvalidArgumentException If the column name is invalid
     */
    public static function updateInfo(int $id, RolesColumns|string $info, string $value): bool
    {
        try {
            if (!in_array($info, RolesColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }
            $con = self::getPdoConnection();
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET ' . $info . ' = :value WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':value', $value);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to update the role info: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the role name.
     *
     * @param string $uuid The user UUID
     *
     * @return string|null The role name
     */
    public static function getUserRoleName(?string $uuid): ?string
    {
        if ($uuid === null) {
            self::db_Error('Failed to get role name: UUID is null');

            return null;
        }
        try {
            $con = self::getPdoConnection();
            $token = User::getTokenFromUUID($uuid);
            if ($token === null) {
                self::db_Error('Failed to get role name: Token is null for UUID ' . $uuid);

                return null;
            }
            $id = User::getInfo($token, UserColumns::ROLE_ID, false);
            if ($id === null) {
                self::db_Error('Failed to get role name: Role ID is null for UUID ' . $uuid);

                return null;
            }
            $stmt = $con->prepare('SELECT name FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = \'false\'');
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get role name: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the role real name.
     *
     * @param string $uuid The user UUID
     *
     * @return string|null The role real name
     */
    public static function getUserRealName(string $uuid): ?string
    {
        try {
            $con = self::getPdoConnection();
            $id = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::ROLE_ID, false);
            $stmt = $con->prepare('SELECT real_name FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = \'false\'');
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get real role name: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the role name from role ID.
     *
     * @param int $roleId The role ID
     * @param bool $useRealName Whether to return the real_name instead of name
     *
     * @return string|null The role name or real name
     */
    public static function getRoleNameById(int $roleId, bool $useRealName = false): ?string
    {
        try {
            $con = self::getPdoConnection();
            $column = $useRealName ? 'real_name' : 'name';
            $stmt = $con->prepare('SELECT ' . $column . ' FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = \'false\'');
            $stmt->bindParam(':id', $roleId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get role name by ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get a role by ID.
     *
     * @param int $id The role ID
     *
     * @return array|null The role data or null if not found
     */
    public static function getRole(int $id): ?array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :id AND deleted = \'false\'');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e) {
            self::db_Error('Failed to get role: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create a new role.
     *
     * @param string $name The role name
     * @param string $real_name The role real name
     * @param string $color The role color
     *
     * @return bool True if the role was created, false otherwise
     */
    public static function createRole(string $name, string $real_name, string $color): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('INSERT INTO ' . self::TABLE_NAME . ' (name, real_name, color) VALUES (:name, :real_name, :color)');
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':real_name', $real_name);
            $stmt->bindParam(':color', $color);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to create role: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a role.
     *
     * @param int $id The role ID
     *
     * @return bool True if the role was deleted, false otherwise
     */
    public static function deleteRole(int $id): bool
    {
        try {
            if ($id === 1 || $id === 7) {
                return false;
            }
            $con = self::getPdoConnection();
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET deleted = \'true\' WHERE id = :id');
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to delete role: ' . $e->getMessage());

            return false;
        }
    }
}
