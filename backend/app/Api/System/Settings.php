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

    // Define settings configuration with defaults
    $settingsConfig = [
        // App settings
        ConfigInterface::APP_NAME => 'MythicalDash',
        ConfigInterface::APP_LANG => 'en_US',
        ConfigInterface::APP_URL => 'framework.mythical.systems',
        ConfigInterface::APP_VERSION => '1.0.0',
        ConfigInterface::APP_TIMEZONE => 'UTC',
        ConfigInterface::APP_LOGO => 'https://github.com/mythicalltd.png',
        ConfigInterface::SEO_DESCRIPTION => 'Change this in the settings area!',
        ConfigInterface::SEO_KEYWORDS => 'some,random,keywords',

        // Turnstile settings
        ConfigInterface::TURNSTILE_ENABLED => 'false',
        ConfigInterface::TURNSTILE_KEY_PUB => 'XXXX',

        // Legal links
        ConfigInterface::LEGAL_TOS => '/tos',
        ConfigInterface::LEGAL_PRIVACY => '/privacy',

        // Pterodactyl settings
        ConfigInterface::PTERODACTYL_BASE_URL => 'https://demopanel.mythical.systems',

        // AFK Settings
        ConfigInterface::AFK_ENABLED => 'false',
        ConfigInterface::AFK_MIN_PER_COIN => '1',

        // Feature toggles
        ConfigInterface::CODE_REDEMPTION_ENABLED => 'false',
        ConfigInterface::J4R_ENABLED => 'false',
        ConfigInterface::REFERRALS_ENABLED => 'false',
        ConfigInterface::L4R_ENABLED => 'false',

        // L4R platform toggles
        ConfigInterface::L4R_LINKVERTISE_ENABLED => 'false',
        ConfigInterface::L4R_SHAREUS_ENABLED => 'false',
        ConfigInterface::L4R_LINKPAYS_ENABLED => 'false',
        ConfigInterface::L4R_GYANILINKS_ENABLED => 'false',

        // L4R Linkvertise settings
        ConfigInterface::L4R_LINKVERTISE_USER_ID => '',
        ConfigInterface::L4R_LINKVERTISE_COINS_PER_LINK => '100',
        ConfigInterface::L4R_LINKVERTISE_DAILY_LIMIT => '5',
        ConfigInterface::L4R_LINKVERTISE_MIN_TIME_TO_COMPLETE => '60',
        ConfigInterface::L4R_LINKVERTISE_TIME_TO_EXPIRE => '3600',
        ConfigInterface::L4R_LINKVERTISE_COOLDOWN_TIME => '3600',

        // L4R ShareUs settings
        ConfigInterface::L4R_SHAREUS_API_KEY => '',
        ConfigInterface::L4R_SHAREUS_COINS_PER_LINK => '100',
        ConfigInterface::L4R_SHAREUS_DAILY_LIMIT => '5',
        ConfigInterface::L4R_SHAREUS_MIN_TIME_TO_COMPLETE => '60',
        ConfigInterface::L4R_SHAREUS_TIME_TO_EXPIRE => '3600',
        ConfigInterface::L4R_SHAREUS_COOLDOWN_TIME => '3600',

        // L4R LinkPays settings
        ConfigInterface::L4R_LINKPAYS_API_KEY => '',
        ConfigInterface::L4R_LINKPAYS_COINS_PER_LINK => '100',
        ConfigInterface::L4R_LINKPAYS_DAILY_LIMIT => '5',
        ConfigInterface::L4R_LINKPAYS_MIN_TIME_TO_COMPLETE => '60',
        ConfigInterface::L4R_LINKPAYS_TIME_TO_EXPIRE => '3600',
        ConfigInterface::L4R_LINKPAYS_COOLDOWN_TIME => '3600',

        // L4R GyaniLinks settings
        ConfigInterface::L4R_GYANILINKS_API_KEY => '',
        ConfigInterface::L4R_GYANILINKS_COINS_PER_LINK => '100',
        ConfigInterface::L4R_GYANILINKS_DAILY_LIMIT => '5',
        ConfigInterface::L4R_GYANILINKS_MIN_TIME_TO_COMPLETE => '60',
        ConfigInterface::L4R_GYANILINKS_TIME_TO_EXPIRE => '3600',
        ConfigInterface::L4R_GYANILINKS_COOLDOWN_TIME => '3600',

        // Store settings
        ConfigInterface::STORE_ENABLED => 'false',
        ConfigInterface::STORE_RAM_PRICE => '100',
        ConfigInterface::STORE_DISK_PRICE => '100',
        ConfigInterface::STORE_CPU_PRICE => '100',
        ConfigInterface::STORE_PORTS_PRICE => '100',
        ConfigInterface::STORE_DATABASES_PRICE => '100',
        ConfigInterface::STORE_BACKUPS_PRICE => '100',
        ConfigInterface::STORE_SERVER_SLOT_PRICE => '100',

        // Email settings
        ConfigInterface::SMTP_ENABLED => 'false',

        // Node settings
        ConfigInterface::SHOW_NODE_PING => 'false',

        // External URLs
        ConfigInterface::WEBSITE_URL => '',
        ConfigInterface::STATUS_PAGE_URL => '',
        ConfigInterface::DISCORD_INVITE_URL => '',
        ConfigInterface::TWITTER_URL => '',
        ConfigInterface::GITHUB_URL => '',
        ConfigInterface::LINKEDIN_URL => '',
        ConfigInterface::INSTAGRAM_URL => '',
        ConfigInterface::YOUTUBE_URL => '',
        ConfigInterface::TIKTOK_URL => '',
        ConfigInterface::FACEBOOK_URL => '',
        ConfigInterface::REDDIT_URL => '',
        ConfigInterface::TELEGRAM_URL => '',
        ConfigInterface::WHATSAPP_URL => '',

        // Support settings
        ConfigInterface::EARLY_SUPPORTERS_ENABLED => 'false',
        ConfigInterface::EARLY_SUPPORTERS_AMOUNT => '100',

        // Company settings
        ConfigInterface::COMPANY_NAME => 'Mythical Systems',
        ConfigInterface::COMPANY_ADDRESS => '1234 Main St, Anytown, USA',
        ConfigInterface::COMPANY_CITY => 'Anytown',
        ConfigInterface::COMPANY_STATE => 'CA',
        ConfigInterface::COMPANY_ZIP => '12345',
        ConfigInterface::COMPANY_COUNTRY => 'USA',
        ConfigInterface::COMPANY_VAT => '1234567890',

        // Gateway settings
        ConfigInterface::CREDITS_RECHARGE_ENABLED => 'false',
        ConfigInterface::ENABLE_STRIPE => 'false',
        ConfigInterface::ENABLE_PAYPAL => 'false',
        ConfigInterface::PAYPAL_CLIENT_ID => '',
        ConfigInterface::PAYPAL_IS_SANDBOX => 'false',
        ConfigInterface::STRIPE_PUBLISHABLE_KEY => '',

        // Currency settings
        ConfigInterface::CURRENCY => 'EUR',
        ConfigInterface::CURRENCY_SYMBOL => '€',

        // Discord Integration
        ConfigInterface::DISCORD_ENABLED => 'false',
        ConfigInterface::DISCORD_SERVER_ID => '',
        ConfigInterface::DISCORD_CLIENT_ID => '',
        ConfigInterface::DISCORD_LINK_ALLOWED => 'false',
        ConfigInterface::DISCORD_REGISTER_ALLOWED => 'false',

        // Max Resources
        ConfigInterface::MAX_RAM => '1024',
        ConfigInterface::MAX_DISK => '1024',
        ConfigInterface::MAX_CPU => '100',
        ConfigInterface::MAX_PORTS => '2',
        ConfigInterface::MAX_DATABASES => '1',
        ConfigInterface::MAX_SERVER_SLOTS => '1',
        ConfigInterface::MAX_BACKUPS => '1',

        // Referrals
        ConfigInterface::REFERRALS_COINS_PER_REFERRAL => '35',
        ConfigInterface::REFERRALS_COINS_PER_REFERRAL_REDEEMER => '15',

        // Default Resources
        ConfigInterface::DEFAULT_RAM => '1024',
        ConfigInterface::DEFAULT_DISK => '1024',
        ConfigInterface::DEFAULT_CPU => '100',
        ConfigInterface::DEFAULT_PORTS => '2',
        ConfigInterface::DEFAULT_DATABASES => '1',
        ConfigInterface::DEFAULT_SERVER_SLOTS => '1',
        ConfigInterface::DEFAULT_BACKUPS => '5',
    ];

    // Retrieve actual settings with defaults in a single array map
    $settings = array_combine(
        array_keys($settingsConfig),
        array_map(
            function ($key, $default) use ($config) {
                return $config->getSetting($key, $default);
            },
            array_keys($settingsConfig),
            array_values($settingsConfig)
        )
    );

    App::OK('Sure here are the settings you were looking for', ['settings' => $settings]);
});
