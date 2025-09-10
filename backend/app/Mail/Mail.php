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

namespace MythicalDash\Mail;

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Mail\services\SMTPServer;

class Mail
{
    /**
     * Send an email.
     *
     * @param string $to The email address of the recipient
     * @param string $subject The subject of the email
     * @param string $message The message of the email
     */
    public static function send(string $to, string $subject, string $message): void
    {
        // TODO: Add more drivers
        $appInstance = App::getInstance(true);

        if (!self::isEnabled()) {
            return;
        }

        try {
            $appInstance->getLogger()->debug('Sending email to ' . $to);
            SMTPServer::send($to, $subject, $message);
            $appInstance->getLogger()->debug('Email sent to ' . $to);
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('(' . APP_SOURCECODE_DIR . '/Mail/Mail.php) [send] Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Is the SMTP server enabled?
     */
    public static function isEnabled(): bool
    {
        $config = new ConfigFactory(Database::getPdoConnection());

        if ($config->getDBSetting(ConfigInterface::SMTP_ENABLED, 'false') == 'true') {
            return true;
        }

        return false;

    }

    public static function processEmailTemplateGlobal(string $template): string
    {
        $config = new ConfigFactory(Database::getPdoConnection());

        // Get config values with null checks
        $appName = $config->getDBSetting(ConfigInterface::APP_NAME, 'MythicalSystems') ?? 'MythicalSystems';
        $appUrl = $config->getDBSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems') ?? 'https://mythicaldash-v3.mythical.systems';
        $appLogo = $config->getDBSetting(ConfigInterface::APP_LOGO, 'https://github.com/mythicalltd.png') ?? 'https://github.com/mythicalltd.png';
        $appLang = $config->getDBSetting(ConfigInterface::APP_LANG, 'en_US') ?? 'en_US';
        $appTimezone = $config->getDBSetting(ConfigInterface::APP_TIMEZONE, 'UTC') ?? 'UTC';
        $appVersion = $config->getDBSetting(ConfigInterface::APP_VERSION, '1.0.0') ?? '1.0.0';

        if (strpos($appUrl, 'https://') !== 0) {
            $appUrl = 'https://' . $appUrl;
        }

        // Replace template variables with config values
        $template = str_replace('${app_name}', (string) $appName, $template);
        $template = str_replace('${app_url}', (string) $appUrl, $template);
        $template = str_replace('${app_logo}', (string) $appLogo, $template);
        $template = str_replace('${app_lang}', (string) $appLang, $template);
        $template = str_replace('${app_timezone}', (string) $appTimezone, $template);
        $template = str_replace('${app_version}', (string) $appVersion, $template);

        return $template;
    }
}
