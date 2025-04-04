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
    public const APP_NAME = 'app_name';
    public const APP_LANG = 'app_lang';
    public const APP_URL = 'app_url';
    public const APP_VERSION = 'app_version';
    public const APP_TIMEZONE = 'app_timezone';
    public const APP_LOGO = 'app_logo';
    public const SEO_DESCRIPTION = 'seo_description';
    public const SEO_KEYWORDS = 'seo_keywords';
    public const TURNSTILE_ENABLED = 'turnstile_enabled';
    public const TURNSTILE_KEY_PUB = 'turnstile_key_pub';
    public const TURNSTILE_KEY_PRIV = 'turnstile_key_priv';
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

    public const CODE_REDEMPTION_ENABLED = 'code_redemption_enabled';

    public const J4R_ENABLED = 'j4r_enabled';

    /**
     * Referrals.
     */
    public const REFERRALS_ENABLED = 'referrals_enabled';

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
}
