<?php
use MythicalDash\Encryption;
use MythicalDash\ErrorHandler;

include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . API_KEY . "'")->fetch_array();
        $rsp = array(
            "code" => 200,
            "error" => null,
            "message" => null,
            "data" => array(
                "info" => array(
                    "database_id" => $userdb['id'],
                    "pterodactyl_id" => $userdb["panel_id"],
                    "username" => $userdb['username'],
                    "email" => $userdb['email'],
                    "first_name" => Encryption::decrypt($userdb['first_name'], $ekey),
                    "last_name" => Encryption::decrypt($userdb['last_name'], $ekey),
                    "role" => $userdb['role'],
                    "banned" => $userdb['banned'],
                    "last_ip" => $userdb["last_ip"],
                    "first_ip" => $userdb["first_ip"],
                    "registred_at" => $userdb['registred']
                ),
                "resources" => array(
                    "coins" => $userdb['coins'],
                    "ram" => $userdb['ram'],
                    "disk" => $userdb['disk'],
                    "cpu" => $userdb['cpu'],
                    "server_limit" => $userdb['server_limit'],
                    "ports" => $userdb['ports'],
                    "databases" => $userdb['databases'],
                    "backups" => $userdb['backups'],
                    "minutes_afk" => $userdb["minutes_afk"],
                ),
                "discord" => array(
                    "linked" => $userdb["discord_linked"],
                    "discord_id" => $userdb["discord_id"],
                    "discord_username" => $userdb["discord_username"],
                    "discord_global_username" => $userdb["discord_global_username"],
                    "discord_email" => $userdb["discord_email"],
                ),
                "profile" => array(
                    "avatar" => $userdb['avatar'],
                    "banner" => $userdb['banner'],
                ),
            ),

        );
        $conn->close();
        http_response_code(200);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        ErrorHandler::Critical("User Info ", $e);
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