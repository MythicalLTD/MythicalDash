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

namespace MythicalDash\Chat\Mails;

use MythicalDash\Chat\Database;

class MailList extends Database
{
    private static string $table = 'mythicaldash_mail_list';

    public static function create(array $data): int|false
    {
        $required = ['queue_id', 'user_uuid'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return false;
            }
        }
        $pdo = Database::getPdoConnection();
        $fields = array_keys($data);
        $placeholders = array_map(fn ($f) => ':' . $f, $fields);
        $sql = 'INSERT INTO ' . self::$table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($data)) {
            return (int) $pdo->lastInsertId();
        }

        return false;
    }

    public static function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('SELECT * FROM ' . self::$table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getAll(bool $includeDeleted = false): array
    {
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT * FROM ' . self::$table;
        if (!$includeDeleted) {
            $sql .= " WHERE deleted = 'false'";
        }
        $stmt = $pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function update(int $id, array $data): bool
    {
        if ($id <= 0 || empty($data)) {
            return false;
        }
        unset($data['id']);
        $pdo = Database::getPdoConnection();
        $fields = array_keys($data);
        if (empty($fields)) {
            return false;
        }
        $set = implode(', ', array_map(fn ($f) => "$f = :$f", $fields));
        $sql = 'UPDATE ' . self::$table . ' SET ' . $set . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;

        return $stmt->execute($data);
    }

    public static function softDelete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $sql = 'UPDATE ' . self::$table . " SET deleted = 'true' WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public static function hardDelete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $sql = 'DELETE FROM ' . self::$table . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public static function restore(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $sql = 'UPDATE ' . self::$table . " SET deleted = 'false' WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public static function getByUserUuid(string $userUuid): array
    {
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT ml.*, mq.subject, mq.body, mq.status, mq.created_at as email_date, mq.created_at as date,
                "system@mythical.systems" as `from`
                FROM ' . self::$table . ' ml 
                JOIN mythicaldash_mail_queue mq ON ml.queue_id = mq.id 
                WHERE ml.user_uuid = :user_uuid 
                ORDER BY ml.created_at DESC LIMIT 250';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_uuid' => $userUuid]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Additional methods for API compatibility with old Mails class
    public static function exists(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::$table . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    public static function get(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT ml.*, mq.subject, mq.body, mq.status, mq.created_at as email_date, mq.created_at as date,
                "system@mythical.systems" as `from`
                FROM ' . self::$table . ' ml 
                JOIN mythicaldash_mail_queue mq ON ml.queue_id = mq.id 
                WHERE ml.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function doesUserOwnEmail(string $userUuid, int $id): bool
    {
        if ($id <= 0 || empty($userUuid)) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . self::$table . ' WHERE id = :id AND user_uuid = :user_uuid');
        $stmt->execute(['id' => $id, 'user_uuid' => $userUuid]);

        return $stmt->fetchColumn() > 0;
    }

    public static function delete(int $id, string $userUuid): bool
    {
        if ($id <= 0 || empty($userUuid)) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $sql = 'DELETE FROM ' . self::$table . ' WHERE id = :id AND user_uuid = :user_uuid';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(['id' => $id, 'user_uuid' => $userUuid]);
    }

    // Legacy method for compatibility with old Mails::getAll
    public static function getAllLegacy(string $userUuid, int $limit = 50): array
    {
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT ml.*, mq.subject, mq.body, mq.status, mq.created_at as email_date, mq.created_at as date,
                "system@mythical.systems" as `from`
                FROM ' . self::$table . ' ml 
                JOIN mythicaldash_mail_queue mq ON ml.queue_id = mq.id 
                WHERE ml.user_uuid = :user_uuid 
                ORDER BY ml.id DESC LIMIT ' . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_uuid' => $userUuid]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Convenience method to add email (creates both queue entry and list entry)
    public static function addEmail(string $subject, string $body, string $userUuid): bool
    {
        try {
            // First create the email in the queue
            $queueData = [
                'user_uuid' => $userUuid,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
            ];

            $queueId = MailQueue::create($queueData);
            if (!$queueId) {
                return false;
            }

            // Then create the list entry linking the email to the user
            $listData = [
                'queue_id' => $queueId,
                'user_uuid' => $userUuid,
                'deleted' => 'false',
                'locked' => 'false',
            ];

            return self::create($listData) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
