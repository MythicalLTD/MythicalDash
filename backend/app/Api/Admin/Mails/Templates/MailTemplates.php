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
use MythicalDash\Chat\Mails\MailTemplate;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\User\UserActivities;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Plugins\Events\Events\MailTemplatesEvent;

$router->get('/api/admin/mail/mail-templates', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MAIL_TEMPLATES_LIST, $session);
    $mailTemplates = MailTemplate::getAll();
    $appInstance->OK('Mail templates retrieved successfully.', ['mail_templates' => $mailTemplates]);
});

$router->post('/api/admin/mail/mail-templates/create', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MAIL_TEMPLATES_CREATE, $session);
    if (isset($_POST['name']) && isset($_POST['body']) && isset($_POST['active']) && isset($_POST['subject'])) {
        $name = $_POST['name'];
        $body = $_POST['body'];
        $active = $_POST['active'];
        $subject = $_POST['subject'];
        $active = strtolower($active);
        if (!in_array($active, ['true', 'false'])) {
            $appInstance->BadRequest('Invalid active value', ['error_code' => 'INVALID_ACTIVE_VALUE']);

            return;
        }

        if (strlen($name) > 255) {
            $appInstance->BadRequest('Name is too long', ['error_code' => 'NAME_TOO_LONG']);

            return;
        }

        if (strlen($body) > 65535) {
            $appInstance->BadRequest('Content is too long', ['error_code' => 'CONTENT_TOO_LONG']);

            return;
        }

        if (strlen($name) < 1) {
            $appInstance->BadRequest('Name is too short', ['error_code' => 'NAME_TOO_SHORT']);

            return;
        }

        if (strlen($body) < 1) {
            $appInstance->BadRequest('Content is too short', ['error_code' => 'CONTENT_TOO_SHORT']);

            return;
        }

        if (MailTemplate::existsByName($name)) {
            $appInstance->BadRequest('Mail template already exists', ['error_code' => 'MAIL_TEMPLATE_ALREADY_EXISTS']);

            return;
        }

        $mailTemplates = MailTemplate::createLegacy($name, $body, $active, $subject);
        if ($mailTemplates) {
            global $eventManager;
            $eventManager->emit(MailTemplatesEvent::onCreateMailTemplate(), [
                'id' => $mailTemplates,
                'name' => $name,
                'body' => $body,
                'active' => $active,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$mail_template_create,
                CloudFlareRealIP::getRealIP(),
                "Created mail template $name"
            );
            $appInstance->OK('Mail template created successfully.', ['mail_template' => $mailTemplates]);
        } else {
            $appInstance->BadRequest('Failed to create mail template', ['error_code' => 'FAILED_TO_CREATE_MAIL_TEMPLATE']);
        }
    } else {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }
});

$router->post('/api/admin/mail/mail-templates/(.*)/update', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MAIL_TEMPLATES_EDIT, $session);
    if (isset($_POST['name']) && isset($_POST['body']) && isset($_POST['active']) && isset($_POST['subject'])) {
        $name = $_POST['name'];
        $body = $_POST['body'];
        $active = $_POST['active'];
        $subject = $_POST['subject'];
        if (!MailTemplate::exists($id)) {
            $appInstance->BadRequest('Mail template does not exist', ['error_code' => 'MAIL_TEMPLATE_DOES_NOT_EXIST']);

            return;
        }
        $active = strtolower($active);
        if (!in_array($active, ['true', 'false'])) {
            $appInstance->BadRequest('Invalid active value', ['error_code' => 'INVALID_ACTIVE_VALUE']);

            return;
        }

        if (strlen($name) > 255) {
            $appInstance->BadRequest('Name is too long', ['error_code' => 'NAME_TOO_LONG']);

            return;
        }

        if (strlen($body) > 65535) {
            $appInstance->BadRequest('Content is too long', ['error_code' => 'CONTENT_TOO_LONG']);

            return;
        }

        if (strlen($name) < 1) {
            $appInstance->BadRequest('Name is too short', ['error_code' => 'NAME_TOO_SHORT']);

            return;
        }

        if (strlen($body) < 1) {
            $appInstance->BadRequest('Content is too short', ['error_code' => 'CONTENT_TOO_SHORT']);

            return;
        }
        $info = MailTemplate::get($id);
        if ($info['name'] !== $name) {
            if (MailTemplate::existsByName($name)) {
                $appInstance->BadRequest('Mail template already exists: ' . $name . ' with id: ' . $id . ' and name: ' . $info['name'], ['error_code' => 'MAIL_TEMPLATE_ALREADY_EXISTS']);

                return;
            }
        }

        $mailTemplates = MailTemplate::updateLegacy($id, $name, $body, $active, $subject);
        if ($mailTemplates) {
            global $eventManager;
            $eventManager->emit(MailTemplatesEvent::onUpdateMailTemplate(), [
                'id' => $id,
                'name' => $name,
                'body' => $body,
                'active' => $active,
            ]);
            UserActivities::add(
                $session->getInfo(UserColumns::UUID, false),
                UserActivitiesTypes::$mail_template_update,
                CloudFlareRealIP::getRealIP(),
                "Updated mail template $name"
            );
            $appInstance->OK('Mail template updated successfully.', ['mail_template' => $mailTemplates]);
        } else {
            $appInstance->BadRequest('Failed to update mail template', ['error_code' => 'FAILED_TO_UPDATE_MAIL_TEMPLATE']);
        }
    } else {
        $appInstance->BadRequest('Missing required fields', ['error_code' => 'MISSING_REQUIRED_FIELDS']);
    }
});

$router->post('/api/admin/mail/mail-templates/(.*)/delete', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MAIL_TEMPLATES_DELETE, $session);
    if (!MailTemplate::exists($id)) {
        $appInstance->BadRequest('Mail template does not exist', ['error_code' => 'MAIL_TEMPLATE_DOES_NOT_EXIST']);

        return;
    }

    global $eventManager;
    $eventManager->emit(MailTemplatesEvent::onDeleteMailTemplate(), [
        'id' => $id,
    ]);
    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$mail_template_delete,
        CloudFlareRealIP::getRealIP(),
        "Deleted mail template $id"
    );

    if (MailTemplate::delete($id)) {
        $appInstance->OK('Mail template deleted successfully.', ['mail_template' => $id]);
    } else {
        $appInstance->BadRequest('Failed to delete mail template', ['error_code' => 'FAILED_TO_DELETE_MAIL_TEMPLATE']);
    }
});

// Mass send using a mail template to all users
$router->post('/api/admin/mail/mail-templates/(.*)/mass-send', function (string $id): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();
    $session = new MythicalDash\Chat\User\Session($appInstance);

    // Reuse list permission for now; optionally define a dedicated permission later
    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_MAIL_SEND_MASS_MAIL, $session);

    // Validate template
    if (!MailTemplate::exists((int) $id)) {
        $appInstance->BadRequest('Mail template does not exist', ['error_code' => 'MAIL_TEMPLATE_DOES_NOT_EXIST']);

        return;
    }
    $tpl = MailTemplate::get((int) $id);
    if (!$tpl || ($tpl['active'] ?? 'false') !== 'true') {
        $appInstance->BadRequest('Mail template is inactive or invalid', ['error_code' => 'MAIL_TEMPLATE_INACTIVE']);

        return;
    }

    // Retrieve all users with uuids and valid emails
    $users = MythicalDash\Chat\User\User::getListWithFilters([
        UserColumns::UUID,
        UserColumns::EMAIL,
    ], []);

    $queued = 0;
    foreach ($users as $user) {
        $email = $user['email'] ?? null;
        $uuid = $user['uuid'] ?? null;
        if (!$uuid || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        // Queue email via MailList -> MailQueue
        if (MythicalDash\Chat\Mails\MailList::addEmail((string) ($tpl['subject'] ?? 'Notification'), (string) ($tpl['body'] ?? ''), (string) $uuid)) {
            ++$queued;
        }
    }

    UserActivities::add(
        $session->getInfo(UserColumns::UUID, false),
        UserActivitiesTypes::$mail_template_update,
        CloudFlareRealIP::getRealIP(),
        "Mass mailed template $id to $queued users"
    );

    $appInstance->OK('Mass email queued successfully.', [
        'queued' => $queued,
        'template' => (int) $id,
    ]);
});
