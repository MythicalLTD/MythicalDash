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
    $dbservers = $cpconn->query("SELECT * FROM servers where id = '". $svid. "'")->fetch_array();
    $panel_id = $dbservers['pid'];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $getsettingsdb["ptero_url"]."/api/application/servers/".$panel_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

    $headers = array();
    $headers[] = "Authorization: Bearer ".$getsettingsdb["ptero_apikey"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    if (mysqli_query($cpconn, "DELETE FROM servers WHERE `servers`.`id` = ".$svid)) {
      echo '<script>window.location.replace("/admin/servers.php");</script>';
    }
    else {
        $_SESSION['error'] = "There was an unexpected error while removing the server from db!";
        echo '<script>window.location.replace("/");</script>';
    }
  }
}



?>
