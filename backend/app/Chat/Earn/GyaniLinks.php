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

namespace MythicalDash\Chat\Earn;

use MythicalDash\App;
use MythicalDash\Chat\Schema;
use MythicalDash\Chat\Database;

class GyaniLinks extends Database implements Schema
{
    public static function getTableName(): string
    {
        return 'mythicaldash_gyanilinks';
    }

    /**
     * Create a new LinkPays code.
     */
    public static function create(string $code, string $user): int
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if (empty($code) || empty($user) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $user)) {
                return 0;
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('INSERT INTO `' . self::getTableName() . '` (`code`, `user`) VALUES (:code, :user)');
            $stmt->execute([
                'code' => $code,
                'user' => $user,
            ]);

            return $dbConn->lastInsertId();
        } catch (\Exception $e) {
            self::db_Error('Failed to create LinkPays: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get all LinkPays codes.
     */
    public static function getAll(int $limit = 150): array
    {
        $appInstance = App::getInstance(true);
        try {
            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM `' . self::getTableName() . "` WHERE `deleted` = 'false' AND `locked` = 'false' ORDER BY `created_at` DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all LinkPays codes: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all LinkPays codes by user.
     *
     * @param string $user The uuid of the user
     * @param int $limit The limit of codes to get
     */
    public static function getAllByUser(string $user, int $limit = 150): array
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if (empty($user) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $user)) {
                return [];
            }
            if ($limit < 1 || $limit > 150) {
                $limit = 150;
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM `' . self::getTableName() . "` WHERE `deleted` = 'false' AND `locked` = 'false' AND `user` = :user ORDER BY `created_at` DESC LIMIT :limit");
            $stmt->bindParam(':user', $user);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get all LinkPays codes by user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Delete a LinkPays code.
     *
     * @param int $id The id of the LinkPays code
     */
    public static function delete(int $id): bool
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if ($id < 1) {
                return false;
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE `' . self::getTableName() . "` SET `deleted` = 'true' WHERE `id` = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to delete LinkPays code: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Convert a code to an id.
     *
     * @param string $code The code to convert
     */
    public static function convertCodeToId(string $code): int
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if (empty($code)) {
                return 0;
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT `id` FROM `' . self::getTableName() . "` WHERE `code` = :code AND `deleted` = 'false' AND `locked` = 'false'");
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC)['id'] ?? 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to convert code to id: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Mark a LinkPays code as completed.
     *
     * @param int $id The id of the LinkPays code
     */
    public static function markAsCompleted(int $id): bool
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if ($id < 1) {
                return false;
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('UPDATE `' . self::getTableName() . "` SET `completed` = 'true' WHERE `id` = :id AND `deleted` = 'false' AND `locked` = 'false'");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to mark as completed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get a LinkPays code by id.
     *
     * @param int $id The id of the LinkPays code
     */
    public static function getById(int $id): array
    {
        $appInstance = App::getInstance(true);
        try {
            // Validate input
            if ($id < 1) {
                return [];
            }

            $dbConn = self::getPdoConnection();
            $stmt = $dbConn->prepare('SELECT * FROM `' . self::getTableName() . "` WHERE `deleted` = 'false' AND `locked` = 'false' AND `id` = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ? $result : [];
        } catch (\Exception $e) {
            self::db_Error('Failed to get LinkPays code by id: ' . $e->getMessage());

            return [];
        }
    }
}
