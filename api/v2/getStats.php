<?php
require("../../core/require/sql.php");
header('Content-type: application/json');
ini_set("display_errors", 0);


if (!$cpconn->ping()) {
    $data = array(
        "error" => "Can't connect to the mysql db",
    );
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}



if (!isset($_GET['api_key'])) {
    $data = array(
        "error" => "Api key was not found"
    );
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$apikey = $cpconn->query("SELECT * FROM api_keys WHERE skey = '".mysqli_real_escape_string($cpconn, $_GET['api_key'])."'");

if ($apikey->num_rows == 0) {
    $data = array(
        "error" => "Api key is wrong"
    );
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
$users = $cpconn->query("SELECT * FROM users");
$servers = $cpconn->query("SELECT * FROM servers");
$serversq = $cpconn->query("SELECT * FROM servers_queue");
$tickets = $cpconn->query("SELECT * FROM tickets");
$data = array(
    "users" => $users->num_rows,
    "servers" => $servers->num_rows,
    "servers_queue" => $serversq->num_rows,
    "tickets" => $tickets->num_rows
  );
  
die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>