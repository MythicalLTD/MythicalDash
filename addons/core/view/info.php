<?php
use MythicalDash\Database\Connect;
use MythicalDash\SettingsManager;
use MythicalDash\ErrorHandler;

$conn = new Connect();
$conn = $conn->connectToDatabase();
header('Content-type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
if (!is_writable(__DIR__)) {
    http_response_code(500);
    $rsp = array(
        'code' => 500,
        'error' => 'The server is not ready to handle the request.',
        'message' => 'We have no write permission for our home directory. Please update the permission by executing this in the server shell: chown -R www-data:www-data /var/www/mythicaldash/ && chown -R www-data:www-data /var/www/mythicaldash/*'
    );
    die (json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

try {
    $rsp = array(
        'code' => 200,
        'error' => null,
        'data' => array(
            'dash_name' => SettingsManager::getSetting('name'),
            'dash_version' => SettingsManager::getSetting('version'),
            'security_modules' => array(
                'turnstile' => SettingsManager::getSetting('enable_turnstile'),
                'anti-adblocker' => SettingsManager::getSetting('enable_adblocker_detection'),
                'anti-alting' => SettingsManager::getSetting('enable_alting'),
                'anti-vpn' => SettingsManager::getSetting('enable_anti_vpn'),
                'purge' => SettingsManager::getSetting("server_purge")
            ),
            'stripe' => array(
                'enabled' => SettingsManager::getSetting("enable_stripe"),
                'public-key' => SettingsManager::getSetting("stripe_publishable_key"),
                'currency' => SettingsManager::getSetting("stripe_currency"),
            ),
            'linkvertise' => array(
                'enabled' => SettingsManager::getSetting("linkvertise_enabled"),
                'code' => SettingsManager::getSetting("linkvertise_code"),
                'coins' => SettingsManager::getSetting("linkvertise_coins")
            ),
            'pterodactyl' => array(
                'url' => SettingsManager::getSetting("PterodactylURL")
            ),
            'smtp' => array(
                'enabled' => SettingsManager::getSetting("enable_smtp"),
                'encryption' => SettingsManager::getSetting("smtpSecure"),
                'port' => SettingsManager::getSetting("smtpPort"),
                'from' => SettingsManager::getSetting("fromEmail")
            ),
            'discord' => array(
                'account_link' => SettingsManager::getSetting("enable_discord_link"),
                'server_id' => SettingsManager::getSetting("discord_serverid"),
                "client_id" => SettingsManager::getSetting("discord_clientid")
            )
        ), 
    );
    $conn->close();
    http_response_code(200);
    die (json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
} catch (Exception $e) {
    ErrorHandler::Critical('Settings DB ', $e);
    $rsp = array(
        'code' => 500,
        'error' => "The server encountered a situation it doesn't know how to handle.",
        'message' => 'We are sorry, but our server cannot handle this request. Please do not try again!'
    );
    http_response_code(500);
    die (json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>
