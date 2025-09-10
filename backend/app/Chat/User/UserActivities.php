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
use MythicalDash\Chat\interface\UserActivitiesTypes;

class UserActivities extends Database
{
    public const TABLE_NAME = 'mythicaldash_users_activities';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Add user activity.
     *
     * @param string $uuid User UUID
     * @param string|UserActivitiesTypes $type Activity type
     * @param string $ipv4 IP address
     * @param string $context Context
     *
     * @return bool True if the activity was added, false otherwise
     */
    public static function add(string $uuid, string|UserActivitiesTypes $type, string $ipv4, string $context = 'None'): bool
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('INSERT INTO ' . self::getTable() . ' (user, action, ip_address, context) VALUES (:user, :action, :ip_address, :context)');

            return $stmt->execute([
                ':user' => $uuid,
                ':action' => $type,
                ':ip_address' => $ipv4,
                ':context' => $context,
            ]);
        } catch (\Exception $e) {
            self::db_Error('Failed to add user activity: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get user activities.
     *
     * @param string $uuid User UUID
     */
    public static function get(string $uuid, int $limit = 125): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTable() . ' WHERE user = :user ORDER BY id DESC LIMIT ' . $limit);
            $stmt->execute([
                ':user' => $uuid,
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get user activities: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all user activities.
     *
     * @param int $limit Limit
     */
    public static function getAll(int $limit = 50): array
    {
        try {
            $dbConn = Database::getPdoConnection();

            $stmt = $dbConn->prepare('SELECT * FROM ' . self::getTable() . ' LIMIT ' . $limit);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all user activities: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get table name.
     *
     * @return string Table name
     */
    public static function getTable(): string
    {
        return 'mythicaldash_users_activities';
    }
}
