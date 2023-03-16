<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require("../core/require/sql.php");
require("../core/require/addons.php");


$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (isset($_COOKIE['remember_token'])) {
  $session_id = $_COOKIE['remember_token'];
  $query = "SELECT * FROM users WHERE session_id='".$session_id."'";
  $result = mysqli_query($cpconn, $query);
  if (mysqli_num_rows($result) > 0) {
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

$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$username = $userdb['username'];
logClient("[AUTH] ".$username." just logged out!");
setcookie('remember_token', '', time() - 3600, '/');
$cpconn->query("UPDATE `users` SET `session_id` = NULL WHERE `users`.`user_id` = ".$_SESSION["uid"].";");
session_destroy();
echo '<script>window.location.replace("/");</script>';
?>