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

class MySQL extends Connection
{
    /**
     * Execute a database query.
     *
     * @param string $query The SQL query
     * @param array $params An array of parameters to bind to the query
     * @param bool $returnResult Whether to return the result set for select queries
     *
     * MySQL Connection
     * @param string $host The database host
     * @param int|null $port The database port
     * @param string $username The database username
     * @param string|null $password The database password
     * @param string $table The databases table!
     *
     * @return mixed True if the query was successful (for non-select queries), mysqli_result for select queries, false on failure
     */
    private static function executeQuery(string $query, array $params, bool $returnResult, string $host, int $port, string $username, ?string $password, string $table): mixed
    {
        $connection = self::getRemoteConnection($host, $port, $username, $password, $table);
        $stmt = mysqli_prepare($connection, $query);

        if (!empty($params)) {
            $paramTypes = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);
        }

        $result = $stmt->execute();

        if ($returnResult) {
            $resultSet = $stmt->get_result();
            $stmt->close();

            return $resultSet;
        }

        $stmt->close();

        // Close the connection after use
        self::closeConnection();

        return $result;
    }
}
