<?php 
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
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$getperms = $cpconn->query("SELECT * FROM roles WHERE name= '". $userdb['role']. "'")->fetch_array();

if(isset($_GET['key'])) {
  $key = $_GET['key'];
  $result = mysqli_query($cpconn, "SELECT * FROM adfoc WHERE sckey='$key'");
  if (mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      if ($row['claim'] == "true") {
          echo "Error: Key has already been used.";
      } else {
          $usr_coins = $userdb['coins'];
          $newcoins = $usr_coins + $getsettingsdb['adfoc_coins'];
          $cpconn->query("UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
          echo "Success: Key is valid.";
          $cpconn->query("DELETE FROM adfoc WHERE sckey='$key'");
          echo '<script>window.location.replace("/");</script>';
      }
  } else {
      echo "Error: Key not found.";
  }
}

if ($getsettingsdb['disable_earning'] == "true")
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to earn coins!";
    die;
}

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
if ($getperms['canlinkvertise'] == "true") 
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to earn coins via linkvertise";
  die;
}

if ($getsettingsdb['linkvertise_status'] == "true")
{

}
else 
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to earn coins via linkvertise";
  die;
}


?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $getsettingsdb['name'] ?> | Linkvertise</title>
    <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
    <style>
        * {
    font-family: Google sans, Arial;
  }
  
  html, body {
    margin: 0;
    padding: 0;
  }
  
  .flex-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: white;
    animation: colorSlide 15s cubic-bezier(0.075, 0.82, 0.165, 1) infinite;
  }
  .flex-container .text-center {
    text-align: center;
  }
  .flex-container .text-center h1,
  .flex-container .text-center h3 {
    margin: 10px;
    cursor: default;
  }
  .flex-container .text-center h1 .fade-in,
  .flex-container .text-center h3 .fade-in {
    animation: fadeIn 2s ease infinite;
  }
  .flex-container .text-center h1 {
    font-size: 8em;
    transition: font-size 200ms ease-in-out;
    border-bottom: 1px dashed white;
  }
  .flex-container .text-center h1 span#digit1 {
    animation-delay: 200ms;
  }
  .flex-container .text-center h1 span#digit2 {
    animation-delay: 300ms;
  }
  .flex-container .text-center h1 span#digit3 {
    animation-delay: 400ms;
  }
  .flex-container .text-center button {
    border: 1px solid white;
    background: transparent;
    outline: none;
    padding: 10px 20px;
    font-size: 1.1rem;
    font-weight: bold;
    color: white;
    text-transform: uppercase;
    transition: background-color 200ms ease-in;
    margin: 20px 0;
  }
  .flex-container .text-center button:hover {
    background-color: white;
    color: #555;
    cursor: pointer;
  }
  
  @keyframes colorSlide {
    0% {
      background-color: #152a68;
    }
    25% {
      background-color: royalblue;
    }
    50% {
      background-color: seagreen;
    }
    75% {
      background-color: tomato;
    }
    100% {
      background-color: #152a68;
    }
  }
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }
    </style> 
</head>
<body>
<div class="flex-container">
  <div class="text-center">
    <h1>
      <span class="fade-in" id="digit1">Link</span>
      <span class="fade-in" id="digit2">ready</span>
    </h1>
    <h3 class="fadeIn">Please click the continue button to continue</h3>
    <?php 
        $genid = mt_rand(100000000000000, 999999999999999);
        $linkid = $genid;
        mysqli_query($cpconn, "INSERT INTO `adfoc` (`sckey`) VALUES ('".$linkid."');");
        $url = $getsettingsdb['proto'].$_SERVER['SERVER_NAME']."/earn/linkvertise?key=".$linkid;
        echo '
        <a href="'.$url.'"><button type="button" name="button">Continue</button></a>
        ';
    ?>
  </div>
</div>
</body>
</html>
<script src="https://publisher.linkvertise.com/cdn/linkvertise.js"></script><script>linkvertise(583258, {whitelist: ["gamepanel.mythicalsystems.tech","status.mythicalsystems.tech","pma.mythicalsystems.tech","mythicalsystems.tech","dsc.gg/mythicalsystems"], blacklist: ["my.mythicalsystems.tech","panel.f1xmc.ro","deploy.mythicalsystems.tech"]});</script>
