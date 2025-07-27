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
