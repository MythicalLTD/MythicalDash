<?php 
require('../core/require/page.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $userdbd = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_GET["id"]) . "'")->fetch_array();
}
else if (isset($_GET['uid']))
{
    $currentuser =  $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
    $giftuser = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_GET["uid"]) . "'")->fetch_array();
    $coinsam = mysqli_real_escape_string($cpconn, $_GET["coinsv"]);
    if ($currentuser['coins'] >= $coinsam)
    {
        $newcoinsforcurrentuser = $currentuser['coins'] - $coinsam;
        $newcoinsforgiftuser = $coinsam + $giftuser['coins'];
        $cpconn->query("UPDATE `users` SET `coins` = '".$newcoinsforcurrentuser."' WHERE `users`.`id` = ".$currentuser['id'].";");
        $cpconn->query("UPDATE `users` SET `coins` = '".$newcoinsforgiftuser."' WHERE `users`.`id` = ".$giftuser['id'].";");
        echo '<script>window.location.replace("/");</script>';
        $_SESSION['success'] = "Done!";
        die;
    }
    else
    {
            echo '<script>window.location.replace("/");</script>';
            $_SESSION['error'] = "You dont have coins to buy this!";
            die;
    }
}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "We can`t find an user id!";
    die;
}

?>
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Gift Coins </h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Users / Gift Coins</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">

<div class="container-fluid mt--6">
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <section id="dashboard-analytics">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">Gift coins for the user "<?= $userdbd['username']?>"</h4>
                                <h5 class="mb-0 text-center">Want to see his profile?  <a href="profile?id=<?= $_GET['id'] ?>">Do it right here</a></h5>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <label for="coinsv">Coins:</label>
                                    <input type="number" class="form-control" name="coinsv" value="1" required>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="uid" value="<?= $_GET['id'] ?>" type="submit">Gift</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>
        </div>
        <div class="col-md-10">
    </div>
</div>

<!-- END: Content-->

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
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
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</body>

</html>