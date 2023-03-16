<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/config.php");
require("../core/require/sql.php");
require("../core/require/addons.php");
  
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (isset($_COOKIE['remember_token'])) {
    $session_id = $_COOKIE['remember_token'];
    $query = "SELECT * FROM users WHERE session_id='".$session_id."'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        session_start();
        $userdbd = $cpconn->query("SELECT * FROM users WHERE session_id='$session_id'")->fetch_array();
        $_SESSION['username'] = $userdbd['username'];
        $_SESSION['email'] = $userdbd['email'];
        $_SESSION["uid"] = $userdbd['user_id'];
    }
    else
    {
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('phpsessid', '', time() - 3600, '/');
        session_destroy();
        echo '<script>window.location.replace("/auth/login");</script>';
    }
  
  }
else
{
  setcookie('remember_token', '', time() - 3600, '/');
  setcookie('phpsessid', '', time() - 3600, '/');
  session_destroy();
  echo '<script>window.location.replace("/auth/login");</script>';
}

$user = $_SESSION['user'];
if (!is_numeric($_GET["server"])) {
    $_SESSION['error'] = "Server id is invalid.";
          echo '<script>window.location.replace("/");</script>';
    die();
}
/*
 * Check user owns server
 */
$ownsServer = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "'");
if ($ownsServer->num_rows == 0) {
    $_SESSION['error'] = "You don't have permission to delete this server or it doesn't exist.";
          echo '<script>window.location.replace("/");</script>';
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
          echo '<script>window.location.replace("/");</script>';
    $_SESSION['success'] = "Your server is no longer in queue!";
    $userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

    logClient("[Server queue deletion] " . $userdfb['username'] . " removed the server from queue with ID #" . $_GET["server"] . ".");
    die();
}
else {
    $_SESSION['error'] = "Hmmm. Cannot delete your server from the queue, contact staff.";
          echo '<script>window.location.replace("/");</script>';
    die();
}