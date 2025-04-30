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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Chat\Database;
use MythicalDash\App as MainApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Hooks\LicenseSystem;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;

class Init extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();

        $appInstance = MainApp::getInstance(true, false);
        $appInstance->loadEnv();
        $db = new Database(
            $_ENV['DATABASE_HOST'],
            $_ENV['DATABASE_DATABASE'],
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD']
        );
        $config = new ConfigFactory($db->getPdo());

        $licenseKeyCfg = $config->getSetting(ConfigInterface::LICENSE_KEY, null);
        $appUrlCfg = $config->getSetting(ConfigInterface::APP_URL, null);

        $app->send('&7To use MythicalDash, you need a valid license key.');
        $app->send('&7You can obtain a license key from: &bhttps://mymythicalid.mythical.systems/');
        $app->send('&7Please enter your license key:');
        $licenseKey = readline('> ');

        if (empty($licenseKey)) {
            if (!empty($licenseKeyCfg)) {
                $licenseKey = $licenseKeyCfg;
            } else {
                $app->send('&cLicense key cannot be empty.');
                exit;
            }
        }

        $app->send('&7Please enter your application URL (e.g., https://panel.example.com):');
        $appUrl = readline('> ');

        if (empty($appUrl)) {
            if (!empty($appUrlCfg)) {
                $appUrl = $appUrlCfg;
            } else {
                $app->send('&cApplication URL cannot be empty.');
                exit;
            }
        }

        if (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
            if (!empty($appUrlCfg)) {
                $appUrl = $appUrlCfg;
            } else {
                $app->send('&cInvalid URL format.');
                exit;
            }
        }

        $config->setSetting(ConfigInterface::APP_URL, $appUrl);
        $config->setSetting(ConfigInterface::LICENSE_KEY, $licenseKey);

        $licenseSystem = new LicenseSystem();
        try {
            $data = $licenseSystem->validateLicense($licenseKey, $appUrl);

            $app->send('&7---------------------------------');
            $app->send('&7License Information:');
            $app->send('&7- Project: &e' . $data['data']['project_info']['name']);
            $app->send('&7- Description: &e' . $data['data']['project_info']['description']);
            $app->send('&7- Expires: &e' . $data['data']['license_key_info']['expires_at']);
            $app->send('&7---------------------------------');
            $app->send('&7Instance Information:');
            $app->send('&7- Company: &e' . $data['data']['instance']['companyName']);
            $app->send('&7- Website: &e' . $data['data']['instance']['companyWebsite']);
            $app->send('&7- Owner: &e' . $data['data']['instance']['ownerFirstName'] . ' ' . $data['data']['instance']['ownerLastName']);
            $app->send('&7- Primary Email: &e' . $data['data']['instance']['primaryEmail']);
            $app->send('&7- Support Email: &e' . $data['data']['instance']['supportEmail']);
            $app->send('&7- Abuse Email: &e' . $data['data']['instance']['abuseEmail']);
            $app->send('&7---------------------------------');
            if (!empty($data['data']['project_info']['features'])) {
                $app->send('&aThis is a license with the following features:');
                foreach ($data['data']['project_info']['features'] as $feature) {
                    $app->send('&7- &e' . $feature);
                }
            }
            $app->send('&7---------------------------------');

        } catch (\Exception $e) {
            $app->send('&c' . $e->getMessage());
            exit;
        }

        $app->send('&aApplication URL and license key saved successfully.');

    }

    public static function getDescription(): string
    {
        return 'Configure the application';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
