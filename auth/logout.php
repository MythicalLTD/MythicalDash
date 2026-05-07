<?php
session_start();
require("../core/require/sql.php");
require("../core/require/addons.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$username = $userdb['username'];
logClient("[AUTH] ".$username." just logged out!");
session_destroy();
echo '<script>window.location.replace("/");</script>';
?>