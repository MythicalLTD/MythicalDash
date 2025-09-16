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

namespace MythicalDash\Mail\templates;

use MythicalDash\App;
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Config\ConfigInterface;

class ServerRenewReminder extends Mail
{
    public static function sendMail(string $uuid, int $ptero_server_id): void
    {
        try {
            $template = self::getFinalTemplate($uuid, $ptero_server_id);
            \MythicalDash\Chat\Mails\MailList::addEmail('Server Renewal Required - Expires in 7 Days', $template, $uuid);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerRenewReminder.php) [sendMail] Failed to send email: ' . $e->getMessage());
        }
    }

    private static function getFinalTemplate(string $uuid, int $ptero_server_id): string
    {
        return self::processTemplate(self::getTemplate(), $uuid, $ptero_server_id);
    }

    private static function getTemplate(): ?string
    {
        try {
            $conn = Database::getPdoConnection();
            $query = $conn->prepare('SELECT body FROM mythicaldash_mail_templates WHERE name = :name');
            $query->execute(['name' => 'server_renewal_reminder']);
            $template = $query->fetchColumn();

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerRenewReminder.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return null;
        }
    }

    private static function processTemplate(string $template, string $uuid, int $ptero_server_id): string
    {
        try {
            $template = self::getTemplate();
            $config = App::getInstance(false, false)->getConfig();
            $template = User::processTemplate($template, $uuid);
            $template = Mail::processEmailTemplateGlobal($template);

            $template = str_replace('${renewal_amount}', $config->getDBSetting(ConfigInterface::SERVER_RENEW_COST, '10'), $template);
            $template = str_replace('${pterodactyl_id}', $ptero_server_id, $template);
            $template = str_replace('${expiration_date}', Server::getExpirationDate($ptero_server_id), $template);

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/ServerRenewReminder.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return '';
        }
    }
}
