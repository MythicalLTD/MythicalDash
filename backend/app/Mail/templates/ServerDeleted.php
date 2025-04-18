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

namespace MythicalDash\Mail\Templates;

use MythicalDash\App;
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Mails;

use MythicalDash\Chat\columns\UserColumns;

class ServerDeleted extends Mail
{
    public static function sendMail(string $uuid): void
    {
        try {
            $template = self::getFinalTemplate($uuid);
            $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
            Mails::add('Server Deleted', $template, $uuid);
            self::send($email, 'Server Deleted', $template);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerDeleted.php) [sendMail] Failed to send email: ' . $e->getMessage());
        }
    }

    private static function getFinalTemplate(string $uuid): string
    {
        return self::processTemplate(self::getTemplate(), $uuid);
    }

    private static function getTemplate(): ?string
    {
        try {
            $conn = Database::getPdoConnection();
            $query = $conn->prepare('SELECT content FROM mythicaldash_mail_templates WHERE name = :name');
            $query->execute(['name' => 'server_deleted']);
            $template = $query->fetchColumn();

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerDeleted.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return null;
        }
    }

    private static function processTemplate(string $template, string $uuid): string
    {
        try {
            $template = self::getTemplate();
            $template = User::processTemplate($template, $uuid);
            $template = Mail::processEmailTemplateGlobal($template);

            $template = str_replace('${deletion_date}', date('Y-m-d'), $template);

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerDeleted.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return '';
        }
    }
}
