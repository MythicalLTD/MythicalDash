<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
  if ($_SERVER["REQUEST_URI"] != "/") {
    $_SESSION["redirafterlogin"] = $_SERVER["REQUEST_URI"];
  }
  header("Location: /auth/login");
  
}
include_once("sql.php");


$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

// Some sessions can resolve to a user without a valid role row.
// Normalize permissions so all checks below are safe.
$roleName = $userdb['role'] ?? '';
$getperms = [];
if ($roleName !== '') {
  $getperms = $cpconn->query("SELECT * FROM roles WHERE name= '" . mysqli_real_escape_string($cpconn, $roleName) . "'")->fetch_array();
}

if (!is_array($getperms)) {
  $getperms = [];
}

$getperms = array_merge([
  'fullperm' => "false",
  'canbuy' => "false",
  'canbypassmaintenance' => "false",
  'canseeadminhomepage' => "false",
  'canseeusers' => "false",
  'canseeservers' => "false",
  'candeleteservers' => "false",
  'caneditappsettings' => "false",
  'issupport' => "false",
  'candeleteusers' => "false",
  'caneditusers' => "false",
  'caneditservers' => "false",
], $getperms);
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
      header('location: '.$url.'/auth/errors/maintenance');
      die;
    }
  }
  else
  {

  }
  
}


?>
<!-- Bidvertiser2078655 -->
<!--
=========================================================
* Argon Dashboard - v1.2.0
=========================================================
* Product Page: https://www.creative-tim.com/product/argon-dashboard
* Copyright  Creative Tim (http://www.creative-tim.com)
* Coded by www.creative-tim.com
=========================================================
-->
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
  <script src="https://publisher.linkvertise.com/cdn/linkvertise.js"></script><script>linkvertise(583258, {whitelist: ["panel.mythicalnodes.xyz","status.mythicalnodes.xyz","phpmyadmin.mythicalnodes.xyz","mythicalnodes.xyz","discord.mythicalnodes.xyz"], blacklist: ["my.mythicalnodes.xyz","panel.f1xmc.ro","deploy.mythicalnodes.xyz"]});</script>
  <meta name="author" content="Creative Tim">
  <title><?= $getsettingsdb["name"] ?></title>
  <!-- logo -->
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/nucleo.css" type="text/css">
  <!-- <link rel="stylesheet" href="sweetalert2.min.css"> -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/fontawesome.css" type="text/css">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/argon.css" type="text/css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
</head>

<body>
  <!-- Sidenav -->
  <nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-dark bg-dark" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand -->
      <div class="sidenav-header  d-flex  align-items-center">
        <a class="navbar-brand" href="/">
          <img src="<?= $getsettingsdb["logo"] ?>" class="navbar-brand-img" alt="...">
        </a>
        <div class=" ml-auto ">
          <!-- Sidenav toggler -->
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
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
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
                  <a class="nav-link" href="/store/select">
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
          
          <!-- Divider -->
          <hr class="my-3">
          <!-- Heading -->
          <h6 class="navbar-heading p-0 text-muted">
            <span class="docs-normal">Links</span>
          </h6>
          <!-- Navigation -->
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
         
          <!-- Heading -->
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
  <!-- Main content -->
  <div class="main-content" id="panel">
    <!-- Topnav -->
    <?php
    if ($_SERVER['REQUEST_URI'] == "/") {
        $navcolor = "default";
    } else {
        $navcolor = "primary";
    }
    ?>
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-<?= $navcolor ?> border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- Navbar links -->
          <ul class="navbar-nav align-items-center  ml-md-auto ">
            <li class="nav-item d-xl-none">
              <!-- Sidenav toggler -->
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
                <a href="/auth/logout" class="dropdown-item">
                  <i class="ni ni-user-run"></i>
                  <span>Logout</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>