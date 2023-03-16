<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/config.php");
session_start();
require("../core/require/sql.php");
require("../core/require/addons.php");
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (!$cpconn->ping()) {
    $_SESSION['error'] = "There was an error communicating with MYSQL";
    echo '<script>window.location.replace("/auth/login");</script>';
    die();
    
}



if(isset($_POST['res_pass'])) {
    $email = mysqli_real_escape_string($cpconn, $_POST['email']);
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
      $userdb = $cpconn->query("SELECT * FROM users WHERE email='$email'")->fetch_array();
      $ip_addres = getclientip();
      $smtpHost = $getsettingsdb['smtpHost'];
      $smtpPort = $getsettingsdb['smtpPort'];
      $smtpSecure = $getsettingsdb['smtpSecure'];
      $smtpUsername = $getsettingsdb['smtpUsername'];
      $smtpPassword = $getsettingsdb['smtpPassword'];
      $fromEmail = $getsettingsdb['fromEmail'];
      $toEmail = $email;
      $username = $userdb['username'];
      $subject=$getsettingsdb['name']." password reset!"; 
      $encodedUsername = base64_encode($username);
      $randomString = bin2hex(random_bytes(8));
      $timestamp = time();
      $encodedTimestamp = base64_encode($timestamp);
      $token = $encodedTimestamp.$encodedUsername . "_" . $randomString;
      $message="Hey ".$username."! \nYou requested for your password at ".$getsettingsdb['name']." to get resetted! \nTo reset your password you need to click this link: ".$getsettingsdb['proto']. $host = $_SERVER['HTTP_HOST']."/auth/reset?token=".$token." \nIf it was not you then you can ignore this email";
      $cpconn->query("INSERT INTO `password_reset` (`email`, `token`) VALUES ('$email', '".$token."');");
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = $smtpHost;
      $mail->Port = $smtpPort;
      $mail->SMTPAuth = true;
      $mail->Username = $smtpUsername;
      $mail->Password = $smtpPassword;
      $mail->SMTPSecure = $smtpSecure;
      $mail->setFrom($fromEmail);
      $mail->addAddress($toEmail);
      $mail->isHTML(false);
      $mail->Subject = $subject;
      $mail->Body = $message;
      $mail->send();
      $_SESSION['success'] = "Done please check your email!";
      echo '<script>window.location.replace("/auth/forgot_password");</script>';
      die();
    }
    else
    {
      $_SESSION['error'] = "This email is not in our database!";
      
    }

}
else
{
  ?>
  <!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <title><?= $getsettingsdb["name"] ?> - Forgot</title>
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/fontawesome.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/css/argon.css" type="text/css">
  <link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
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
              <p class="text-lead text-light">To reset your password we need to know your email</p>
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
                <small>Enter your email</small>
                
              </div>
              <br>
              <form method="POST">
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control bg-white" required name="email" placeholder="Email" type="email">
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" name="res_pass" value="ok" class="btn btn-primary my-4 ">Send</button>
                  
                </div>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <a href="login" class="text-light"><small>Have an account?</small></a>
            </div>
            <div class="col-6 text-right">
              <a href="register" class="text-light"><small>Create new account</small></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
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
  <?php
}

?>
