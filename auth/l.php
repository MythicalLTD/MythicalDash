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
if(isset($_GET['login'])) {
    $ip_address = getclientip();    
    $email = mysqli_real_escape_string($cpconn, $_POST['email']);
    $password = mysqli_real_escape_string($cpconn, $_POST['password']);
    //$query = "SELECT * FROM users WHERE email='$email'";

}
if(isset($_GET['err'])) {
    
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
			
			<form method="GET">
			<div class="login-form">
				<div class="form-group">
					<label for="email" class="placeholder"><b>Email</b></label>
					<input id="email" name="email" type="email" class="form-control" required="">
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
				    <button type="submit" name="login" class="btn btn-primary col-md-5 mt-3 mt-sm-0 fw-bold"><a>Sign In</a></button>
				</div>
				<div class="login-account">
					<span class="msg">Don't have an account yet ?</span>
					<a href="register" id="show-signup" class="link">Sign Up</a>
				</div>
				
			</div>
			
	    	</form>
		</div>
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
      console.log('Callback - particles.js config loaded');
    });
  </script>
</body>
</html>