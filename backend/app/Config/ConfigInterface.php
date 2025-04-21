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

namespace MythicalDash\Config;

interface ConfigInterface
{
    /**
     * App.
     */
    public const APP_NAME = 'app_name';
    public const APP_LANG = 'app_lang';
    public const APP_URL = 'app_url';
    public const APP_VERSION = 'app_version';
    public const APP_TIMEZONE = 'app_timezone';
    public const APP_LOGO = 'app_logo';
    public const SEO_DESCRIPTION = 'seo_description';
    public const SEO_KEYWORDS = 'seo_keywords';
    /**
     * Turnstile.
     */
    public const TURNSTILE_ENABLED = 'turnstile_enabled';
    public const TURNSTILE_KEY_PUB = 'turnstile_key_pub';
    public const TURNSTILE_KEY_PRIV = 'turnstile_key_priv';
    /**
     * SMTP.
     */
    public const SMTP_ENABLED = 'smtp_enabled';
    public const SMTP_HOST = 'smtp_host';
    public const SMTP_PORT = 'smtp_port';
    public const SMTP_USER = 'smtp_user';
    public const SMTP_PASS = 'smtp_pass';
    public const SMTP_FROM = 'smtp_from';
    public const SMTP_ENCRYPTION = 'smtp_encryption';
    /**
     * Legal Values.
     */
    public const LEGAL_TOS = 'legal_tos_url';
    public const LEGAL_PRIVACY = 'legal_privacy_url';

    /**
     * Pterodactyl.
     */
    public const PTERODACTYL_API_KEY = 'pterodactyl_api_key';
    public const PTERODACTYL_BASE_URL = 'pterodactyl_base_url';

    /**
     * License.
     */
    public const LICENSE_KEY = 'license_key';

    /**
     * Earn.
     */
    public const AFK_ENABLED = 'afk_enabled';
    public const AFK_MIN_PER_COIN = 'afk_min_per_coin';

    /**
     * Code Redemption.
     */
    public const CODE_REDEMPTION_ENABLED = 'code_redemption_enabled';
    /**
     * Join For Rewards.
     */
    public const J4R_ENABLED = 'j4r_enabled';

    /**
     * Referrals.
     */
    public const REFERRALS_ENABLED = 'referrals_enabled';
    public const REFERRALS_COINS_PER_REFERRAL = 'referrals_coins_per_referral';
    public const REFERRALS_COINS_PER_REFERRAL_REDEEMER = 'referrals_coins_per_referral_redeemer';

    /**
     * Link For Rewards.
     */
    public const L4R_ENABLED = 'l4r_enabled';
    /**
     * Linkvertise Stuff.
     */
    public const L4R_LINKVERTISE_ENABLED = 'l4r_linkadvertise_enabled';
    public const L4R_LINKVERTISE_USER_ID = 'l4r_linkadvertise_user_id';
    public const L4R_LINKVERTISE_COINS_PER_LINK = 'l4r_linkadvertise_coins_per_link';
    public const L4R_LINKVERTISE_DAILY_LIMIT = 'l4r_linkadvertise_daily_limit';
    public const L4R_LINKVERTISE_MIN_TIME_TO_COMPLETE = 'l4r_linkadvertise_min_time_to_complete';
    public const L4R_LINKVERTISE_TIME_TO_EXPIRE = 'l4r_linkadvertise_time_to_expire';
    public const L4R_LINKVERTISE_COOLDOWN_TIME = 'l4r_linkadvertise_cooldown_time';
    /**
     * ShareUs Settings.
     */
    public const L4R_SHAREUS_ENABLED = 'l4r_shareus_enabled';
    public const L4R_SHAREUS_API_KEY = 'l4r_shareus_api_key';
    public const L4R_SHAREUS_COINS_PER_LINK = 'l4r_shareus_coins_per_link';
    public const L4R_SHAREUS_DAILY_LIMIT = 'l4r_shareus_daily_limit';
    public const L4R_SHAREUS_MIN_TIME_TO_COMPLETE = 'l4r_shareus_min_time_to_complete';
    public const L4R_SHAREUS_TIME_TO_EXPIRE = 'l4r_shareus_time_to_expire';
    public const L4R_SHAREUS_COOLDOWN_TIME = 'l4r_shareus_cooldown_time';

    /**
     * LinkPays Settings.
     */
    public const L4R_LINKPAYS_ENABLED = 'l4r_linkpays_enabled';
    public const L4R_LINKPAYS_API_KEY = 'l4r_linkpays_api_key';
    public const L4R_LINKPAYS_COINS_PER_LINK = 'l4r_linkpays_coins_per_link';
    public const L4R_LINKPAYS_DAILY_LIMIT = 'l4r_linkpays_daily_limit';
    public const L4R_LINKPAYS_MIN_TIME_TO_COMPLETE = 'l4r_linkpays_min_time_to_complete';
    public const L4R_LINKPAYS_TIME_TO_EXPIRE = 'l4r_linkpays_time_to_expire';
    public const L4R_LINKPAYS_COOLDOWN_TIME = 'l4r_linkpays_cooldown_time';

    /**
     * GyaniLinks Settings.
     */
    public const L4R_GYANILINKS_ENABLED = 'l4r_gyanilinks_enabled';
    public const L4R_GYANILINKS_API_KEY = 'l4r_gyanilinks_api_key';
    public const L4R_GYANILINKS_COINS_PER_LINK = 'l4r_gyanilinks_coins_per_link';
    public const L4R_GYANILINKS_DAILY_LIMIT = 'l4r_gyanilinks_daily_limit';
    public const L4R_GYANILINKS_MIN_TIME_TO_COMPLETE = 'l4r_gyanilinks_min_time_to_complete';
    public const L4R_GYANILINKS_TIME_TO_EXPIRE = 'l4r_gyanilinks_time_to_expire';
    public const L4R_GYANILINKS_COOLDOWN_TIME = 'l4r_gyanilinks_cooldown_time';

    /**
     * Store.
     */
    public const STORE_ENABLED = 'store_enabled';
    public const STORE_RAM_PRICE = 'store_ram_price';
    public const STORE_DISK_PRICE = 'store_disk_price';
    public const STORE_CPU_PRICE = 'store_cpu_price';
    public const STORE_PORTS_PRICE = 'store_ports_price';
    public const STORE_DATABASES_PRICE = 'store_databases_price';
    public const STORE_SERVER_SLOT_PRICE = 'store_server_slot_price';
    public const STORE_BACKUPS_PRICE = 'store_backups_price';

    /**
     * Misc.
     */
    public const WEBSITE_URL = 'website_url';
    public const STATUS_PAGE_URL = 'status_page_url';
    public const DISCORD_INVITE_URL = 'discord_invite_url';
    public const TWITTER_URL = 'twitter_url';
    public const GITHUB_URL = 'github_url';
    public const LINKEDIN_URL = 'linkedin_url';
    public const INSTAGRAM_URL = 'instagram_url';
    public const YOUTUBE_URL = 'youtube_url';
    public const TIKTOK_URL = 'tiktok_url';
    public const FACEBOOK_URL = 'facebook_url';
    public const REDDIT_URL = 'reddit_url';
    public const TELEGRAM_URL = 'telegram_url';
    public const WHATSAPP_URL = 'whatsapp_url';

    /**
     * Misc.
     */
    public const EARLY_SUPPORTERS_ENABLED = 'early_supporters_enabled';
    public const EARLY_SUPPORTERS_AMOUNT = 'early_supporters_amount';
    public const SHOW_NODE_PING = 'show_node_ping';

    /**
     * Credits Recharge.
     */
    public const CREDITS_RECHARGE_ENABLED = 'credits_recharge_enabled';
    public const COMPANY_NAME = 'company_name';
    public const COMPANY_ADDRESS = 'company_address';
    public const COMPANY_CITY = 'company_city';
    public const COMPANY_STATE = 'company_state';
    public const COMPANY_ZIP = 'company_zip';
    public const COMPANY_COUNTRY = 'company_country';
    public const COMPANY_VAT = 'company_vat';

    /**
     * Gateway Configs.
     */
    public const ENABLE_STRIPE = 'enable_stripe';
    public const ENABLE_PAYPAL = 'enable_paypal';

    /**
     * Paypal Configs.
     */
    public const PAYPAL_CLIENT_ID = 'paypal_email';
    public const PAYPAL_IS_SANDBOX = 'paypal_is_sandbox';

    /**
     * Stripe Configs.
     */
    public const STRIPE_SECRET_KEY = 'stripe_secret_key';
    public const STRIPE_PUBLISHABLE_KEY = 'stripe_publishable_key';
    public const STRIPE_WEBHOOK_ID = 'stripe_webhook_id';

    /**
     * Currency Configs.
     */
    public const CURRENCY = 'currency';
    public const CURRENCY_SYMBOL = 'currency_symbol';

    /**
     * Discord Integration.
     */
    public const DISCORD_ENABLED = 'discord_enabled';
    public const DISCORD_SERVER_ID = 'discord_server_id';
    public const DISCORD_CLIENT_ID = 'discord_client_id';
    public const DISCORD_CLIENT_SECRET = 'discord_client_secret';
    public const DISCORD_LINK_ALLOWED = 'discord_link_allowed';

    /**
     * Github Integration.
     */
    public const GITHUB_ENABLED = 'github_enabled';
    public const GITHUB_CLIENT_ID = 'github_client_id';
    public const GITHUB_CLIENT_SECRET = 'github_client_secret';
    public const GITHUB_LINK_ALLOWED = 'github_link_allowed';

    /**
     * Max Resources.
     */
    public const MAX_RAM = 'max_ram';
    public const MAX_DISK = 'max_disk';
    public const MAX_CPU = 'max_cpu';
    public const MAX_PORTS = 'max_ports';
    public const MAX_DATABASES = 'max_databases';
    public const MAX_SERVER_SLOTS = 'max_server_slots';
    public const MAX_BACKUPS = 'max_backups';
    /**
     * Default Resources.
     */
    public const DEFAULT_RAM = 'default_ram';
    public const DEFAULT_DISK = 'default_disk';
    public const DEFAULT_CPU = 'default_cpu';
    public const DEFAULT_PORTS = 'default_ports';
    public const DEFAULT_DATABASES = 'default_databases';
    public const DEFAULT_SERVER_SLOTS = 'default_server_slots';
    public const DEFAULT_BACKUPS = 'default_backups';

    /**
     * Block Resources.
     */
    public const BLOCK_RAM = 'block_ram';
    public const BLOCK_DISK = 'block_disk';
    public const BLOCK_CPU = 'block_cpu';
    public const BLOCK_PORTS = 'block_ports';
    public const BLOCK_DATABASES = 'block_databases';
    public const BLOCK_SERVER_SLOTS = 'block_server_slots';
    public const BLOCK_BACKUPS = 'block_backups';

    /**
     * Leaderboard Configs.
     */
    public const LEADERBOARD_ENABLED = 'leaderboard_enabled';
    public const LEADERBOARD_LIMIT = 'leaderboard_limit';

    /**
     * Allow Tickets.
     */
    public const ALLOW_TICKETS = 'allow_tickets';
    /**
     * Allow Servers.
     */
    public const ALLOW_SERVERS = 'allow_servers';

    /**
     * Allow Public Profiles.
     */
    public const ALLOW_PUBLIC_PROFILES = 'allow_public_profiles';

    /**
     * Allow Coins Sharing.
     */
    public const ALLOW_COINS_SHARING = 'allow_coins_sharing';
    public const COINS_SHARE_MAX_AMOUNT = 'coins_share_max_amount';
    public const COINS_SHARE_MIN_AMOUNT = 'coins_share_min_amount';
    public const COINS_SHARE_FEE = 'coins_share_fee';

    /**
     * Telemetry.
     */
    public const TELEMETRY_ENABLED = 'telemetry_enabled';
    public const MYTHICAL_ZERO_TRUST_ENABLED = 'zero_trust_enabled';
    public const MYTHICAL_ZERO_TRUST_SERVER_SCAN_TOOL_ENABLED = 'zero_trust_server_scan_tool_enabled';
    public const MYTHICAL_ZERO_TRUST_WHITELIST_IPS_ENABLED = 'zero_trust_whitelist_ips_enabled';
    public const MYTHICAL_ZERO_TRUST_BLOCK_TOR_ENABLED = 'zero_trust_block_tor_enabled';
    public const MYTHICAL_ZERO_TRUST_ENHANCED_LOGGING_ENABLED = 'zero_trust_enhanced_logging_enabled';

    /**
     * Server Renew.
     */
    public const SERVER_RENEW_ENABLED = 'server_renew_enabled';
    public const SERVER_RENEW_COST = 'server_renew_cost';
    public const SERVER_RENEW_DAYS = 'server_renew_days';
    public const SERVER_RENEW_SEND_MAIL = 'server_renew_send_mail';

}
