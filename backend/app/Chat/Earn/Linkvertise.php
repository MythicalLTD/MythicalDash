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

namespace MythicalDash\Chat\Earn;

use MythicalDash\App;
use MythicalDash\Chat\Schema;
use MythicalDash\Chat\Database;

class Linkvertise extends Database implements Schema
{
    public static function getTableName(): string
    {
        return 'mythicaldash_linkvertise';
    }

    /**
     * Create a new linkvertise code.
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
            self::db_Error('Failed to create linkvertise: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get all linkvertise codes.
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
            self::db_Error('Failed to get all linkvertise codes: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all linkvertise codes by user.
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
            self::db_Error('Failed to get all linkvertise codes by user: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Delete a linkvertise code.
     *
     * @param int $id The id of the linkvertise code
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
            self::db_Error('Failed to delete linkvertise code: ' . $e->getMessage());

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
     * Mark a linkvertise code as completed.
     *
     * @param int $id The id of the linkvertise code
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
     * Get a linkvertise code by id.
     *
     * @param int $id The id of the linkvertise code
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
            self::db_Error('Failed to get linkvertise code by id: ' . $e->getMessage());

            return [];
        }
    }
}
