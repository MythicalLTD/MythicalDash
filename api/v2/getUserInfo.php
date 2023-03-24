<?php
require("../../core/require/sql.php");
header('Content-type: application/json');
ini_set("display_errors", 0);
if (!isset($_GET['user_id'])) {
    die("Email not set");
}
if (!is_numeric($_GET['user_id'])) {
    die("Invalid email");
}
$user = $cpconn->query("SELECT * FROM users WHERE user_id = '".mysqli_real_escape_string($cpconn, $_GET['user_id'])."'");
if ($user->num_rows == 0) {
    die(json_encode(array("error" => array("message"=>"The user is not registered on our systems."))));
}
$user = $user->fetch_object();
$data = array(
    "error" => null,
    "user" => array(
        "username" => $user->username,
        "avatar" => $user->avatar,
        "coins" => $user->coins,
        "resources" => array(
            "memory" => $user->memory,
            "disk" => $user->disk_space,
            "ports" => $user->ports,
            "databases" => $user->databases,
            "backups" => $user->backup_limit,
            "cpu" => $user->cpu
        ),
        "server_limit" => $user->server_limit,
    )
);
die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
