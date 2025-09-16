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

namespace MythicalDash\Chat;

class TimedTask extends Database
{
    private static string $table = 'mythicaldash_timed_tasks';

    /**
     * List tasks with optional search by task_name.
     */
    public static function getAll(?string $search = null, int $limit = 25, int $offset = 0): array
    {
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT * FROM ' . self::$table;
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE task_name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Get total count (with optional search). */
    public static function getCount(?string $search = null): int
    {
        $pdo = Database::getPdoConnection();
        $sql = 'SELECT COUNT(*) FROM ' . self::$table;
        $params = [];
        if ($search !== null && $search !== '') {
            $sql .= ' WHERE task_name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** Get a task by id. */
    public static function getById(int $id): ?array
    {
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('SELECT * FROM ' . self::$table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /** Get a task by its unique task_name. */
    public static function getByName(string $taskName): ?array
    {
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('SELECT * FROM ' . self::$table . ' WHERE task_name = :name LIMIT 1');
        $stmt->execute(['name' => $taskName]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /** Create a new timed task. */
    public static function create(array $data): int|false
    {
        // Required minimal fields
        if (!isset($data['task_name']) || trim((string) $data['task_name']) === '') {
            return false;
        }

        $allowed = ['task_name', 'last_run_at', 'last_run_success', 'last_run_message'];
        $insert = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $insert[$field] = $data[$field];
            }
        }

        if (empty($insert)) {
            $insert['task_name'] = $data['task_name'];
        }

        $pdo = Database::getPdoConnection();
        $fields = array_keys($insert);
        $placeholders = array_map(fn ($f) => ':' . $f, $fields);
        $sql = 'INSERT INTO ' . self::$table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($insert)) {
            return (int) $pdo->lastInsertId();
        }

        return false;
    }

    /** Update an existing task by id. */
    public static function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $allowed = ['task_name', 'last_run_at', 'last_run_success', 'last_run_message'];
        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (empty($update)) {
            return false;
        }

        $set = implode(', ', array_map(fn ($f) => "$f = :$f", array_keys($update)));
        $update['id'] = $id;

        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('UPDATE ' . self::$table . ' SET ' . $set . ' WHERE id = :id');

        return $stmt->execute($update);
    }

    /** Delete a task by id. */
    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare('DELETE FROM ' . self::$table . ' WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    /** Convenience method to mark run result for a task by name. */
    public static function markRun(string $taskName, bool $success, ?string $message = null): bool
    {
        $pdo = Database::getPdoConnection();
        $stmt = $pdo->prepare(
            // Try to update, or insert if not exists
            'INSERT INTO ' . self::$table . ' (task_name, last_run_at, last_run_success, last_run_message) VALUES (:name, NOW(), :success, :msg)
			ON DUPLICATE KEY UPDATE last_run_at = NOW(), last_run_success = :success, last_run_message = :msg'
        );

        return $stmt->execute([
            'success' => $success ? 1 : 0,
            'msg' => $message,
            'name' => $taskName,
        ]);
    }
}
