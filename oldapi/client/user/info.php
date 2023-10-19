<?php
include(__DIR__ . '/../../base.php');
include(__DIR__ . '/../base.php');
$api_key = mysqli_real_escape_string($conn, $_GET['api_key']);
$userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . $api_key . "'")->fetch_array();
$rsp = array(
    "code" => 200,
    "error" => null,
    "info" => array(
        "database_id" => $userdb['id'],
        "username" => $userdb['username'],
        "email" => $userdb['email'],
        "first_name" => $userdb['first_name'],
        "last_name" => $userdb['last_name'],
        "avatar" => $userdb['avatar'],
        "role" => $userdb['role'],
        "banned" => $userdb['banned'],
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
    ),
);
$conn->close();
http_response_code(200);
die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>