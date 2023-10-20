<?php
include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $rsp = array(
            "code" => 200,
            "error" => null,
            "data" => array(
                "name" => $settings['name'],
                "logo" => $settings["logo"],
                "seo" => array(
                    "description" => $settings["seo_description"],
                    "keywords" => $settings["seo_keywords"],
                ),
                "turnstile" => array(
                    "enabled" => $settings["enable_turnstile"],
                    "sitekey" => $settings["turnstile_sitekey"],
                    "secretkey" => $settings["turnstile_secretkey"],
                ),
                "discord" => array(
                    "enabled" => $settings["enable_discord_link"],
                    "invite" => $settings["discord_invite"],
                    "serverid" => $settings["discord_serverid"],
                    "clientid" => $settings["discord_clientid"],
                    "clientsecret" => $settings["discord_clientsecret"],
                    "webhook" => $settings["discord_webhook"],
                ),
                "mailserver" => array(
                    "enabled" => $settings["enable_smtp"],
                    "host" => $settings["smtpHost"],
                    "port" => $settings["smtpPort"],
                    "encryption" => $settings["smtpSecure"],
                    "username" => $settings["smtpUsername"],
                    "password" => $settings["smtpPassword"],
                    "email" => $settings["fromEmail"],
                ),
                "pterodactyl" => array(
                    "url" => $settings["PterodactylURL"],
                    "key" => $settings["PterodactylAPIKey"],
                ),
                "mythicaldash" => array(
                    "version" => $settings["version"],
                )
            ),
        );
        $conn->close();
        http_response_code(200);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server can't handle this request. Please do not try again!"
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