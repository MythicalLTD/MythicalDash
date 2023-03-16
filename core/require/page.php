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

<!-- 



-->






<!--

WARNING: THIS IS A PART OF THE OLD CODE DO NOT DELETE IT 
SINGED: NaysKutzu
NOTE: Love you tye!


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?= $getsettingsdb["linkvertise"] ?>
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <meta name="keywords" content="<?= $getsettingsdb['seo_keywords'] ?>">
  <meta name="theme-color" content="<?= $getsettingsdb['seo_color'] ?>">
  <meta name="description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta name="og:description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta property="og:title" content="<?= $getsettingsdb['name'] ?>">
  <meta property="og:image" content="<?= $getsettingsdb['logo'] ?>">
  <title><?= $getsettingsdb["name"] ?></title>
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/fontawesome.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/argon.css" type="text/css">
</head>

<body>
  <nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-dark bg-dark" id="sidenav-main">
    <div class="scrollbar-inner">
      <div class="sidenav-header  d-flex  align-items-center">
        <a class="navbar-brand" href="/">
          <img src="<?= $getsettingsdb["logo"] ?>" class="navbar-brand-img" alt="...">
        </a>
        <div class=" ml-auto ">
          <div class="sidenav-toggler d-none d-xl-block" data-action="sidenav-unpin" data-target="#sidenav-main">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="navbar-inner">
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="/">
                      <i class="fas fa-home text-primary"></i>
                      <span class="nav-link-text">Home</span>
                  </a>
              </li>
          </ul>
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="/user/profile">
                      <i class="fas fa-user-lock text-primary"></i>
                      <span class="nav-link-text">Profile</span>
                  </a>
              </li>
          </ul>
          <?php 
          if ($getperms['canbuy'] == "true")
          {
            ?>
            <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="/store">
                      <i class="fas fa-shopping-cart text-primary"></i>
                      <span class="nav-link-text">Store</span>
                  </a>
              </li>
          </ul>
          <?php
          }
          else
          {

          }
          if ($getsettingsdb['disable_earning'] == "true")
          {
            
          }
          else
          {
            ?>
            <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="/earn/select">
                      <i class="fas fa-dollar-sign text-primary"></i>
                      <span class="nav-link-text">Earn coins</span>
                  </a>
              </li>
          </ul>
            <?php
          }
          ?>
          
          

          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="/support/select">
                      <i class="fas fa-question text-primary"></i>
                      <span class="nav-link-text">Support</span>
                  </a>
              </li>
          </ul>
          <hr class="my-3">
          <h6 class="navbar-heading p-0 text-muted">
            <span class="docs-normal">Links</span>
          </h6>
          <ul class="navbar-nav mb-md-3">
            <li class="nav-item">

              <a class="nav-link" href="<?= $getsettingsdb["ptero_url"] ?>" target="_blank">
                <i class="fas fa-gamepad"></i>
                <span class="nav-link-text">Game panel</span>
              </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $getsettingsdb["website"] ?>" target="_blank">
                    <i class="fas fa-home"></i>
                    <span class="nav-link-text">Website</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $getsettingsdb["statuspage"] ?>" target="_blank">
                    <i class="fas fa-signal"></i>
                    <span class="nav-link-text">Status page</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $getsettingsdb["phpmyadmin"] ?>" target="_blank">
                    <i class="fa fa-database"></i>
                    <span class="nav-link-text">PhpMyAdmin</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $getsettingsdb["discordserver"] ?>" target="_blank">
                    <i class="fab fa-discord"></i>
                    <span class="nav-link-text">Discord</span>
                </a>
            </li>
          </ul>
          <?php 
          if ($getperms['fullperm'] == "true" || $getperms['canseeadminhomepage'] == "true" || $getperms['canseeusers'] == "true" || $getperms['canseeservers'] == "true" || $getperms['canseeservers'] == "true" || $getperms['candeleteservers'] == "true" || $getperms['caneditappsettings'] == "true" || $getperms['issupport'] == "true" )
          {
            ?>
            <h6 class="navbar-heading p-0 text-muted">
              <span class="docs-normal">Administrators</span>
            </h6>
            <?php
            if ($getperms['fullperm'] == "true" || $getperms['canseeadminhomepage'] == "true")
            {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/home">
                    <i class="fas fa fa-inbox"></i>
                    <span class="nav-link-text">Statistics</span>
                  </a>
                </li>
              </ul>
              <?php
            }
          if ($getperms['fullperm'] == "true" || $getperms['issupport'] == "true")
          {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/tickets">
                    <i class="fas fa-question"></i>
                    <span class="nav-link-text">Tickets</span>
                  </a>
                </li>
              </ul>
              <?php
          }
          if ($getperms['fullperm'] == "true" || $getperms['canseeusers'] == "true" || $getperms['candeleteusers'] == "true" || $getperms['caneditusers'] == "true")
          {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/users">
                    <i class="fas fa fa-users"></i>
                    <span class="nav-link-text">Users</span>
                  </a>
                </li>
              </ul>
              <?php
          }
          if ($getperms['fullperm'] == "true" || $getperms['canseeservers'] == "true" || $getperms['caneditservers'] == "true" || $getperms['candeleteservers'] == "true")
          {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/servers">
                    <i class="fas fa fa-server"></i>
                    <span class="nav-link-text">Servers</span>
                  </a>
                </li>
              </ul>
              <?php
          }
          if ($getperms['fullperm'] == "true" || $getperms['canseeservers'] == "true" || $getperms['caneditservers'] == "true" || $getperms['candeleteservers'] == "true")
          {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/servers_queue">
                    <i class="fas fa fa-server"></i>
                    <span class="nav-link-text">Servers queue</span>
                  </a>
                </li>
              </ul>
              <?php
          }
          if ($getperms['fullperm'] == "true" || $getperms['caneditappsettings'] == "true")
          {
              ?>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="/admin/settings">
                    <i class="fas fa fa-cogs"></i>
                    <span class="nav-link-text">Settings</span>
                  </a>
                </li>
              </ul>
              <?php
          }
          }
          ?>
        </div>
      </div>
    </div>
  </nav>
  <div class="main-content" id="panel">
    <?php
    if ($_SERVER['REQUEST_URI'] == "/" || $_SERVER['REQUEST_URI'] == "/index.php" || $_SERVER['REQUEST_URI'] == "/user/profile" || $_SERVER['REQUEST_URI'] == "/user/profile.php" || $_SERVER['REQUEST_URI'] == "index") {
        $navcolor = "default";
    } else {
        $navcolor = "primary";
    }
    ?>
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-<?= $navcolor ?> border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav align-items-center  ml-md-auto ">
            <li class="nav-item d-xl-none">
              <div class="pr-3 sidenav-toggler sidenav-toggler-dark" data-action="sidenav-pin" data-target="#sidenav-main">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </div>
            </li>
          </ul>
          <ul class="navbar-nav align-items-center  ml-auto ml-md-0 ">
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                    <img alt="Image placeholder" src="<?= $userdb["avatar"] ?>">
                  </span>
                </div>
              </a>
              <div class="dropdown-menu  dropdown-menu-right ">
                <div class="dropdown-header noti-title text-center">
                  <h4 class="text-overflow m-0">Welcome, <?= $userdb["username"] ?>!</h4>
                  <h5 class="text-overflow m-0">You have <?= $userdb["coins"]?> coins</h5>
                </div>
                <a href="/user/profile" class="dropdown-item">
                  <i class="fa fa-user"></i>
                  <span>Profile Page</span>
                </a>
                <a href="/support/select" class="dropdown-item">
                  <i class="fas fa-question"></i>
                  <span>Support</span>
                </a>
                <a href="/users/lookup" class="dropdown-item">
                  <i class="fa fa-search"></i>
                  <span>Find a user</span>
                </a>
                <a href="/user/credentials" class="dropdown-item">
                  <i class="fa fa-key"></i>
                  <span>Credentials</span>
                </a>
                <a href="/auth/logout" class="dropdown-item">
                  <i class="ni ni-user-run"></i>
                  <span>Logout</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>-->
