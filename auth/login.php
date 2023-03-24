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
    echo '<script>window.location.replace("/auth/login");</script>';
    die();
}

if (isset($_POST['log_user'])) {
    $ip_address = getclientip();    
    $email = mysqli_real_escape_string($cpconn, $_POST['email']);
    $password = mysqli_real_escape_string($cpconn, $_POST['password']);
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        $userdb = mysqli_fetch_assoc($result);
        if (password_verify($password, $userdb['password'])) {
            $usr_id = $userdb['user_id'];
            //VPN Check
            if ($ip_address == "127.0.0.1") {
                $ip_address = "12.34.56.78";
            }
            $url = "http://ipinfo.io/$ip_address/json";
            $data = json_decode(file_get_contents($url), true);

            if(isset($data['error']) || $data['org'] == "AS1221 Telstra Pty Ltd") {
                $_SESSION['error'] = "You are using a VPN. This is not allowed.";
                echo '<script>window.location.replace("/auth/errors/vpn");</script>';
                die();
            } 

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
                    echo '<script>window.location.replace("/auth/errors/alting");</script>';
                    die();
                }
            }

            $cpconn->query("INSERT INTO login_logs (ipaddr, userid) VALUES ('$ip_address', '$usr_id')");
            if ($userdb["banned"] == 1) {
                $_SESSION['ban_reason'] = $userdb["banned_reason"];
                session_destroy();
                echo '<script>window.location.replace("/auth/errors/banned");</script>';
                die();
            }

            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('phpsessid', '', time() - 3600, '/');
            setcookie('remember_token', $token, time() + (10 * 365 * 24 * 60 * 60), '/');
            $cpconn->query("UPDATE `users` SET `session_id` = '$token' WHERE `users`.`user_id` = $usr_id;");
            
            $username = $userdb['username'];
            logClient("[AUTH] ".$username." just logged in!");
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION["uid"] = $usr_id;
            echo '<script>window.location.replace("/");</script>';
        } else {
            $_SESSION['error'] = "The email or the password is wrong please try again!";
        }
    } 
  }
    ?>
    
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= $getsettingsdb['name']?> | Login</title>
  <link rel="icon" type="image/x-icon" href="<?= $getsettingsdb['logo']?>">

	<!-- Fonts and icons -->
	<script src="/assets/js/plugin/webfont/webfont.min.js"></script>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" media="all"><link rel="stylesheet" href="/assets/css/fonts.min.css" media="all"><script>
		WebFont.load({
			google: {"families":["Lato:300,400,700,900"]},
			custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['/assets/css/fonts.min.css']},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>

  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/atlantis.css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/auth.css">
</head>
<body>
<?php 
if (isset($_SESSION["error"])) {
	?>
	<style>
.alert {
  position: relative;
}
.alert .btn-close {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
}
.alert.fadeout {
  animation: fadeout 1s forwards;
}
@keyframes fadeout {
  from { opacity: 1; transform: translateY(0); }
  to { opacity: 0; transform: translateY(-50px); }
}
</style>

<div class="alert alert-danger" role="alert" id="myAlert">
  <strong>Error!</strong> <?= $_SESSION["error"] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<script>
  setTimeout(function() {
    var myAlert = document.querySelector('#myAlert');
    if (myAlert) {
      myAlert.classList.add('fadeout');
      setTimeout(function() {
        myAlert.remove();
      }, 1000); 
    }
  }, 3000); 
</script>
	<?php
	unset($_SESSION["error"]);
}

?>
  <div id="particles-js"></div>
  <body class="login" style="display: block; opacity: 1;">
	<div class="wrapper wrapper-login">
		<div class="container container-login animated fadeIn" style="display: block;">
		
			<h3 class="text-center">Sign In To <?= $getsettingsdb["name"]?> </h3>
			
			<form method="POST">
			<div class="login-form">
				<div class="form-group">
					<label for="email" class="placeholder"><b>Email</b></label>
					<input id="email" name="email" type="text" class="form-control" required="">
				</div>
				<div class="form-group">
					<label for="password" class="placeholder"><b>Password</b></label>
					<a href="forgot_password" class="link float-right">Forget Password ?</a>
					<div class="position-relative">
						<input id="password" name="password" type="password" class="form-control" required="">
						<div class="show-password">
							<i class="icon-eye"></i>
						</div>
					</div>
				</div>
				<div class="form-group text-center">
				    <button type="submit" name="log_user" class="btn btn-primary col-md-5 mt-3 mt-sm-0 fw-bold"><a>Sign In</a></button>
				</div>
				<div class="login-account">
					<span class="msg">Don't have an account yet ?</span>
					<a href="register" id="show-signup" class="link">Sign Up</a>
				</div>
				
			</div>
			
	    	</form>
		</div>
<!-- 

<div class="container container-signup animated fadeIn" style="display: none;">
			<h3 class="text-center">Sign Up To <?= $_CONFIG['name']?> </h3>
			<form method="GET">
			<div class="login-form">
				<div class="form-group">
					<label for="username" class="placeholder"><b>Username</b></label>
					<input id="username" name="username" type="text" class="form-control" required="">
				</div>
				<div class="form-group">
					<label for="email" class="placeholder"><b>Email</b></label>
					<input id="email" name="email" type="email" class="form-control" required="">
				</div>
				<div class="form-group">
					<label for="password" class="placeholder"><b>Password</b></label>
					<div class="position-relative">
						<input id="password" name="password" type="password" class="form-control" required="">
						<div class="show-password">
							<i class="icon-eye"></i>
						</div>
					</div>
				</div>
				<div class="row form-sub m-0">
					<div class="custom-control custom-checkbox">
						<input required type="checkbox" class="custom-control-input" name="agree" id="agree">
						<label class="custom-control-label" for="agree">I Agree the terms and conditions.</label>
					</div>
				</div>
				<div class="row form-action">
					<div class="col-md-6">
						<a href="#" id="show-signin" class="btn btn-danger btn-link w-100 fw-bold">Cancel</a>
					</div>
					<div class="col-md-6">
						<button type="submit" name="register" href="#" class="btn btn-primary w-100 fw-bold">Sign Up</button>
					</div>
				</div>
			</div>
		</form>
		</div>
-->
	</div>
  </div>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/popper.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/bootstrap.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/atlantis.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/splidejs/4.1.4/js/splide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script>
    particlesJS.load('particles-js', '<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/json/particles.json', function() {
      console.log('callback - particles.js config loaded');
    });
  </script>
</body>
</html>

<!--
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <title><?= $getsettingsdb["name"] ?> - Login</title>
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/css/fontawesome.css" type="text/css">
  <link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/css/argon.css" type="text/css">
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
              <form method="POST">
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
                <div class="text-center">
                  <button type="submit" name="log_user" value="ok" class="btn btn-primary my-4 ">Login</button>
                  
                </div>
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
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/jquery-scrollLock.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>//assets/js/argon.js?v=1.2.0"></script>
</body>

</html>


<script>
    var icon = document.getElementById("show-password");
    var passwordInput = document.getElementById("password");
    icon.addEventListener("click", function() {
        if (passwordInput.getAttribute("type") === "password") {
            passwordInput.setAttribute("type", "text");
        } else {
            passwordInput.setAttribute("type", "password");
           
        }
    });
  </script>-->