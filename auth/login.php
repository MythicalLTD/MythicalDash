<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/config.php");
session_start();
require("../core/require/sql.php");
require("../core/require/addons.php");

$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (!$cpconn->ping()) {
    $_SESSION['error'] = "There was an error communicating with MYSQL";
    header("location: /auth/login");
    die();
    
}
if (isset($_SESSION['loggedin'])) {
    if (isset($_SESSION["redirafterlogin"])) {
        header("location: " . $_SESSION["redirafterlogin"]);
    } else {
        header("location: /");
    }
    die();
}
if (isset($_GET['log_user'])) {
    $ip_addres = getclientip();    
    $email = mysqli_real_escape_string($cpconn, $_GET['email']);
    $password = mysqli_real_escape_string($cpconn, $_GET['password']);
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        $userdb = $cpconn->query("SELECT * FROM users WHERE email='$email' AND password='$password'")->fetch_array();
        $usr_id = $userdb['user_id'];
        //VPN Check
        if ($ip_addres == "127.0.0.1") {
            $ip_addres = "12.34.56.78";
        }
        $vpn = false;
        $response = file_get_contents("http://ip-api.com/json/" . $ip_addres . "?fields=status,message,country,regionName,city,timezone,isp,org,as,mobile,proxy,hosting,query");
        $response = json_decode($response, true);
        if (isset($response['proxy'])) {
            if ($response['proxy'] == true || $response['hosting'] == true) {
                $vpn = true;
            }
        }
        if ($response['type'] = !"Residential") {
            $vpn = true;
        }
        if ($ip_addres == "51.161.152.218" || $ip_addres == "66.220.20.165"){
            $vpn = false;
        }
        if ($vpn == true) {
            $_SESSION['error'] = "You are using a VPN. This is not allowed.";
            header("location: /auth/errors/vpn");
            die();
        }
    
        //Checks if a user is using an alt
        $userids = array();
        $loginlogs = mysqli_query($cpconn, "SELECT * FROM login_logs WHERE userid = '$usr_id'");
        foreach ($loginlogs as $login) {
            $ip = $login['ipaddr'];
            if ($ip == "12.34.56.78") {
                continue;
            }
            $saio = mysqli_query($cpconn, "SELECT * FROM login_logs WHERE ipaddr = '$ip'");
            foreach ($saio as $hello) {
                if (in_array($hello['userid'], $userids)) {
                    continue;
                }
                if ($hello['userid'] == $usr_id) {
                    continue;
                }
                array_push($userids, $hello['userid']);
            }
        }
        if (count($userids) !== 0) {
            if ($_SESSION["uid"] != 638672769009319956 && $_SESSION["uid"] != 536579437064486912) {
            $_SESSION["alts"] = $userids;
            header("location: /auth/errors/alting");
            die();
            }
        }
        $cpconn->query("INSERT INTO login_logs (ipaddr, userid) VALUES ('$ip_addres', '$usr_id')");
        if ($userdb["banned"] == 1) {
          $_SESSION['ban_reason'] = $userdb["banned_reason"];
          session_destroy();
          header("location: /auth/errors/banned");
          die();
        }
        $username = $userdb['username'];
        logClient("[AUTH] ".$username." just logged in!");
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION["uid"] = $usr_id;
        $_SESSION['loggedin'] = true;
        if (isset($_SESSION["redirafterlogin"])) {
            header("location: " . $_SESSION["redirafterlogin"]);
        } else {
            header("location: /");
        }
    } else {
        $_SESSION['error'] = "The email or the password is wrong please try again!";
    }
}
?>
<!-- Bidvertiser2078655 -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= $getsettingsdb["name"] ?> - Login</title>
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/nucleo.css" type="text/css">
  <!-- <link rel="stylesheet" href="sweetalert2.min.css"> -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/fontawesome.css" type="text/css">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/argon.css" type="text/css">
  <meta name="keywords" content="<?= $getsettingsdb['seo_keywords'] ?>">
  <meta name="theme-color" content="<?= $getsettingsdb['seo_color'] ?>">
  <meta name="description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta name="og:description" content="<?= $getsettingsdb['seo_description'] ?>">
  <meta property="og:title" content="<?= $getsettingsdb['name'] ?>">
  <meta property="og:image" content="<?= $getsettingsdb['logo'] ?>">
</head>

<body class="bg-default">
  <style>
    body {
    	background-image: url('<?= $getsettingsdb["home_background"]  ?>');
    	background-size: cover;
    	-moz-background-size: cover;
    	-webkit-background-size: cover;
    	-o-background-size: cover;
    } 
  </style>
  <div class="main-content">
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
    <!-- Header -->
    <div class="header py-7 py-lg-8">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6">
              <h1 class="text-white">Welcome!</h1>
              <p class="text-lead text-light">To continue, you must login!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
        <?php
        if (isset($_SESSION["error"])) {
            ?>
            <div class="alert alert-danger" role="alert">
              <strong>Error!</strong> <?= $_SESSION["error"] ?>
            </div>
            <?php
            unset($_SESSION["error"]);
        }
        if (isset($_SESSION["success"])) {
          ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?= $_SESSION["success"] ?>
          </div>
          <?php
          unset($_SESSION["success"]);
      }
        ?>
    
          <div class="card bg-secondary shadow border-0">
            <div class="card-body px-lg-5 py-lg-5">
              <div class="text-center text-muted mb-4">
                <small>Login with your credentials</small>
                
              </div>
              <br>
              <form method="GET">
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="email" placeholder="Email" type="email">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="password" placeholder="Password"  id="password" type="password">
                    <div class="input-group-append">
                      <span class="input-group-text bg-secondary" id="show-password" style="cursor: pointer;">
                        <i class="fa fa-eye"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="custom-control custom-control-alternative custom-checkbox ">
                  <input class="custom-control-input bg-white" id=" customCheckLogin" type="checkbox">
                  <label class="custom-control-label" for=" customCheckLogin">
                    <span class="text-muted">Remember me</span>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" name="log_user" value="ok" class="btn btn-primary my-4 ">Login</button>
                  
                </div>
                <small><font color="gray">
                <?php
                if (isset($_SESSION["redirafterlogin"])) {
                  echo "You will be automatically redirected to <b>" . $_SESSION["redirafterlogin"] . "</b>.";
                }
                ?>
                </font></small>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <a href="forgot_password" class="text-light"><small>Forgot password?</small></a>
            </div>
            <div class="col-6 text-right">
              <a href="register" class="text-light"><small>Create new account</small></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/argon.js?v=1.2.0"></script>
</body>

</html>


<script>
    // Get the icon element
    var icon = document.getElementById("show-password");
    // Get the password input
    var passwordInput = document.getElementById("password");

    // Add a click event listener to the icon
    icon.addEventListener("click", function() {
        // If the password input type is "password"
        if (passwordInput.getAttribute("type") === "password") {
            // Change the input type to "text"
            passwordInput.setAttribute("type", "text");
           
        } else {
            // Otherwise, change the input type to "password"
            passwordInput.setAttribute("type", "password");
           
        }
    });
  </script>