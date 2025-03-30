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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Mails;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Chat\interface\UserActivitiesTypes;

$router->get('/api/user/session/emails', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    $appInstance->OK('User emails', [
        'emails' => Mails::getAll(User::getInfo($accountToken, UserColumns::UUID, false)),
    ]);
});

$router->get('/api/user/session/emails/(.*)/raw', function (string $id): void {
    global $eventManager;

    $appInstance = App::getInstance(true);
    if ($id == '') {
        exit(header('location: /account'));
    }

    if (!is_numeric($id)) {
        exit(header('location: /account'));
    }
    $id = (int) $id;

    $appInstance->allowOnlyGET();

    $session = new Session($appInstance);

    $accountToken = $session->SESSION_KEY;

    if (Mails::exists($id)) {
        if (Mails::doesUserOwnEmail(User::getInfo($accountToken, UserColumns::UUID, false), $id)) {
            $eventManager->emit(MythicalDash\Plugins\Events\Events\UserEmailEvent::onEmailView(), [$id]);
            $mail = Mails::get($id);
            UserActivities::add(
                User::getInfo($accountToken, UserColumns::UUID, false),
                UserActivitiesTypes::$email_view,
                CloudFlareRealIP::getRealIP()
            );
            header('Content-Type: text/html; charset=utf-8');
            echo $mail['body'];
            exit;
        }
        exit(header('location: /account'));

    }
    exit(header('location: /account'));

});

$router->delete('/api/user/session/emails/(.*)/delete', function (string $id): void {
    global $eventManager;
    $appInstance = App::getInstance(true);
    if ($id == '') {
        $appInstance->BadRequest('Email not found!', ['error_code' => 'EMAIL_NOT_FOUND']);
    }
    if (!is_numeric($id)) {
        $appInstance->BadRequest('Email not found!', ['error_code' => 'EMAIL_NOT_FOUND']);
    }
    $id = (int) $id;
    $appInstance->allowOnlyDELETE();
    $session = new Session($appInstance);
    $accountToken = $session->SESSION_KEY;
    if (Mails::exists($id)) {
        if (Mails::doesUserOwnEmail(User::getInfo($accountToken, UserColumns::UUID, false), $id)) {
            $eventManager->emit(MythicalDash\Plugins\Events\Events\UserEmailEvent::onEmailDelete(), [$id]);
            UserActivities::add(
                User::getInfo($accountToken, UserColumns::UUID, false),
                UserActivitiesTypes::$email_delete,
                CloudFlareRealIP::getRealIP()
            );
            Mails::delete($id, User::getInfo($accountToken, UserColumns::UUID, false));
            $appInstance->OK('Email deleted successfully!', []);
        } else {
            $appInstance->Unauthorized('Unauthorized', ['error_code' => 'UNAUTHORIZED']);
        }
    } else {
        $appInstance->BadRequest('Email not found!', ['error_code' => 'EMAIL_NOT_FOUND']);
    }
});
