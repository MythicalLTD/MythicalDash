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

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\User;

$router->get('/api/user/search', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();

    $query = $_GET['query'] ?? '';

    if (strlen($query) < 3) {
        $appInstance->BadRequest('Search query must be at least 3 characters', []);

        return;
    }

    try {
        $users = [];
        $con = Database::getPdoConnection();

        // Search for users where username contains the query string
        $searchQuery = "%{$query}%";
        $stmt = $con->prepare('SELECT 
            username, 
            uuid, 
            avatar, 
            last_seen, 
            banned 
        FROM ' . User::TABLE_NAME . ' 
        WHERE username LIKE :query 
        LIMIT 10');

        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $user) {
            $users[] = [
                'uuid' => $user['uuid'],
                'username' => $user['username'],
                'avatar' => $user['avatar'],
                'last_seen' => $user['last_seen'],
                'banned' => $user['banned'] === 'YES',
            ];
        }

        $appInstance->OK('Search results', [
            'success' => true,
            'users' => $users,
        ]);

    } catch (Exception $e) {
        $appInstance->BadRequest('Error performing search', [
            'error' => $e->getMessage(),
        ]);
    }
});
