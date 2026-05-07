<?php


require("../core/require/sql.php");
require("../core/require/config.php");
require("../core/require/addons.php");
session_start();
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();

if (!isset($_SESSION['loggedin'])) {
    header("location: /login");
    die();
}
$user = $_SESSION['user'];
if (!is_numeric($_GET["server"])) {
    $_SESSION['error'] = "Server id is invalid.";
    header("location: /");
    die();
}
/*
 * Check user owns server
 */
$userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

$ownsServer = mysqli_query($cpconn, "SELECT * FROM servers WHERE pid = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "' AND uid = '"  . $_SESSION['uid'] . "'");
if ($ownsServer->num_rows == 0) {
    $_SESSION['error'] = "You don't have permission to delete this server or it doesn't exist.";
    header("location: /");
    die();
}
/*
 * Delete server
*/
/* @var $panel_url */
/* @var $panel_apikey */
$delete_server = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $_GET["server"] . "/force");
curl_setopt($delete_server, CURLOPT_CUSTOMREQUEST, "DELETE");
$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
    "Authorization: Bearer " . $getsettingsdb["ptero_apikey"]
);
curl_setopt($delete_server, CURLOPT_HTTPHEADER, $headers);
curl_setopt($delete_server, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($delete_server);
curl_close($delete_server);
if (!empty($result)) {
    $_SESSION['error'] = "There was an error while deleting your server.";
    header("location: /");
    die();
}
if (mysqli_query($cpconn, "DELETE FROM servers WHERE pid = '" . $_GET["server"]. "'")) {
    $_SESSION['success'] = "Deleted server. ";
    $userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

    logClient("[Server deletion] " . $userdfb['username'] . " deleted the server with PID #" . $_GET["server"] . ".");
    header("location: /");
    die();
}
else {
    $_SESSION['error'] = "There was an error while deleting your server, contact support immediately.";
    header("location: /");
    die();
}