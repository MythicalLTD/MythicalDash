<?php 
require("../../../core/require/sql.php");
header('Content-type: application/json');
ini_set("display_errors", 1);
if (!$cpconn->ping()) {
    $data = array(
        "error" => "Host can't reach the MySQL db",
        "status" => array(
            "client" => "Unknown",
            "panel" => "Unknown",
            "mysql" => "FAILED"
        )
    );
    header('HTTP/1.1 500 MYSQL ERROR');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}



if (!isset($_GET['api_key'])) {
    $data = array(
        "error" => "No API key was specified"
    );
    header('HTTP/1.1 402 NO API KEY');

    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$apikey = $cpconn->query("SELECT * FROM api_keys WHERE skey = '".mysqli_real_escape_string($cpconn, $_GET['api_key'])."'");

if ($apikey->num_rows == 0) {
    $data = array(
        "error" => "API key can't be found"
    );
    header('HTTP/1.1 401 BAD API KEY');

    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

if (!isset($_GET['email'])) {
    $data = array(
        "error" => "Some information was not given"
    );
    header('HTTP/1.1 403 MISSING INFO');

    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$user = $cpconn->query("SELECT * FROM users WHERE email = '".mysqli_real_escape_string($cpconn, $_GET['email'])."'");

if ($user->num_rows == 0) {
    $data = array(
        "error" => "The info was not found in the db or the API dose not exist"
    );
    header('HTTP/1.1 404 NOT FOUND');

    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

if (!isset($_GET['reason'])) {
    $data = array(
        "error" => "The info was not found in the db or the API dose not exist"
    );
    header('HTTP/1.1 404 NOT FOUND');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$user = $cpconn->query("SELECT * FROM users WHERE email = '".mysqli_real_escape_string($cpconn, $_GET['email'])."'");

if ($user->num_rows == 0) {
    $data = array(
        "error" => "The info was not found in the db or the API dose not exist"
    );
    header('HTTP/1.1 404 NOT FOUND');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$user = $user->fetch_object();
$cpconn->query("UPDATE `users` SET `banned` = '0' WHERE `users`.`email` = '".$_GET['email']."';");
$cpconn->query("UPDATE `users` SET `banned_reason` = NULL WHERE `users`.`email` = '".$_GET['email']."';");
$data = array(
    "error" => null
);
header('HTTP/1.1 200 OK');
die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>