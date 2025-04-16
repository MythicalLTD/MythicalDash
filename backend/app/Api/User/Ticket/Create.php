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

    if ($config->getSetting(ConfigInterface::ALLOW_TICKETS, 'false') === 'false') {
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
