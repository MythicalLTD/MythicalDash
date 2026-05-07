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
  header("location: /auth/register");
  die();
  
}


if(isset($_POST['reg_user']))
{
    $referral = file_get_contents($getsettingsdb["proto"] . $_SERVER['SERVER_NAME'] . "/api/tools/randompassword?length=5");
    $username = mysqli_real_escape_string($cpconn, $_POST['username']);
    $first_name = mysqli_real_escape_string($cpconn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($cpconn, $_POST['last_name']);
    $email = mysqli_real_escape_string($cpconn, $_POST['email']);
    $password = mysqli_real_escape_string($cpconn, $_POST['password']);
    $time = time();
    $registered = date("d-m-y", time());
    $defram = $getsettingsdb['def_memory'];
    $defdisk = $getsettingsdb['def_disk_space'];
    $defcpu = $getsettingsdb['def_cpu'];
    $defsvlimit = $getsettingsdb['def_server_limit'];
    $defalloc = $getsettingsdb['def_port'];
    $defdata = $getsettingsdb['def_data'];
    $defback = $getsettingsdb['def_back'];
    $usr_id = mt_rand(100000000000000, 999999999999999);
    //Password blocking
    $insecure_passwords = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy");
    if (in_array($password, $insecure_passwords)) {
        $_SESSION['error'] = "Password is not secure. Please choose a different one";
        header("location: register");
        die();
    } else {
        
    }
    //Username blocking
    /**$blocked_usernames = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy", "plexed", "badsk", "username", "sex", "porn", "nudes", "nude", "ass", "hacker", "bigdick");
    if (in_array($username, $blocked_usernames)) {
        $_SESSION['error'] = "Please don`t use this username we blocked it!";
        header("location: register");
        die();
    } else {
        
    }
    if (preg_match("/[^a-zA-Z]+/", $username)) {
      $_SESSION['error'] = "Please don`t use this username we blocked it!";
      header("location: register");
      die();
    } else {
        
    }
    if (preg_match("/[^a-zA-Z]+/", $first_name)) {
      $_SESSION['error'] = "Please don`t use this username we blocked it!";
      header("location: register");
      die();
    } else {
        
    }
    if (preg_match("/[^a-zA-Z]+/", $last_name)) {
      $_SESSION['error'] = "Please don`t use this username we blocked it!";
      header("location: register");
      die();
    } else {
        
    }**/

    $size = "200x200";
    $avatar_url = "https://robohash.org/".$username."?size=".$size.'&?set=set4';
    $avatar = $avatar_url;
    $ip_addres = getclientip();

    $query = "SELECT * FROM login_logs WHERE ipaddr = '$ip_addres'";
    $result = mysqli_query($cpconn, $query);
    $count = mysqli_num_rows($result);
    echo $count; 

    if ($count >= 1) {
        echo "No account for you";
        $_SESSION['error'] = "Please dont try to abuse to get more resources.";
        header("location: register");
        die();
    }

    
    //Alt check 1
    $query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "This username or email is alertly in the database.";
        header("location: register");
        die();
    } else {
        
    }

    //Checks if the user is using an vpn
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
    //Make an panel account
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $getsettingsdb["ptero_url"].'/api/application/users');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $getsettingsdb["ptero_apikey"],
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    $data = [
        'email' => $email,
        'username' => $username,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'password' => $password,
        'language' => 'en',
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $responseData = json_decode($response, true);
    $panelId = $responseData['attributes']['id'];
    $panel_username = $responseData['attributes']['username'];

    // Check for errors
    if (curl_errno($ch)) {
        //echo 'cURL Error: ' . curl_error($ch);
    } else {
        
    }
    
    curl_close($ch);
    
    // Create the user in the database
    if (!mysqli_query($cpconn, "INSERT INTO `users` (`panel_id`, `user_id`,  `username`, `first_name`, `last_name`, `email`, `password`, `avatar`, `role`, `minutes_idle`, `last_seen`, `coins`, `memory`, `disk_space`, `ports`, `databases`, `cpu`, `server_limit`, `backup_limit`, `panel_username`, `panel_password`, `register_ip`, `lastlogin_ip`, `last_login`, `banned`, `banned_reason`, `staff`) VALUES ('$panelId', '$usr_id', '$username', '$first_name', '$last_name', '$email', '$password', '$avatar', 'Member', NULL, '0', '0.00', '$defram', '$defdisk', '$defalloc', '$defdata', '$defcpu', '$defsvlimit', '$defback', '$panel_username', '$password', '$ip_addres', '$ip_addres', '$time', '0', NULL, '0');")) {
      $_SESSION['error'] =  mysqli_error($cpconn);
      header("location: /auth/register");
      die();
    }
    //Logs the ip into the ip login logs table
    $cpconn->query("INSERT INTO login_logs (ipaddr, userid) VALUES ('$ip_addres', '$usr_id')");
    //Creates a code
    if (!mysqli_query($cpconn, "INSERT INTO referral_codes (uid, referral) VALUES ('$usr_id', '$referral')")) {
      header("location: /auth/login");
    }

    //if (isset($_POST['r_code'])) {
    //    $r = mysqli_query($cpconn, "SELECT * FROM referral_codes WHERE referral = '" . mysqli_real_escape_string($cpconn, $_POST['r_code']) . "'")->fetch_object();
    //    $referrer = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '$r->uid'")->fetch_object();
    //    $newc = $referrer->coins + $getsettingsdb['coinsref'];
    //    mysqli_query($cpconn, "UPDATE users SET coins = '$newc' WHERE user_id = '$r->uid'");
    //    $time = time();
    //    mysqli_query($cpconn, "INSERT INTO referral_claims (`code`, `uid`, `timestamp`) VALUES ('$r->referral', '$usr_id', '$time')");
    //    mysqli_query($cpconn, "UPDATE users SET coins = '".$getsettingsdb['coinsref']."' WHERE user_id = '$usr_id'");
    //    $_SESSION['success'] = "You used a referral from " . $referrer->username . ", so you just earned ".$getsettingsdb['coinsref']." coins.";
    //}

    $_SESSION['success'] = "Done thanks for using".$getsettingsdb['name'];
    header('location: login');
    mysqli_close($cpconn);
    logClient("[AUTH] ".$username." just registred in!");

    
}




?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= $getsettingsdb["name"] ?> - Register</title>
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
              <p class="text-lead text-light">To continue, you must register!</p>
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
                <small>Please register with your credentials</small>
              </div>
              <br>
              <form method="POST">
              <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="fa fa-user"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="first_name" placeholder="Your name" type="text">
                  </div>
                </div>
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="fa fa-users"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="last_name" placeholder="Your last name" type="text">
                  </div>
                </div>
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="fa fa-user"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="username" placeholder="Username" type="text">
                  </div>
                </div>
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="email" placeholder="Email" type="email">
                  </div>
                </div>
                <!--<div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="fa fa-code"></i></span>
                    </div>
                    <input class="form-control bg-white" name="r_code" placeholder="Referral code" type="text">
                  </div>
                </div>-->
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
                  <input class="custom-control-input bg-white" required id=" customCheckLogin" type="checkbox">
                  <label class="custom-control-label" for=" customCheckLogin">
                    <span class="text-muted">I accept with the <a href="<?= $getsettingsdb['termsofservice'] ?>">Terms of Service</a> and the <a href="<?= $getsettingsdb['privacypolicy'] ?>">Privacy Policy</a>!</span>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" name="reg_user" value="ok" class="btn btn-primary my-4 ">Register</button>
                </div>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              
            </div>
            <div class="col-6 text-right">
              <a href="login" class="text-light"><small>Login with your account</small></a>
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