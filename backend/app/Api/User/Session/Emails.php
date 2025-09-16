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
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Session;
use MythicalDash\Chat\Mails\MailList;
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
        'emails' => MailList::getByUserUuid(User::getInfo($accountToken, UserColumns::UUID, false)),
    ]);
});

$router->get('/api/user/session/emails/(.*)/raw', function (string $id): void {
    global $eventManager;

    $appInstance = App::getInstance(true);
    if ($id == '') {
        header('location: /account');
        exit;
    }

    if (!is_numeric($id)) {
        header('location: /account');
        exit;
    }
    $id = (int) $id;

    $appInstance->allowOnlyGET();

    $session = new Session($appInstance);

    $accountToken = $session->SESSION_KEY;

    if (MailList::exists($id)) {
        if (MailList::doesUserOwnEmail(User::getInfo($accountToken, UserColumns::UUID, false), $id)) {
            $eventManager->emit(MythicalDash\Plugins\Events\Events\UserEmailEvent::onEmailView(), [$id]);
            $mail = MailList::get($id);
            UserActivities::add(
                User::getInfo($accountToken, UserColumns::UUID, false),
                UserActivitiesTypes::$email_view,
                CloudFlareRealIP::getRealIP()
            );
            header('Content-Type: text/html; charset=utf-8');
            echo $mail['body'];
            exit;
        }
        header('location: /account');
        exit;

    }
    header('location: /account');
    exit;

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
    if (MailList::exists($id)) {
        if (MailList::doesUserOwnEmail(User::getInfo($accountToken, UserColumns::UUID, false), $id)) {
            $eventManager->emit(MythicalDash\Plugins\Events\Events\UserEmailEvent::onEmailDelete(), [$id]);
            UserActivities::add(
                User::getInfo($accountToken, UserColumns::UUID, false),
                UserActivitiesTypes::$email_delete,
                CloudFlareRealIP::getRealIP()
            );
            MailList::delete($id, User::getInfo($accountToken, UserColumns::UUID, false));
            $appInstance->OK('Email deleted successfully!', []);
        } else {
            $appInstance->Unauthorized('Unauthorized', ['error_code' => 'UNAUTHORIZED']);
        }
    } else {
        $appInstance->BadRequest('Email not found!', ['error_code' => 'EMAIL_NOT_FOUND']);
    }
});
