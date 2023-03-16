<?php
ini_set("display_errors", 1);
require('../core/require/page.php');
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . $_SESSION['uid'] . "'")->fetch_object();
$referral = mysqli_query($cpconn, "SELECT * FROM referral_codes WHERE uid = '$userdb->user_id'")->fetch_object()->referral;
$referrals = mysqli_query($cpconn, "SELECT * FROM referral_claims WHERE code = '$referral'");
?>
<!-- Header -->
    <div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Referrals</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                  <li class="breadcrumb-item"><a href="/user/profile">User</a></li>
                  <li class="breadcrumb-item active" aria-current="page">My Referrals</li>
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
            <div class="card-body text-center">
                <h1>Your referral code is <code><?= $referral ?></code></h1>
                <h2>For every friend you invite you are going to get <code><?= $getsettingsdb['coinsref']?></code> coins</h2>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="mb-0"><img src="https://i.imgur.com/6ZBcn3p.png" width="30"> Your referrals</h3>
            </div>
            <div class="card-body">
            <div class="table-responsive">
                  <table class="table table-hover-animation mb-0 text-white">
                      <thead>
                      <tr>
                          <th class="col-1">ID</th>
                          <th class="col-1">User</th>
                          <th class="col-1">Time</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php
                      foreach($referrals as $refer) {
                          $referred = $refer['uid'];
                          $user = mysqli_query($cpconn, "SELECT username FROM users WHERE user_id = '$referred'")->fetch_object()->username;
                          ?>
                      <tr>
                          <td><?= $refer['id'] ?></td>
                          <td><?= $user ?></td>
                          <td><?= date("d/m/y H:i:s", $refer['timestamp']) ?></td>
                      </tr>
                      <?php
                      }
                      ?>
                      </tbody>
                  </table>
              </div>
            </div>
          </div>
    </div>
  </div>

      <!-- Footer -->
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-muted">
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
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</div>

</html>
