<?php 
require("../core/require/page.php");



$password = $userdb['password'];
$stars = "";
for ($i = 1; $i <= strlen($password); $i++) {
    $stars = $stars . "*";
}
?>
<div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Credentials</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                  <li class="breadcrumb-item"><a href="/user/profile">User</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Credentials</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <div class="row justify-content-center">
        <div class="col-lg-8 card-wrapper">
          <!-- Alerts -->
            <?php
            if (isset($_SESSION["success"])) {
                echo '<div class="alert alert-success" role="alert"><strong>Success!</strong> ' . $_SESSION["success"] . '</div>';
                unset($_SESSION["success"]);
            }
            ?>
            <?php
            if (isset($_SESSION["error"])) {
                echo '<div class="alert alert-danger" role="alert"><strong>Error!</strong> ' . $_SESSION["error"] . '</div>';
                unset($_SESSION["error"]);
            }
            ?>
          <div class="card">
            <div class="card-header">
              <h3 class="mb-0"><img src="https://i.imgur.com/WtzMfm7.png" width="30"> Your credentials</h3>
            </div>
            <div class="card-body">
                <i>Theses credentials are for access to your game panel account. <span style="color: red;">Do not share theses!</span> We recommend to enable 2FA on your account.</i>
                <br/><br/>
                <div style="text-align: center;">
                    <img src="https://i.imgur.com/1e90xFP.png" width="90"><br/>
                    <div class="text-center">
                        <h3>Username: <code><?= $userdb['username'] ?></code></h3>
                        <h3>Email: <code><?= $userdb['email'] ?></code></h3>
                        <h3>Password: <code id="passwordView"><?= $stars ?></code><a href="#" onclick="viewPassword()" id="viewpassbutton" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></h3>
                        <br/><br/>
                        <a href="reset_info"><button class="btn btn-danger" type="button"><i class="fas fa fa-key"></i> Edit info </button></a>
                    </div>
                </div>
            </div>
          </div>
    </div>
  </div>
  <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-white">
                    Copyright &copy;2022-2023 <a href="https://github.com/MythicalLTD/MythicalDash" class="font-weight-bold ml-1" target="_blank">ShadowDash x MythicalDash </a> - Theme by <a href="https://creativetim.com" target="_blank">Creative Tim</a>
                </div>
            </div>
            <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["website"] ?>" class="nav-link" target="_blank"> Website</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["statuspage"] ?>" class="nav-link" target="_blank">Uptime / Status</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["privacypolicy"] ?>" class="nav-link" target="_blank">Privacy policy</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["termsofservice"] ?>" class="nav-link" target="_blank">Terms of service</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
  <!-- Argon Scripts -->
  <!-- Core -->
  <script>
      var viewingPassword = false;
      function viewPassword() {
          if (!viewingPassword) {
              document.getElementById('passwordView').innerHTML = "<?= $password ?>";
              document.getElementById('viewpassbutton').innerHTML = '<i class="fas fa-eye-slash"></i>';
              viewingPassword = true;
          } else {
              document.getElementById('passwordView').innerHTML = "<?= $stars ?>";
              document.getElementById('viewpassbutton').innerHTML = '<i class="fas fa-eye"></i>';
              viewingPassword = false;
          }
      }
  </script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</body>

</html>
