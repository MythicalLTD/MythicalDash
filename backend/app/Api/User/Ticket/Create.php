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
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Tickets\Tickets;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Tickets\Departments;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\TicketEvent;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/user/ticket/create', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    new Session($appInstance);

    $departments = Departments::getAll();

    $appInstance->OK('Ticket Process', [
        'departments' => $departments,
    ]);
});

$router->post('/api/user/ticket/create', function () {
    global $eventManager;
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new Session($appInstance);
    $config = $appInstance->getConfig();

    if ($config->getDBSetting(ConfigInterface::ALLOW_TICKETS, 'false') === 'false') {
        $appInstance->BadRequest('Tickets are not enabled!', ['error_code' => 'TICKETS_NOT_ENABLED']);
    }

    if (isset($_POST['department_id']) && $_POST['department_id'] != '') {
        $departmentId = $_POST['department_id'];
        if (Departments::exists((int) $departmentId)) {
            if (isset($_POST['subject']) && $_POST['subject'] != '') {
                $subject = $_POST['subject'];
            } else {
                $appInstance->BadRequest('Subject is missing!', ['error_code' => 'SUBJECT_MISSING']);
            }

            if (isset($_POST['message']) && $_POST['message'] != '') {
                $message = $_POST['message'];
            } else {
                $appInstance->BadRequest('Message is missing!', ['error_code' => 'MESSAGE_MISSING']);
            }

            if (isset($_POST['priority']) && $_POST['priority'] != '') {
                $priority = $_POST['priority'];
            } else {
                $priority = 'low';
            }

            /**
             * Check if the user has more than 3 open tickets.
             */
            $userTickets = Tickets::getAllTicketsByUser($session->getInfo(UserColumns::UUID, false), 150);

            $openTickets = array_filter($userTickets, function ($ticket) {
                return in_array($ticket['status'], ['open', 'waiting', 'replied', 'inprogress']);
            });
            if (count($openTickets) >= 3) {
                $appInstance->BadRequest('You have reached the limit of 3 open tickets!', ['error_code' => 'LIMIT_REACHED']);
            }
            /**
             * Create the ticket.
             */
            $uuid = $session->getInfo(UserColumns::UUID, false);
            $ticketId = Tickets::create($uuid, $departmentId, $subject, $message, $priority);
            UserActivities::add(
                $uuid,
                UserActivitiesTypes::$ticket_create,
                CloudFlareRealIP::getRealIP()
            );
            $eventManager->emit(TicketEvent::onTicketCreate(), [
                'ticket_id' => $ticketId,
                'department_id' => $departmentId,
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority,
                'user_id' => $uuid,
            ]);
            if ($ticketId == 0) {
                $appInstance->BadRequest('Failed to create ticket!', ['error_code' => 'FAILED_TO_CREATE_TICKET']);
            } else {
                $appInstance->OK('Ticket created successfully!', ['ticket_id' => $ticketId]);
            }
        } else {
            $appInstance->BadRequest('Department not found!', ['error_code' => 'DEPARTMENT_NOT_FOUND']);
        }
    } else {
        $appInstance->BadRequest('Department ID is missing!', ['error_code' => 'DEPARTMENT_ID_MISSING']);
    }
});
