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
use MythicalDash\Permissions;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Tickets\Tickets;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Middleware\PermissionMiddleware;

$router->get('/api/admin/tickets', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_TICKETS_LIST, $session);
    $tickets = Tickets::getAllTickets(9500);

    // Process tickets to include user information instead of just UUID
    foreach ($tickets as &$ticket) {
        // Get user token from UUID
        $userToken = User::getTokenFromUUID($ticket['user']);

        if ($userToken) {
            // Get user information
            $userInfo = User::getInfoArray($userToken, [
                UserColumns::USERNAME,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::EMAIL,
                UserColumns::AVATAR,
            ], [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
            ]);

            // Add user information to ticket
            $ticket['user_details'] = [
                'uuid' => $ticket['user'],
                'username' => $userInfo[UserColumns::USERNAME] ?? 'Unknown',
                'name' => ($userInfo[UserColumns::FIRST_NAME] ?? '') . ' ' . ($userInfo[UserColumns::LAST_NAME] ?? ''),
                'email' => $userInfo[UserColumns::EMAIL] ?? '',
                'avatar' => $userInfo[UserColumns::AVATAR] ?? '',
            ];
        } else {
            $ticket['user_details'] = [
                'uuid' => $ticket['user'],
                'username' => 'Unknown User',
                'name' => 'Unknown User',
                'email' => '',
                'avatar' => '',
            ];
        }
    }

    $appInstance->OK('Tickets', [
        'tickets' => $tickets,
    ]);

});
