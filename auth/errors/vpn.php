<?php
require("../../core/require/config.php");
require("../../core/require/sql.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
?>
<!--
=========================================================
* Argon Dashboard - v1.2.0
=========================================================
* Product Page: https://www.creative-tim.com/product/argon-dashboard
* Copyright  Creative Tim (http://www.creative-tim.com)
* Coded by www.creative-tim.com
=========================================================
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <meta name="keywords" content="<?= $getsettingsdb['seo_keywords'] ?>">
  <meta name="theme-color" content="<?= $getsettingsdb['seo_color'] ?>">
  <meta name="description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta name="og:description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta property="og:title" content="<?= $getsettingsdb['name'] ?>">
  <meta property="og:image" content="<?= $getsettingsdb['logo'] ?>">
  <title><?= $getsettingsdb["name"] ?> - VPNs are not allowed</title>
  <!-- logo -->
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/nucleo.css" type="text/css">
  <!-- <link rel="stylesheet" href="sweetalert2.min.css"> -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/fontawesome.css" type="text/css">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/argon.css" type="text/css">
</head>

<body class="bg-default">
<style>
    body {
  background-image: url('<?= $getsettingsdb["home_background"]  ?>');
  background-size:cover
  -moz-background-size: cover;
  -webkit-background-size: cover;
  -o-background-size: cover;
    } 
  </style>
  <!-- Navbar -->
  <nav id="navbar-main" class="navbar navbar-horizontal navbar-transparent navbar-main navbar-expand-lg navbar-light">
    <div class="container">
      <a class="navbar-brand" href="/">
        <img src="<?= $getsettingsdb["logo"] ?>">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse" aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-collapse navbar-custom-collapse collapse" id="navbar-collapse">
        <div class="navbar-collapse-header">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a href="/">
                <img src="<?= $getsettingsdb["logo"] ?>">
              </a>
            </div>
            <div class="col-6 collapse-close">
              <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar-collapse" aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span></span>
                <span></span>
              </button>
            </div>
          </div>
        </div>
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a href="<?= $getsettingsdb["website"] ?>" class="nav-link">
              <span class="nav-link-inner--text">Website</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= $getsettingsdb["statuspage"] ?>" class="nav-link">
              <span class="nav-link-inner--text">Status page</span>
            </a>
          </li>
        </ul>
        <hr class="d-lg-none" />
        <ul class="navbar-nav align-items-lg-center ml-lg-auto">
          <li class="nav-item">
            <a class="nav-link nav-link-icon" href="<?= $getsettingsdb["discordserver"] ?>" target="_blank" data-toggle="tooltip" data-original-title="Join our discord">
              <i class="fab fa-discord"></i>
              <span class="nav-link-inner--text d-lg-none">Discord server</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Main content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header  py-7 py-lg-8 pt-lg-9">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <h1 class="text-white"></h1>
              <p class="text-lead text-white"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <style>
      h3,p {
        color: black;
      }
    </style>
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="card card-profile bg-secondary mt-5">
            <div class="card-body pt-5 px-5">
              <div class="text-center mb-4">
                <img src="https://i.imgur.com/CvhLFdI.png" width="100" />
                <h3>VPNs are not allowed.</h3>
                  <p>We aim to provide you a good user experience. That is why for moderation purposes VPNs are not allowed.</p>
              </div>
              <form role="form">
                <div class="text-center">
                  <a href="<?= $getsettingsdb["discordserver"] ?>" target="_blank"><button type="button" class="btn btn-primary mt-2">Join our discord server</button></a>
                  <a href="/auth/logout"><button type="button" class="btn btn-danger mt-2">Logout</button></a>
                </div>
              </form>
              <br/><br/>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Footer -->
  
  <!-- Argon Scripts -->
  <!-- Core -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
  <!-- Demo JS - remove this in your project -->
  <!-- Done -->
</body>

</html>