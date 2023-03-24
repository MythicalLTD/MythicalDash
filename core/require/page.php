<?php
include_once("sql.php");


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

$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$getperms = $cpconn->query("SELECT * FROM roles WHERE name= '". $userdb['role']. "'")->fetch_array();
if ($getsettingsdb['maintenance'] == "false")
{

}
else
{
  if ($getperms['canbypassmaintenance'] == "false")
  {
    if ($getperms['fullperm'] == "true")
    {

    }
    else
    {
      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
      {
        $url = "https://";  
      }
      else 
      {
        $url = "http://"; 
      }         
      $url.= $_SERVER['HTTP_HOST'];                      
      echo '<script>window.location.replace("'.$url.'/auth/errors/maintenance");</script>';

      die;
    }
  }
  else
  {

  }
  
}


?>
