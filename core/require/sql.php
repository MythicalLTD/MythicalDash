<?php
include_once("config.php");

$cpconn = new mysqli($_CONFIG["db_host"] . ':' .$_CONFIG['db_port'], $_CONFIG["db_username"], $_CONFIG["db_password"], $_CONFIG["db_name"]);
$mysqli = new mysqli($_CONFIG["db_host"] . ':' .$_CONFIG['db_port'], $_CONFIG["db_username"], $_CONFIG["db_password"], $_CONFIG["db_name"]);
//
// Some functions
//
function getclientip() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)) { $ip = $client; }
    elseif(filter_var($forward, FILTER_VALIDATE_IP)) { $ip = $forward; }
    else { $ip = $remote; }

    return $ip;
}
