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

namespace MythicalDash\Mail\services;

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;

class SMTPServer
{
    public static function send(string $to, string $subject, string $body)
    {
        $appInstance = App::getInstance(true);
        $appInstance->getLogger()->debug('Sending email to ' . $to);
        try {
            $config = new ConfigFactory(Database::getPdoConnection());
            if ($config->getDBSetting(ConfigInterface::SMTP_ENABLED, 'false') == 'true') {
                if (
                    $config->getDBSetting(ConfigInterface::SMTP_HOST, null) == null
                    || $config->getDBSetting(ConfigInterface::SMTP_PORT, null) == null
                    || $config->getDBSetting(ConfigInterface::SMTP_USER, null) == null
                    || $config->getDBSetting(ConfigInterface::SMTP_PASS, null) == null
                    || $config->getDBSetting(ConfigInterface::SMTP_FROM, null) == null
                ) {
                    $appInstance->getLogger()->info('Failed to send email, SMTP settings are not configured.');

                    return;
                }
                $mail = new \PHPMailer\PHPMailer\PHPMailer(false);
                try {
                    $mail->isSMTP();
                    $mail->Host = $config->getDBSetting(ConfigInterface::SMTP_HOST, null);
                    $mail->SMTPAuth = true;
                    $mail->Username = $config->getDBSetting(ConfigInterface::SMTP_USER, null);
                    $mail->Password = $config->getDBSetting(ConfigInterface::SMTP_PASS, null);
                    $mail->SMTPSecure = $config->getDBSetting(ConfigInterface::SMTP_ENCRYPTION, 'ssl');
                    $mail->Port = $config->getDBSetting(ConfigInterface::SMTP_PORT, null);
                    $mail->setFrom($config->getDBSetting(ConfigInterface::SMTP_FROM, null), $config->getDBSetting(ConfigInterface::APP_NAME, null));
                    $mail->addReplyTo($config->getDBSetting(ConfigInterface::SMTP_FROM, null), $config->getDBSetting(ConfigInterface::APP_NAME, null));
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->addAddress($to);
                    $mail->send();
                    $appInstance->getLogger()->debug('Email sent');
                } catch (\Exception $e) {
                    $appInstance->getLogger()->error('Failed to send email: ' . $e->getMessage());

                    return;
                }

            }
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('Failed to send email: ' . $e->getMessage());
        }
    }
}
