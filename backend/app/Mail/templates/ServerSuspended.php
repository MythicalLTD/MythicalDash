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
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Config\ConfigInterface;

use MythicalDash\Chat\columns\UserColumns;

class ServerSuspended extends Mail
{
    public static function sendMail(string $uuid, int $ptero_server_id, string $suspension_reason): void
    {
        try {
            $template = self::getFinalTemplate($uuid, $ptero_server_id, $suspension_reason);
            $email = User::getInfo(User::getTokenFromUUID($uuid), UserColumns::EMAIL, false);
            Mails::add('Server Suspended', $template, $uuid);
            self::send($email, 'Server Suspended', $template);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerSuspended.php) [sendMail] Failed to send email: ' . $e->getMessage());
        }
    }

    private static function getFinalTemplate(string $uuid, int $ptero_server_id, string $suspension_reason): string
    {
        return self::processTemplate(self::getTemplate(), $uuid, $ptero_server_id, $suspension_reason);
    }

    private static function getTemplate(): ?string
    {
        try {
            $conn = Database::getPdoConnection();
            $query = $conn->prepare('SELECT content FROM mythicaldash_mail_templates WHERE name = :name');
            $query->execute(['name' => 'server_suspended']);
            $template = $query->fetchColumn();

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerSuspended.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return null;
        }
    }

    private static function processTemplate(string $template, string $uuid, int $ptero_server_id, string $suspension_reason): string
    {
        try {
            $template = self::getTemplate();
            $config = App::getInstance(false, false)->getConfig();
            $template = User::processTemplate($template, $uuid);
            $template = Mail::processEmailTemplateGlobal($template);

            $template = str_replace('${renewal_amount}', $config->getSetting(ConfigInterface::SERVER_RENEW_COST, '10'), $template);
            $template = str_replace('${pterodactyl_id}', $ptero_server_id, $template);
            $template = str_replace('${expiration_date}', Server::getExpirationDate($ptero_server_id), $template);
            $template = str_replace('${suspension_reason}', $suspension_reason, $template);

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerSuspended.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return '';
        }
    }
}
