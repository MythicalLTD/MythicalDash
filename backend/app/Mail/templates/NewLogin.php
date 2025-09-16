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

use MythicalDash\Config\ConfigInterface;

class NewLogin extends Mail
{
    public static function sendMail(string $uuid): void
    {
        try {
            $template = self::getFinalTemplate($uuid);

            // Use the new MailList system to add email
            $appName = App::getInstance(true)->getConfig()->getDBSetting(ConfigInterface::APP_NAME, 'MythicalSystems');
            \MythicalDash\Chat\Mails\MailList::addEmail('New Login Detected - ' . $appName . ' Security Alert', $template, $uuid);

            // self::send($email, 'New Login Detected', $template);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/NewLogin.php) [sendMail] Failed to send email: ' . $e->getMessage());
        }
    }

    private static function getFinalTemplate(string $uuid): string
    {
        $template = self::getTemplate();
        if ($template === null) {
            throw new \Exception('Failed to load email template');
        }

        return self::processTemplate($template, $uuid);
    }

    private static function getTemplate(): ?string
    {
        try {
            $conn = Database::getPdoConnection();
            $query = $conn->prepare('SELECT body FROM mythicaldash_mail_templates WHERE name = :name');
            $query->execute(['name' => 'new_login']);
            $template = $query->fetchColumn();

            // If no template found, return a default template
            if (!$template) {
                return '<!doctype html><html lang="en"><meta charset="UTF-8"><meta content="width=device-width,initial-scale=1" name="viewport"><title>New Login Detected - ${app_name}</title><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;background-color:#1a103c;margin:0;padding:0}.container{max-width:600px;margin:20px auto;background-color:#fff;border-radius:8px;overflow:hidden}.header{background-color:#1a103c;color:#fff;text-align:center;padding:20px}.content{padding:20px}.button{display:inline-block;background-color:#6366f1;color:#fff;text-decoration:none;padding:10px 20px;border-radius:5px;margin-top:20px}.footer{background-color:#f4f4f4;text-align:center;padding:10px;font-size:12px;color:#666}</style><div class="container"><div class="header"><h1>${app_name}</h1></div><div class="content"><h2>New Login Detected</h2><p>Dear ${first_name} ${last_name},</p><p>We detected a new login to your ${app_name} account.</p><p>If this was you, you can safely ignore this email.</p><p>If you did not log in to your account, please secure your account immediately.</p></div><div class="footer"><p>© 2025 ${app_name}. All rights reserved.</p></div></div>';
            }

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/NewLogin.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return null;
        }
    }

    private static function processTemplate(string $template, string $uuid): string
    {
        try {
            $template = User::processTemplate($template, $uuid);
            $template = Mail::processEmailTemplateGlobal($template);

            return $template;
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/templates/NewLogin.php) [sendMail] Failed to process template: ' . $e->getMessage());

            return '';
        }
    }
}
