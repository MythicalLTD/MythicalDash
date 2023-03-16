<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/config.php");
require("../core/require/sql.php");
require("../core/require/addons.php");

session_start();
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();


if (!is_numeric($_GET["server"])) {
    $_SESSION['error'] = "Server ID is invalid.";
          echo '<script>window.location.replace("/");</script>';
    die();
}
$userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

$userdb = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_object();
$curcoins = $userdb->coins;
if ($curcoins < $getsettingsdb["vipqueue"]) {
    $cneeded = $getsettingsdb["vipqueue"] - $userdb->coins;
    if ($userdb->coins == 0) {
        $cneeded = $getsettingsdb["vipqueue"];
    }
    $_SESSION['error'] = "You do not have enough coins to buy the VIP queue. You need <strong>$cneeded</strong> more coins!";
          echo '<script>window.location.replace("/");</script>';
    die();
}


$server = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "'")->fetch_object();

if ($server->type > 1) {
    $_SESSION['error'] = "You already have VIP or staff queue!";
          echo '<script>window.location.replace("/");</script>';
    die();
}

// Remove the coins

$newcoins = $curcoins - $getsettingsdb["vipqueue"];

mysqli_query($cpconn, "START TRANSACTION");
if (!mysqli_query($cpconn, "UPDATE users SET coins = '$newcoins' WHERE user_id = '".$_SESSION["uid"]."'")) {
    $_SESSION['error'] = "There was an exception while communicating with the database. Please contact support.";
    mysqli_query($cpconn, "ROLLBACK");
          echo '<script>window.location.replace("/");</script>';
    die();
}


if (!mysqli_query($cpconn, "UPDATE servers_queue SET type = '1' WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET["server"]) . "'")) {
    mysqli_query($cpconn, "ROLLBACK");
          echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "There was an exception while communicating with the database. Pleast contact support.";
    die();
}

logClient("" . $userdfb['username'] ." bought **1 VIP queue pass**!");
mysqli_query($cpconn, "COMMIT");
      echo '<script>window.location.replace("/");</script>';
$_SESSION['success'] = "Your server got is now in VIP queue. Enjoy faster queue times!";
die();