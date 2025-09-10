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

use PDO;

class Database
{
    private $pdo;
    private $mysqli;
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $port;

    /**
     * Database constructor.
     *
     * @param string $host the hostname or path to the database
     * @param string $dbName the name of the database (not used for sqlite)
     * @param string|null $username the username for the database connection (not used for sqlite)
     * @param string|null $password the password for the database connection (not used for sqlite)
     * @param int $port the port to use for the database connection
     *
     * @throws \Exception if an unsupported database type is provided or the connection fails
     */
    public function __construct($host, $dbName, $username = null, $password = null, int $port = 3306)
    {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbName";
        try {
            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception('Connection failed: ' . $e->getMessage());
        }

        $this->host = $host;
        $this->dbName = $dbName;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function getMysqli(): \mysqli
    {
        return new \mysqli($this->host, $this->username, $this->password, $this->dbName);
    }

    /**
     * Get the PDO connection.
     *
     * @return \PDO the PDO connection
     */
    public static function getPdoConnection(): \PDO
    {
        /**
         * Load the environment variables.
         */
        \MythicalDash\App::getInstance(true)->loadEnv();
        $con = new self($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);

        return $con->getPdo();
    }

    /**
     * Get the table row count.
     *
     * @param string $table the table name
     */
    public static function getTableRowCount(string $table, bool $adminSide = false): int
    {
        try {
            if ($adminSide) {
                $query = self::getPdoConnection()->query('SELECT COUNT(*) FROM ' . $table . ' WHERE deleted = "false"');
            } else {
                $query = self::getPdoConnection()->query('SELECT COUNT(*) FROM ' . $table);
            }

            return (int) $query->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get table row count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get the table column count.
     *
     * @param string $table the table name
     * @param array $where the where conditions
     * @param bool $includeDeleted whether to include deleted records
     *
     * @return int the number of rows in the table
     */
    public static function getTableColumnCount(string $table, array $where = [], bool $includeDeleted = false): int
    {
        try {
            $conditions = [];
            $params = [];

            // Add non-deleted condition by default unless includeDeleted is true
            if (!$includeDeleted) {
                $conditions[] = "deleted = 'false'";
            }

            // Process where conditions
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    // Handle special operators like >, <, >=, <=, !=
                    $conditions[] = "{$value[0]} {$value[1]} ?";
                    $params[] = $value[2];
                } else {
                    // Standard equals condition
                    $conditions[] = "$key = ?";
                    $params[] = $value;
                }
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $query = self::getPdoConnection()->prepare('SELECT COUNT(*) FROM ' . $table . ' ' . $whereClause);
            $query->execute($params);

            return (int) $query->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get table column count: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Check if a table exists.
     *
     * @param string $table the table name
     *
     * @return bool true if the table exists, false otherwise
     */
    public static function tableExists(string $table): bool
    {
        try {
            $query = self::getPdoConnection()->query("SHOW TABLES LIKE '$table'");

            return $query->rowCount() > 0;
        } catch (\Exception $e) {
            self::db_Error('Failed to check if table exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all tables in the database.
     */
    public static function getTables(): array
    {
        try {
            $query = self::getPdoConnection()->query('SHOW TABLES');

            return $query->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            self::db_Error('Failed to get tables: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Marks a record as deleted in the specified table by setting the 'deleted' column to 'true'.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to mark as deleted
     */
    public static function markRecordAsDeleted(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('UPDATE ' . $table . " SET deleted = 'true' WHERE id = " . $row);
            $query->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to mark record as deleted: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Retrieves all records marked as deleted from the specified table.
     *
     * @param string $table the name of the table to query
     *
     * @return array array of deleted records in associative array format
     */
    public static function getDeletedRecords(string $table): array
    {
        try {
            $query = self::getPdoConnection()->query('SELECT * FROM ' . $table . " WHERE deleted = 'true'");

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to get deleted records: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Restores a previously deleted record by setting the 'deleted' column to 'false'.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to restore
     */
    public static function restoreRecord(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('UPDATE ' . $table . " SET deleted = 'false' WHERE id = " . $row);
            $query->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to restore record: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Permanently deletes a record from the specified table.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to delete
     */
    public static function deleteRecord(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('DELETE FROM ' . $table . ' WHERE id = ' . $row);
            $query->execute();

        } catch (\Exception $e) {
            self::db_Error('Failed to delete record: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Locks a record in the specified table by setting the 'locked' column to 'true'.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to lock
     */
    public static function lockRecord(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('UPDATE ' . $table . " SET locked = 'true' WHERE id = " . $row);
            $query->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to lock record: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Unlocks a record in the specified table by setting the 'locked' column to 'false'.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to unlock
     */
    public static function unlockRecord(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('UPDATE ' . $table . " SET locked = 'false' WHERE id = " . $row);
            $query->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to unlock record: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Checks if a specific record is locked.
     *
     * @param string $table the name of the table containing the record
     * @param int $row the ID of the record to check
     *
     * @return bool returns true if the record is locked, false otherwise
     */
    public static function isLocked(string $table, int $row): bool
    {
        try {
            $query = self::getPdoConnection()->query('SELECT locked FROM ' . $table . ' WHERE id = ' . $row);

            return $query->fetch(\PDO::FETCH_ASSOC)['locked'] == 'true';
        } catch (\Exception $e) {
            self::db_Error('Failed to check for lock: ' . $e->getMessage());

            return false;
        }
    }

    public static function db_Error(string $message): void
    {
        $app = \MythicalDash\App::getInstance(true);
        $app->getLogger()->error($message, true);
    }

    /**
     * DANGER: This function is dangerous and should only be used in special cases.
     * DANGER: This function can break the database.
     * DANGER: This function can corrupt the database.
     * DANGER: This function can crash the server.
     * DANGER: This function can brick the server.
     * DANGER: This function can brick the database.
     * DANGER: This function can brick the server and the database.
     *
     * Run a SQL query.
     *
     * @param string $sql the SQL query to run
     *
     * @return array the result of the SQL query
     */
    public static function runSQL(string $sql): array
    {
        try {
            $query = self::getPdoConnection()->query($sql);

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to run SQL: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get the last insert ID.
     *
     * @param string $table the table name
     *
     * @return int the last insert ID
     */
    public static function getLastInsertId(string $table): int
    {
        try {
            $query = self::getPdoConnection()->query('SELECT LAST_INSERT_ID() FROM ' . $table);

            return (int) $query->fetchColumn();
        } catch (\Exception $e) {
            self::db_Error('Failed to get last insert ID: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Request to save and unlock a record.
     *
     * @param string $table the table name
     * @param int $row the ID of the record to save and unlock
     */
    public static function requestSaveAndUnlock(string $table, int $row): void
    {
        try {
            $query = self::getPdoConnection()->query('UPDATE ' . $table . " SET locked = 'false' WHERE id = " . $row);
            $query->execute();
        } catch (\Exception $e) {
            self::db_Error('Failed to request save and unlock: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Run a raw SQL query.
     *
     * @param string $query the SQL query to run
     *
     * @return array the result of the SQL query
     */
    public static function rawQuery(string $query): array
    {
        try {
            $query = self::getPdoConnection()->query($query);

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            self::db_Error('Failed to run raw query: ' . $e->getMessage());

            return [];
        }
    }
}
