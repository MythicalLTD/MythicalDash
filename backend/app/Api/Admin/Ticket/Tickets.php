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

use MythicalDash\App;
use MythicalDash\Chat\User\Can;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Tickets\Tickets;
use MythicalDash\Chat\columns\UserColumns;

$router->get('/api/admin/tickets', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    if (Can::canAccessAdminUI($session->getInfo(UserColumns::ROLE_ID, false))) {
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
    } else {
        $appInstance->Unauthorized('Unauthorized', ['error_code' => 'INVALID_SESSION']);
    }
});
