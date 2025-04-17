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
