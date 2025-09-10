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

namespace MythicalDash\Hooks\MythicalSystems\Database;

class Connection
{
    private static $remote_Connection;
    private static $local_Connection;

    /**
     * Get a remote connection to a MySQL server!
     *
     * @param string $host The database host
     * @param int|null $port The database port
     * @param string $username The database username
     * @param string|null $password The database password
     * @param string $table The databases table!
     *
     * @throws \Exception
     *
     * @return \mysqli A MySQL connection
     */
    public static function getRemoteConnection(string $host, int $port, string $username, ?string $password, string $table): \mysqli
    {
        if (!isset(self::$remote_Connection)) {
            self::$remote_Connection = new \mysqli(
                $host,
                $username,
                $password,
                $table,
                $port
            );

            if (self::$remote_Connection->connect_error) {
                throw new \Exception('[MythicalSystems] Failed to connect to the MySQL/MariaDB server:' . self::$remote_Connection->connect_error);
            }
        }

        return self::$remote_Connection;
    }

    /**
     * Connect to a local database.
     *
     * @param string $file The file path
     *
     * @throws \Exception
     *
     * @return \SQLite3 The connection
     */
    public static function getLocalConnection(string $file): \SQLite3
    {
        if (file_exists($file)) {
            if (extension_loaded('sqlite3')) {
                if (!isset(self::$local_Connection)) {
                    self::$local_Connection = new \SQLite3($file);
                    if (!self::$local_Connection) {

                    }
                }

                return self::$local_Connection;
            } else {
                throw new \Exception('It looks like you did not install the sqlite3 extension for php!');
            }
        } else {
            throw new \Exception('No valid file path was provided or file does not exist');
        }

    }

    /**
     * Close a database connection if open.
     */
    public static function closeConnection(): void
    {
        if (isset(self::$remote_Connection)) {
            self::$remote_Connection->close();
            self::$remote_Connection = null;
        }

        if (isset(self::$local_Connection)) {
            self::$local_Connection->close();
            self::$local_Connection = null;
        }
    }
}
