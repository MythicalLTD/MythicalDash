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

namespace MythicalDash\Config;

class PublicConfig extends ConfigFactory
{
    /**
     * ⚠️ DANGER ZONE - HANDLE WITH EXTREME CAUTION ⚠️.
     *
     * This is a critical configuration section that defines default values for public settings.
     * Any changes made here will affect the entire application's behavior.
     *
     * IMPORTANT SECURITY CONSIDERATIONS:
     * - This is the ONLY place where default values should be modified
     * - All values defined here are PUBLIC and accessible from the frontend
     * - These values are visible to ALL users of the application
     * - The data is also collected and sent to telemetry services
     *
     * NEVER add sensitive information such as:
     * - API keys or tokens
     * - Passwords or credentials
     * - Private configuration values
     * - Internal system details
     *
     * Any sensitive data added here will be exposed publicly and could lead to
     * security vulnerabilities. Always use proper secure storage for sensitive values.
     *
     * @return array An array of public configuration defaults
     */
    public static function getPublicSettingsWithDefaults(): array
    {
        // Define settings configuration with defaults
        return [
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
            ConfigInterface::AFK_ENABLED => 'true',
            ConfigInterface::AFK_MIN_PER_COIN => '1',

            // Feature toggles
            ConfigInterface::CODE_REDEMPTION_ENABLED => 'true',
            ConfigInterface::J4R_ENABLED => 'false',
            ConfigInterface::REFERRALS_ENABLED => 'true',
            ConfigInterface::L4R_ENABLED => 'true',

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
            ConfigInterface::SHOW_NODE_PING => 'true',

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
            ConfigInterface::DISCORD_FORCE_JOIN_SERVER => 'false',

            // Github Integration
            ConfigInterface::GITHUB_ENABLED => 'false',
            ConfigInterface::GITHUB_CLIENT_ID => '',
            ConfigInterface::GITHUB_LINK_ALLOWED => 'false',

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

            // Block Resources
            ConfigInterface::BLOCK_RAM => 'false',
            ConfigInterface::BLOCK_DISK => 'false',
            ConfigInterface::BLOCK_CPU => 'false',
            ConfigInterface::BLOCK_PORTS => 'false',
            ConfigInterface::BLOCK_DATABASES => 'false',
            ConfigInterface::BLOCK_SERVER_SLOTS => 'false',
            ConfigInterface::BLOCK_BACKUPS => 'false',

            // Leaderboard
            ConfigInterface::LEADERBOARD_ENABLED => 'true',
            ConfigInterface::LEADERBOARD_LIMIT => '15',

            // Allow Tickets
            ConfigInterface::ALLOW_TICKETS => 'true',

            // Allow Servers
            ConfigInterface::ALLOW_SERVERS => 'true',

            // Allow Public Profiles
            ConfigInterface::ALLOW_PUBLIC_PROFILES => 'true',

            // Allow Coins Sharing
            ConfigInterface::ALLOW_COINS_SHARING => 'true',
            ConfigInterface::COINS_SHARE_MAX_AMOUNT => '100',
            ConfigInterface::COINS_SHARE_MIN_AMOUNT => '1',
            ConfigInterface::COINS_SHARE_FEE => '10',

            // Telemetry
            ConfigInterface::TELEMETRY_ENABLED => 'true',
            ConfigInterface::MYTHICAL_ZERO_TRUST_ENABLED => 'true',
            ConfigInterface::MYTHICAL_ZERO_TRUST_SERVER_SCAN_TOOL_ENABLED => 'false',
            ConfigInterface::MYTHICAL_ZERO_TRUST_WHITELIST_IPS_ENABLED => 'false',
            ConfigInterface::MYTHICAL_ZERO_TRUST_BLOCK_TOR_ENABLED => 'false',
            ConfigInterface::MYTHICAL_ZERO_TRUST_ENHANCED_LOGGING_ENABLED => 'false',

            // Server Renew
            ConfigInterface::SERVER_RENEW_ENABLED => 'false',
            ConfigInterface::SERVER_RENEW_COST => '100',
            ConfigInterface::SERVER_RENEW_DAYS => '30',
            ConfigInterface::SERVER_RENEW_SEND_MAIL => 'false',

            // Daily Backup
            ConfigInterface::DAILY_BACKUP_ENABLED => 'false',

            // Custom CSS and JS
            ConfigInterface::CUSTOM_CSS => '',
            ConfigInterface::CUSTOM_JS => '',

            // Firewall
            ConfigInterface::FIREWALL_ENABLED => 'true',
            ConfigInterface::FIREWALL_RATE_LIMIT => '100',
            ConfigInterface::FIREWALL_BLOCK_VPN => 'false',
            ConfigInterface::FIREWALL_BLOCK_ALTS => 'false',

            // Image Hosting
            ConfigInterface::IMAGE_HOSTING_ENABLED => 'false',
            ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE_ENABLED => 'true',
            ConfigInterface::IMAGE_HOSTING_COINS_PER_IMAGE => '1',
            ConfigInterface::IMAGE_HOSTING_MAX_FILE_SIZE => '1024',
            ConfigInterface::IMAGE_HOSTING_ALLOW_DOMAINS => 'false',

            // Google Ads
            ConfigInterface::GOOGLE_ADS_ENABLED => 'false',
            ConfigInterface::GOOGLE_ADS_CLIENT_ID => '',

            // Force Links
            ConfigInterface::FORCE_DISCORD_LINK => 'false',
            ConfigInterface::FORCE_GITHUB_LINK => 'false',
            ConfigInterface::FORCE_MAIL_LINK => 'false',
            ConfigInterface::FORCE_2FA => 'false',

            // Anti Adblocker
            ConfigInterface::ANTI_ADBLOCKER_ENABLED => 'false',

            // Default BG
            ConfigInterface::DEFAULT_BG => 'https://cdn.mythical.systems/mc.jpg',

            // Store
            ConfigInterface::STORE_RAM_QUANTITY => '1024',
            ConfigInterface::STORE_DISK_QUANTITY => '1024',
            ConfigInterface::STORE_CPU_QUANTITY => '100',
            ConfigInterface::STORE_ALLOCATION_QUANTITY => '2',
            ConfigInterface::STORE_DATABASES_QUANTITY => '1',
            ConfigInterface::STORE_SERVER_SLOT_QUANTITY => '1',
            ConfigInterface::STORE_BACKUPS_QUANTITY => '1',
        ];

    }
}
