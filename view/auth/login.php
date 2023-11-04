<?php
use MythicalDash\CloudFlare\Captcha;
use MythicalDash\DiscordWebhookHandler;
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;

try {
  $conn = new Connect();
  $conn = $conn->connectToDatabase();
  $session = new SessionManager();
  session_start();
  $csrf = new MythicalDash\CSRF();
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validate('login-form')) {
      if (isset($_POST['login'])) {
        if (SettingsManager::getSetting("enable_turnstile") == "false") {
          $captcha_success = 1;
        } else {
          $captcha_success = Captcha::validate_captcha($_POST["cf-turnstile-response"], $session->getIP(), SettingsManager::getSetting("turnstile_secretkey"));
        }
        if ($captcha_success) {
          $email = mysqli_real_escape_string($conn, $_POST['email']);
          $password = mysqli_real_escape_string($conn, $_POST['password']);
          if (!$email == "" || !$password == "") {
            $query = "SELECT * FROM mythicaldash_users WHERE email = '$email'";
            $result = mysqli_query($conn, $query);
            if ($result) {
              if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $hashedPassword = $row['password'];
                if (password_verify($password, $hashedPassword)) {
                  $token = $row['api_key'];
                  $email = $row['email'];
                  $banned = $row['banned'];
                  if (!$banned == "") {
                    header('location: /auth/login?e=We are sorry but you are banned from using our system!');
                    exit; // Stop execution if user is banned
                  } else {
                    $usr_id = $row['api_key'];
                    $url = "http://ipinfo.io/" . $session->getIP() . "/json";
                    $data = json_decode(file_get_contents($url), true);
                    if (SettingsManager::getSetting('enable_anti_vpn') == "true") {
                      if (isset($data['error']) || $data['org'] == "AS1221 Telstra Pty Ltd") {
                        header('location: /auth/login?e=Hmmm it looks like you are trying to abuse. You are trying to use a VPN, which is not allowed.');
                        die();
                      }
                    }
                    $userids = array();
                    $loginlogs = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE userkey = '$usr_id'");
                    foreach ($loginlogs as $login) {
                      $ip = $login['ipaddr'];
                      if ($ip == "12.34.56.78") {
                        continue;
                      }
                      $saio = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE ipaddr = '" . $ip . "'");
                      foreach ($saio as $hello) {
                        if (in_array($hello['userkey'], $userids)) {
                          continue;
                        }
                        if ($hello['userkey'] == $usr_id) {
                          continue;
                        }
                        array_push($userids, $hello['userkey']);
                      }
                    }
                    if (SettingsManager::getSetting('enable_alting') == "true") {
                      if (count($userids) !== 0) {
                        header('location: /auth/login?e=Using multiple accounts is really sad when using free services!');
                        die();
                      }
                    }
                    $conn->query("INSERT INTO mythicaldash_login_logs (ipaddr, userkey) VALUES ('" . $session->getIP() . "', '$usr_id')");

                    $cookie_name = 'token';
                    $cookie_value = $token;
                    setcookie($cookie_name, $cookie_value, time() + (10 * 365 * 24 * 60 * 60), '/');
                    DiscordWebhookHandler::NewLogin($appURL,$row['username'],$row['email'], $row['avatar'],$session->getIP());
                    $conn->query("UPDATE `mythicaldash_users` SET `last_ip` = '" . $session->getIP() . "' WHERE `mythicaldash_users`.`api_key` = '" . $usr_id . "';");
                    if (isset($_GET['r'])) {
                      header('location: ' . $_GET['r']);
                    } else {
                      header('location: /dashboard');
                    }
                    // Stop execution after successful login
                  }
                } else {
                  header('location: /auth/login?e=Invalid Password');
                  exit; // Stop execution if password is invalid
                }
              } else {
                header('location: /auth/login?e=Invalid email');
                exit; // Stop execution if email is invalid
              }
            } else {
              header('location: /auth/login?e=Failed to log user in');
              exit; // Stop execution if login fails
            }
            mysqli_free_result($result);
            $conn->close();
            exit;
          } else {
            header("location: /auth/login?e=Captcha verification failed; please refresh!");
            die();
          }
        }
      } else {
        header('location: /auth/login?e=Login failed');
        exit; // Stop execution if login button is not pressed
      }
    } else {
      // CSRF validation failed
      setcookie('api_key', '', time() - (10 * 365 * 24 * 60 * 60 * 2), '/');
      setcookie('phpsessid', '', time() - (10 * 365 * 24 * 60 * 60 * 2), '/');
      header('location: /auth/login?e=CSRF Verification Failed');
      exit; // Stop execution if CSRF validation fails
    }
  }
} catch (Exception $e) {
  header("location: /auth/login?e=An unexpected error occurred!");
  ErrorHandler::Error("Login ", $e);
  die();
}
?>
<html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="horizontal-menu-template">

<head>
  <?php include(__DIR__ . '/../requirements/head.php'); ?>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-auth.css" />
  <title>
    <?= SettingsManager::getSetting("name") ?> - Login
  </title>

</head>

<body>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="authentication-wrapper authentication-cover authentication-bg">
    <div class="authentication-inner row">
      <div class="d-none d-lg-flex col-lg-7 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img src="<?= $appURL ?>/assets/img/illustrations/auth-login-illustration-light.png" alt="auth-login-cover"
            class="img-fluid my-5 auth-illustration"
            data-app-light-img="illustrations/auth-login-illustration-light.png"
            data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
          <img src="<?= $appURL ?>/assets/img/illustrations/bg-shape-image-light.png" alt="auth-login-cover"
            class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" />
        </div>
      </div>
      <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
        <div class="w-px-400 mx-auto">
          <h3 class="mb-1 fw-bold">Welcome to
            <?= SettingsManager::getSetting("name") ?>!
          </h3>
          <p class="mb-4">Please sign-in to your account and start the adventure</p>
          <?php
          if (isset($_GET['e'])) {
            ?>
            <div class="text-center alert alert-danger" role="alert">
              <?= $_GET['e'] ?>
            </div>
            <?php
          } else {

          }
          ?>
          <form method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email"
                autofocus />
            </div>
            <div class="mb-3 form-password-toggle">
              <?php if (SettingsManager::getSetting("enable_smtp") == "ture") {
                ?>
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password">Password</label>
                  <a href="/auth/forgot-password">
                    <small>Forgot Password?</small>
                  </a>
                </div>
                <?php
              } ?>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember-me" />
                <label class="form-check-label" for="remember-me"> Remember Me </label>
              </div>
            </div>
            <?php
            if (SettingsManager::getSetting("enable_turnstile") == "true") {
              ?>
              <center>
                <div class="cf-turnstile" data-sitekey="<?= SettingsManager::getSetting("turnstile_sitekey") ?>"></div>
              </center>
              &nbsp;
              <?php
            }
            ?>
            <?= $csrf->input('login-form'); ?>
            <button type="submit" name="login" class="btn btn-primary d-grid w-100">Sign in</button>

          </form>
          <p class="text-center">
            <span>New on our platform?</span>
            <a href="/auth/register">
              <span>Create an account</span>
            </a>
          </p>
          <div class="divider my-2">
            <div class="divider-text"> or </div>
          </div>
          <div class="auth-footer-btn d-flex justify-content-center">
            <?php
            if (SettingsManager::getSetting("enable_discord_link") == "true") {
              ?>
              <a href="/auth/discord" target="_self" class="btn btn-primary">
                <img width="18px" height="18px" src="/assets/img/discord-mark-white.svg" alt="Discord Logo">
              </a>
              <?php
            }
            ?>

          </div>
          <br>
          </p>
        </div>
      </div>
    </div>
  </div>
  <?php include(__DIR__ . '/../requirements/footer.php'); ?>
  <script src="<?= $appURL ?>/assets/js/pages-auth.js"></script>
</body>

</html>