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
                "pterodactyl" => array(
                    "url" => SettingsManager::getSetting("PterodactylURL"),
                    "key" => SettingsManager::getSetting("PterodactylAPIKey"),
                ),
                "mythicaldash" => array(
                    "version" => SettingsManager::getSetting("version"),
                )
            ),
        );
        $conn->close();
        http_response_code(200);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        ErrorHandler::Critical("Settings DB ",$e);
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server cannot handle this request. Please do not try again!"
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