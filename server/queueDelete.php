<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/config.php");
require("../core/require/sql.php");
require("../core/require/addons.php");
  
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
session_start();


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
$ownsServer = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "'");
if ($ownsServer->num_rows == 0) {
    $_SESSION['error'] = "You don't have permission to delete this server or it doesn't exist.";
    header("location: /");
    die();
}
/*
 * Delete server
*/
$serverInformation = $ownsServer->fetch_object();
if ($serverInformation->type == 2) {
    $userdb = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_object();
    $current_qc = $userdb->coins;
    $new_qc = $getsettingsdb["vipqueue"] + $current_qc;
    mysqli_query($cpconn, "UPDATE users SET coins = '$new_qc' WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
}
if (mysqli_query($cpconn, "DELETE FROM servers_queue WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "'")) {
    header("location: /");
    $_SESSION['success'] = "Your server is no longer in queue!";
    $userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

    logClient("[Server queue deletion] " . $userdfb['username'] . " removed the server from queue with ID #" . $_GET["server"] . ".");
    die();
}
else {
    $_SESSION['error'] = "Hmmm. Cannot delete your server from the queue, contact staff.";
    header("location: /");
    die();
}