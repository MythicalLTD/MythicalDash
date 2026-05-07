<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/page.php");
if($getperms['canbuy'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to buy resources";
    die;
}

$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$cpuprice = $getsettingsdb["cpuprice"];
$ramprice = $getsettingsdb["ramprice"];
$diskprice = $getsettingsdb["diskprice"];
$svprice = $getsettingsdb["svslotprice"];
$portsprice = $getsettingsdb["portsprice"];
$databaseprice = $getsettingsdb["databaseprice"];
$backupprice = $getsettingsdb["backupprice"];

$usr_coins = $usrdb['coins'];
$usr_cpu = $usrdb["cpu"];
$usr_ram = $usrdb["memory"];
$usr_disk = $usrdb["disk_space"];
$usr_svlimit = $usrdb["server_limit"];
$usr_ports = $userdb["ports"];
$usr_databases = $userdb["databases"];
$usr_backup_limit = $userdb["backup_limit"];


if (isset($_GET["buycpu"])) {
  if ($usr_coins >= $cpuprice) {
    //new coins
    $newcoins = $usr_coins - $cpuprice;
    $newcpu = $usr_cpu + "100";
    mysqli_query($cpconn, "UPDATE `users` SET `cpu` = '" . $newcpu . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}

if (isset($_GET["buyram"])) {
  if ($usr_coins >= $ramprice) {
    //new coins
    $newcoins = $usr_coins - $ramprice;
    $newram = $usr_ram + "1024";
    mysqli_query($cpconn, "UPDATE `users` SET `memory` = '" . $newram . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}

if (isset($_GET["buydisk"])) {
  if ($usr_coins >= $diskprice) {
    //new coins
    $newcoins = $usr_coins - $diskprice;
    $newdisk = $usr_disk + "1024";
    mysqli_query($cpconn, "UPDATE `users` SET `disk_space` = '" . $newdisk . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}

if (isset($_GET["buysv"])) {
  if ($usr_coins >= $svprice) {
    //new coins
    $newcoins = $usr_coins - $svprice;
    $newsv = $usr_svlimit + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `server_limit` = '" . $newsv . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}

if(isset($_GET["buyport"])) {
  if ($usr_coins >= $portsprice) {
    $newcoins = $usr_coins - $portsprice;
    $newport = $usr_ports + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `ports` = '" . $newport . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store/resources");</script>';
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}


if(isset($_GET['buydata'])) {
  if ($usr_coins >= $databaseprice)
  {
    $newcoins = $usr_coins - $databaseprice;
    $newdb = $usr_databases + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `databases` = '" . $newdb . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store/resources");</script>';
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!"; 
  }
}

if(isset($_GET['buyback'])) {
  if ($usr_coins >= $backupprice)
  {
    $newcoins = $usr_coins - $backupprice;
    $newbk = $usr_backup_limit + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `backup_limit` = '" . $newbk . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store/resources");</script>';
  }
}
?>
<style>
.card-img-top {
 height: 140px;
 width: 160px;
}
.card-footer {
  display: flex;
  justify-content: space-between;
}
</style>

<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Coins shop</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Coins shop</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<input id="node" name="node" type="hidden" value="">


<div class="container-fluid mt--6">
    <div class="row justify-content-center">
    <div class="col-md-12">
            </div>
        <div class="col-lg-8 card-wrapper">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><img src="https://i.imgur.com/2WYzXDV.png" width="30"> Coins shop</h3>
                    
                </div>
                
                <div class="card-body" style="text-align: center;">
                <?php
                if (isset($_SESSION["error"])) {
                    ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error!</strong> <?= $_SESSION["error"] ?>
                    </div>
                    <?php
                    unset($_SESSION["error"]);
                }
                ?>
                <?php
                if (isset($_SESSION["success"])) {
                    ?>
                    <div class="alert alert-success" role="alert">
                        <strong>Success!</strong> <?= $_SESSION["success"] ?>
                    </div>
                    <?php
                    unset($_SESSION["success"]);
                }
                ?>
                    <div class="row">
                          <div class="col-lg-3 col-md-6 mb-9">
                            <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="https://i.imgur.com/b6TNCeZ.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>CPU</a>
                                </h4>
                                <h5><?= $cpuprice ?>€</h5>
                                <p class="card-text">For every <?= $cpuprice ?> coins you get 1VCore to use on your server / bot.</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buycpu" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                          </div>
                       
                          <div class="col-lg-3 col-md-6 mb-9">
                             <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="https://i.imgur.com/sxZ4OB4.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>RAM</a>
                                </h4>
                                <h5><?= $ramprice ?>€</h5>
                                <p class="card-text">For every <?= $ramprice ?> coins you get 1GB ram to use on your application.</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buyram" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                          </div>
                        <div class="col-lg-3 col-md-6 mb-9">
                            <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="https://i.imgur.com/N0MwF0M.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>Disk</a>
                                </h4>
                                <h5><?= $diskprice ?>€</h5>
                                <p class="card-text">For every <?= $diskprice ?> coins you get 1GB disk to use on your application.</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buydisk" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-9">
                          <div class="h-100 text-center">
                            <a class="mx-auto text-center"><img class="card-img-top" src="https://i.imgur.com/3w5wt0k.png" alt=""></a>
                            <div class="card-body">
                              <h4 class="card-title">
                                <a>Server Slot</a>
                              </h4>
                              <h5><?= $svprice ?>€</h5>
                              
                              <p class="card-text">For every <?= $svprice ?> coins you get 1 server slot to deploy your application.</p>
                            </div>
                            <form action="resources" method="GET">
                            <div class="card-footer">
                              <button name="buysv" value="yes" class="btn btn-primary btn-block">Buy</button>
                            </div>
                            </form>
                          </div>
                        </div>
                        <!-- Row 2 -->
                        <div class="col-lg-3 col-md-6 mb-9">
                            <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/internet_hub_480px.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>Ports</a>
                                </h4>
                                <h5><?= $portsprice ?>€</h5>
                                <p class="card-text">For every <?= $portsprice ?> coins you get 1 more port to use on your server</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buyport" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                          </div>
                       
                          <div class="col-lg-3 col-md-6 mb-9">
                             <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/synchronize_480px.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>Backup</a>
                                </h4>
                                <h5><?= $backupprice ?>€</h5>
                                <p class="card-text">For every <?= $backupprice ?> coins you get 1 backup slot to backup your server / bot.</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buyback" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                          </div>
                        <div class="col-lg-3 col-md-6 mb-9">
                            <div class="h-100 text-center">
                              <a class="mx-auto text-center"><img class="card-img-top" src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/mysql_logo_480px.png" alt=""></a>
                              <div class="card-body">
                                <h4 class="card-title">
                                  <a>Database</a>
                                </h4>
                                <h5><?= $databaseprice ?>€</h5>
                                <p class="card-text">For every <?= $databaseprice ?> coins you get 1 database to use on your application.</p>
                              </div>
                              <form action="resources" method="GET">
                              <div class="card-footer">
                                <button name="buydata" value="yes" class="btn btn-primary btn-block">Buy</button>
                              </div>
                              </form>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
         
        </div>
    </div>
</div>
<footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-muted">
                    Copyright &copy;2020-2023 <a href="https://github.com/MythicalLTD/MythicalDash" class="font-weight-bold ml-1" target="_blank">ShadowDash x MythicalDash </a> - Theme by <a href="https://creativetim.com" target="_blank">Creative Tim</a>
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
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/argon.js?v=1.2.0"></script>
</div>

</html>
