<?php

/*
 * This file is part of McCloudAdmin.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
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