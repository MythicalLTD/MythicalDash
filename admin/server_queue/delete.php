<?php 
require("../../core/require/page.php");
$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
//Looks into perms if users has acces to see this page!
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();

if ($perms['candeleteservers'] == "true" || $perms['fullperm'] == "true")
{
  //Do nothing
}
else
{
  echo '<script>window.location.replace("/");</script>';
  die;
}
if (isset($_GET['id'])) {
  if ($_GET['id'] == "")
  {
    $_SESSION['error'] = "We did not find an server id!";
    echo '<script>window.location.replace("/");</script>';
    die;
  }
  else
  {
    $svid = $_GET['id'];
    if (mysqli_query($cpconn, "DELETE FROM servers_queue WHERE `servers_queue`.`id` =".$svid)) {
        echo '<script>window.location.replace("/admin/servers_queue");</script>';
      }
      else {
          $_SESSION['error'] = "There was an unexpected error while removing the server from db!";
          echo '<script>window.location.replace("/");</script>';
      }
  }
}

?>