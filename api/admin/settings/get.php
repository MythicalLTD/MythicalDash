<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;

include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $rsp = array(
            "code" => 200,
            "error" => null,
            "data" => array(
                "name" => SettingsManager::getSetting("name"),
                "logo" => SettingsManager::getSetting("logo"),
                "timezone" => SettingsManager::getSetting("timezone"),
                "language" => SettingsManager::getSetting("lang"),
                "seo" => array(
                    "description" => SettingsManager::getSetting("seo_description"),
                    "keywords" => SettingsManager::getSetting("seo_keywords"),
                ),
                "turnstile" => array(
                    "enabled" => SettingsManager::getSetting("enable_turnstile"),
                    "sitekey" => SettingsManager::getSetting("turnstile_sitekey"),
                    "secretkey" => SettingsManager::getSetting("turnstile_secretkey"),
                ),
                "discord" => array(
                    "enabled" => SettingsManager::getSetting("enable_discord_link"),
                    "invite" => SettingsManager::getSetting("discord_invite"),
                    "serverid" => SettingsManager::getSetting("discord_serverid"),
                    "clientid" => SettingsManager::getSetting("discord_clientid"),
                    "clientsecret" => SettingsManager::getSetting("discord_clientsecret"),
                    "webhook" => SettingsManager::getSetting("discord_webhook"),
                ),
                "mailserver" => array(
                    "enabled" => SettingsManager::getSetting("enable_smtp"),
                    "host" => SettingsManager::getSetting("smtpHost"),
                    "port" => SettingsManager::getSetting("smtpPort"),
                    "encryption" => SettingsManager::getSetting("smtpSecure"),
                    "username" => SettingsManager::getSetting("smtpUsername"),
                    "password" => SettingsManager::getSetting("smtpPassword"),
                    "email" => SettingsManager::getSetting("fromEmail"),
                ),
                "resources" => array(
                    "default" => array(
                        "coins" => SettingsManager::getSetting("def_coins"),
                        "ram" => SettingsManager::getSetting("def_memory"),
                        "disk" => SettingsManager::getSetting("def_disk_space"),
                        "cpu" => SettingsManager::getSetting("def_cpu"),
                        "slots" => SettingsManager::getSetting("def_server_limit"),
                        "allocations" => SettingsManager::getSetting("def_port"),
                        "databases" => SettingsManager::getSetting("def_db"),
                        "backups" => SettingsManager::getSetting("def_backups"),
                    ),
                    "maximum" => array(
                        "ram" => SettingsManager::getSetting("max_ram"),
                        "disk" => SettingsManager::getSetting("max_disk"),
                        "cpu" => SettingsManager::getSetting("max_cpu"),
                        "slots" => SettingsManager::getSetting("max_servers"),
                        "allocations" => SettingsManager::getSetting("max_allocations"),
                        "databases" => SettingsManager::getSetting("max_dbs"),
                        "backups" => SettingsManager::getSetting("max_backups"),
                    ),
                    "prices" => array(
                        "message" => "Please note that every thing from this list is per 1gb / per 1 core / per one slot etc!",
                        "ram" => SettingsManager::getSetting("price_memory"),
                        "disk" => SettingsManager::getSetting("price_disk_space"),
                        "cpu" => SettingsManager::getSetting("price_cpu"),
                        "slots" => SettingsManager::getSetting("price_server_limit"),
                        "allocations" => SettingsManager::getSetting("price_allocation"),
                        "databases" => SettingsManager::getSetting("price_database"),
                        "backups" => SettingsManager::getSetting("price_backup"),
                    )
                ),
                "pterodactyl" => array(
                    "url" => SettingsManager::getSetting("PterodactylURL"),
                    "key" => SettingsManager::getSetting("PterodactylAPIKey"),
                ),
                "linkvertise" => array(
                    "enabled"=> SettingsManager::getSetting("linkvertise_enabled"),
                    "code"=> SettingsManager::getSetting("linkvertise_code"),
                    "coins_per_link"=> SettingsManager::getSetting("linkvertise_coins"),
                ),
                "stripe" => array (
                    "enabled"=> SettingsManager::getSetting("enable_stripe"),
                    "public_key" => SettingsManager::getSetting("stripe_publishable_key"),
                    "secret_key" => SettingsManager::getSetting("stripe_secret_key"),
                ),
                "paypal" => array (
                    "enabled"=> SettingsManager::getSetting("enable_paypal"),
                    "client_id" => SettingsManager::getSetting("paypal_client_id"),
                    "client_secret" => SettingsManager::getSetting("paypal_client_secret"),
                ),
                "mythicaldash" => array(
                    "version" => SettingsManager::getSetting("version"),
                    "encryption_key" => APP_ENCRYPTION_KEY,
                ),
                "security_modules" => array (
                    "enable_alting" => SettingsManager::getSetting("enable_alting"),
                    "enable_anti_vpn" => SettingsManager::getSetting("enable_anti_vpn"),
                    "maintenance" => SettingsManager::getSetting("maintenance"),
                ), 
                "payments" => array(
                    "payments" => SettingsManager::getSetting("allow_payments"),
                    "currency" => SettingsManager::getSetting("payments_currency"),
                    "per_coin_value" => SettingsManager::getSetting("coin_per_balance"),
                ),
                "other" => array(
                    "show_snow" => SettingsManager::getSetting("show_snow"),
                    "landingpage" => SettingsManager::getSetting("landingpage"),
                    "background" => SettingsManager::getSetting("bg"),
                    "links" => array(
                        "status_page" => SettingsManager::getSetting("status"),
                        "x" => SettingsManager::getSetting("x"),
                    ),
                    "afk" => array(
                        "enabled" => SettingsManager::getSetting("enable_afk"),
                        "coins_per_minute" => SettingsManager::getSetting("afk_coins_per_min"),
                        "minute" => SettingsManager::getSetting("afk_min"),
                    ),
                    "ads" => array(
                        "enabled" => SettingsManager::getSetting("enable_ads"),
                        "code" => SettingsManager::getSetting("ads_code"),
                        "anti_adblock" => SettingsManager::getSetting("enable_adblocker_detection"),
                    ),
                    "custom" => array(
                        "css" => array(
                            "enabled" => SettingsManager::getSetting("customcss_enabled"),
                            "code" => SettingsManager::getSetting("customcss_code"),
                        ),
                        "header" => array(
                            "enabled"=> SettingsManager::getSetting("customhead_enabled"), 
                            "code"=> SettingsManager::getSetting("customhead_code"),
                        ),
                        "terms_of_service" => SettingsManager::getSetting("terms_of_service"),
                        "privacy_policy" => SettingsManager::getSetting("privacy_policy"),
                        "server_purge" => SettingsManager::getSetting("server_purge"),
                    ),
                ),
            ),
        );
        $conn->close();
        http_response_code(200);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        ErrorHandler::Critical("Settings DB ", $e);
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server cannot handle this request. Please do not try again!",
        );
        http_response_code(500);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 405,
        "error" => "A request was made of a page using a request method not supported by that page",
        "message" => "Please use a get request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>