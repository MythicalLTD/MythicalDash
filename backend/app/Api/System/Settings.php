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

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;

$router->add('/api/system/settings', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    $settings = [
        ConfigInterface::APP_NAME => $config->getSetting(ConfigInterface::APP_NAME, 'MythicalDash'),
        ConfigInterface::APP_LANG => $config->getSetting(ConfigInterface::APP_LANG, 'en_US'),
        ConfigInterface::APP_URL => $config->getSetting(ConfigInterface::APP_URL, 'framework.mythical.systems'),
        ConfigInterface::APP_VERSION => $config->getSetting(ConfigInterface::APP_VERSION, '1.0.0'),
        ConfigInterface::APP_TIMEZONE => $config->getSetting(ConfigInterface::APP_TIMEZONE, 'UTC'),
        ConfigInterface::APP_LOGO => $config->getSetting(ConfigInterface::APP_LOGO, 'https://github.com/mythicalltd.png'),
        ConfigInterface::SEO_DESCRIPTION => $config->getSetting(ConfigInterface::SEO_DESCRIPTION, 'Change this in the settings area!'),
        ConfigInterface::SEO_KEYWORDS => $config->getSetting(ConfigInterface::SEO_KEYWORDS, 'some,random,keywords'),
        ConfigInterface::TURNSTILE_ENABLED => $config->getSetting(ConfigInterface::TURNSTILE_ENABLED, 'false'),
        ConfigInterface::TURNSTILE_KEY_PUB => $config->getSetting(ConfigInterface::TURNSTILE_KEY_PUB, 'XXXX'),

        ConfigInterface::LEGAL_TOS => $config->getSetting(ConfigInterface::LEGAL_TOS, '/tos'),
        ConfigInterface::LEGAL_PRIVACY => $config->getSetting(ConfigInterface::LEGAL_PRIVACY, '/privacy'),

        ConfigInterface::PTERODACTYL_BASE_URL => $config->getSetting(ConfigInterface::PTERODACTYL_BASE_URL, 'https://demopanel.mythical.systems'),

        ConfigInterface::AFK_ENABLED => $config->getSetting(ConfigInterface::AFK_ENABLED, 'false'),
        ConfigInterface::AFK_MIN_PER_COIN => $config->getSetting(ConfigInterface::AFK_MIN_PER_COIN, '1'),

        ConfigInterface::CODE_REDEMPTION_ENABLED => $config->getSetting(ConfigInterface::CODE_REDEMPTION_ENABLED, 'false'),

        ConfigInterface::J4R_ENABLED => $config->getSetting(ConfigInterface::J4R_ENABLED, 'false'),

        ConfigInterface::REFERRALS_ENABLED => $config->getSetting(ConfigInterface::REFERRALS_ENABLED, 'false'),

        ConfigInterface::L4R_ENABLED => $config->getSetting(ConfigInterface::L4R_ENABLED, 'false'),

        ConfigInterface::L4R_LINKVERTISE_ENABLED => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_ENABLED, 'false'),
        ConfigInterface::L4R_SHAREUS_ENABLED => $config->getSetting(ConfigInterface::L4R_SHAREUS_ENABLED, 'false'),
        ConfigInterface::L4R_LINKPAYS_ENABLED => $config->getSetting(ConfigInterface::L4R_LINKPAYS_ENABLED, 'false'),
        ConfigInterface::L4R_GYANILINKS_ENABLED => $config->getSetting(ConfigInterface::L4R_GYANILINKS_ENABLED, 'false'),

        ConfigInterface::L4R_LINKVERTISE_USER_ID => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_USER_ID, ''),
        ConfigInterface::L4R_LINKVERTISE_COINS_PER_LINK => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_COINS_PER_LINK, '100'),
        ConfigInterface::L4R_LINKVERTISE_DAILY_LIMIT => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_DAILY_LIMIT, '5'),
        ConfigInterface::L4R_LINKVERTISE_MIN_TIME_TO_COMPLETE => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_MIN_TIME_TO_COMPLETE, '60'),
        ConfigInterface::L4R_LINKVERTISE_TIME_TO_EXPIRE => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_TIME_TO_EXPIRE, '3600'),
        ConfigInterface::L4R_LINKVERTISE_COOLDOWN_TIME => $config->getSetting(ConfigInterface::L4R_LINKVERTISE_COOLDOWN_TIME, '3600'),

        ConfigInterface::L4R_SHAREUS_API_KEY => $config->getSetting(ConfigInterface::L4R_SHAREUS_API_KEY, ''),
        ConfigInterface::L4R_SHAREUS_COINS_PER_LINK => $config->getSetting(ConfigInterface::L4R_SHAREUS_COINS_PER_LINK, '100'),
        ConfigInterface::L4R_SHAREUS_DAILY_LIMIT => $config->getSetting(ConfigInterface::L4R_SHAREUS_DAILY_LIMIT, '5'),
        ConfigInterface::L4R_SHAREUS_MIN_TIME_TO_COMPLETE => $config->getSetting(ConfigInterface::L4R_SHAREUS_MIN_TIME_TO_COMPLETE, '60'),
        ConfigInterface::L4R_SHAREUS_TIME_TO_EXPIRE => $config->getSetting(ConfigInterface::L4R_SHAREUS_TIME_TO_EXPIRE, '3600'),
        ConfigInterface::L4R_SHAREUS_COOLDOWN_TIME => $config->getSetting(ConfigInterface::L4R_SHAREUS_COOLDOWN_TIME, '3600'),

        ConfigInterface::L4R_LINKPAYS_API_KEY => $config->getSetting(ConfigInterface::L4R_LINKPAYS_API_KEY, ''),
        ConfigInterface::L4R_LINKPAYS_COINS_PER_LINK => $config->getSetting(ConfigInterface::L4R_LINKPAYS_COINS_PER_LINK, '100'),
        ConfigInterface::L4R_LINKPAYS_DAILY_LIMIT => $config->getSetting(ConfigInterface::L4R_LINKPAYS_DAILY_LIMIT, '5'),
        ConfigInterface::L4R_LINKPAYS_MIN_TIME_TO_COMPLETE => $config->getSetting(ConfigInterface::L4R_LINKPAYS_MIN_TIME_TO_COMPLETE, '60'),
        ConfigInterface::L4R_LINKPAYS_TIME_TO_EXPIRE => $config->getSetting(ConfigInterface::L4R_LINKPAYS_TIME_TO_EXPIRE, '3600'),
        ConfigInterface::L4R_LINKPAYS_COOLDOWN_TIME => $config->getSetting(ConfigInterface::L4R_LINKPAYS_COOLDOWN_TIME, '3600'),

        ConfigInterface::L4R_GYANILINKS_API_KEY => $config->getSetting(ConfigInterface::L4R_GYANILINKS_API_KEY, ''),
        ConfigInterface::L4R_GYANILINKS_COINS_PER_LINK => $config->getSetting(ConfigInterface::L4R_GYANILINKS_COINS_PER_LINK, '100'),
        ConfigInterface::L4R_GYANILINKS_DAILY_LIMIT => $config->getSetting(ConfigInterface::L4R_GYANILINKS_DAILY_LIMIT, '5'),
        ConfigInterface::L4R_GYANILINKS_MIN_TIME_TO_COMPLETE => $config->getSetting(ConfigInterface::L4R_GYANILINKS_MIN_TIME_TO_COMPLETE, '60'),
        ConfigInterface::L4R_GYANILINKS_TIME_TO_EXPIRE => $config->getSetting(ConfigInterface::L4R_GYANILINKS_TIME_TO_EXPIRE, '3600'),
        ConfigInterface::L4R_GYANILINKS_COOLDOWN_TIME => $config->getSetting(ConfigInterface::L4R_GYANILINKS_COOLDOWN_TIME, '3600'),

        ConfigInterface::STORE_ENABLED => $config->getSetting(ConfigInterface::STORE_ENABLED, 'false'),
        ConfigInterface::STORE_RAM_PRICE => $config->getSetting(ConfigInterface::STORE_RAM_PRICE, '100'),
        ConfigInterface::STORE_DISK_PRICE => $config->getSetting(ConfigInterface::STORE_DISK_PRICE, '100'),
        ConfigInterface::STORE_CPU_PRICE => $config->getSetting(ConfigInterface::STORE_CPU_PRICE, '100'),
        ConfigInterface::STORE_PORTS_PRICE => $config->getSetting(ConfigInterface::STORE_PORTS_PRICE, '100'),
        ConfigInterface::STORE_DATABASES_PRICE => $config->getSetting(ConfigInterface::STORE_DATABASES_PRICE, '100'),
        ConfigInterface::STORE_BACKUPS_PRICE => $config->getSetting(ConfigInterface::STORE_BACKUPS_PRICE, '100'),
        ConfigInterface::STORE_SERVER_SLOT_PRICE => $config->getSetting(ConfigInterface::STORE_SERVER_SLOT_PRICE, '100'),

        ConfigInterface::SMTP_ENABLED => $config->getSetting(ConfigInterface::SMTP_ENABLED, 'false'),
    ];

    App::OK('Sure here are the settings you were looking for', ['settings' => $settings]);
});
