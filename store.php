<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("core/require/page.php");
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


if (isset($_POST["buycpu"])) {
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

if (isset($_POST["buyram"])) {
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

if (isset($_POST["buydisk"])) {
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

if (isset($_POST["buysv"])) {
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

if(isset($_POST["buyport"])) {
  if ($usr_coins >= $portsprice) {
    $newcoins = $usr_coins - $portsprice;
    $newport = $usr_ports + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `ports` = '" . $newport . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store");</script>';
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!";
  }
}


if(isset($_POST['buydata'])) {
  if ($usr_coins >= $databaseprice)
  {
    $newcoins = $usr_coins - $databaseprice;
    $newdb = $usr_databases + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `databases` = '" . $newdb . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store");</script>';
  }
  else
  {
    $_SESSION['error'] = "You dont have coins to buy this!"; 
  }
}

if(isset($_POST['buyback'])) {
  if ($usr_coins >= $backupprice)
  {
    $newcoins = $usr_coins - $backupprice;
    $newbk = $usr_backup_limit + "1";
    mysqli_query($cpconn, "UPDATE `users` SET `backup_limit` = '" . $newbk . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
    $_SESSION['success'] = "Thanks for your purchase, we updated your resources!";
    echo '<script>window.location.replace("/store");</script>';
  }
}

?>

<head>
    <title><?= $getsettingsdb['name']?> | Store</title>
    <?php include 'core/imports/header.php'; ?>
</head>

<body data-background-color="dark">
    <div class="wrapper">
        <div class="main-header">
            <div class="logo-header" data-background-color="dark2">
                <a href="/" class="logo">
                    <p style="color:white;" class="navbar-brand"><?= $getsettingsdb["name"]?></p>
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <nav class="navbar navbar-header navbar-expand-lg" data-background-color="dark">

                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
                                aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="<?= $userdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="<?= $userdb['avatar']?>"
                                                    alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h4><?= $userdb['username']?></h4>
                                                <p class="text-muted"><?= $userdb['role']?></p>
                                                <p class="text-muted">Coins: <?= $userdb['coins']?></p>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/regen">Reset Password</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/auth/logout">Logout</a>
                                    </li>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="sidebar sidebar-style-2" data-background-color="dark2">
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="avatar-sm float-left mr-2">
                            <img src="<?= $userdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    <?= $userdb['username']?>

                                    <span class="user-level"><?= $userdb['role']?></span>

                                </span>
                            </a>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Overview</h4>
                        </li>
                        <li class="nav-item ">
                            <a href="/" class="collapsed">
                                <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/server/create" class="collapsed">
                                <i class="fas fa-plus-square"></i>
                                <p>New Server</p>
                            </a>
                        </li>
                        <li class="nav-item active  ">
                            <a href="/store" class="collapsed">
                                <i class="fas fa-shopping-bag"></i>
                                <p>Shop</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="collapse" href="#earn">
                                <i class="fas fa-coins"></i>
                                <p>Earn</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="earn">
                                <ul class="nav nav-collapse">
                                    <li class="nav-item">
                                        <a href="/earn/afk" class="collapsed">
                                            <p>AFK</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/earn/linkvertise" class="collapsed">
                                            <p>Linkvertise</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/earn/redeem" class="collapsed">
                                            <p>Redeem</p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Links</h4>
                        </li>
                        <?php 
                        if ($getsettingsdb['enable_mainwebsite'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['website']?>" class="collapsed">
                                <i class="bi bi-house-fill"></i>
                                <p>Website</p>
                            </a>
                        </li>
                        <?php 
                            
                        }
                        
                        if ($getsettingsdb['enable_discord'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['discordserver']?>" class="collapsed">
                                <i class="bi bi-discord"></i>
                                <p>Discord</p>
                            </a>
                        </li>
                        <?php
                        }

                        if ($getsettingsdb['enable_phpmyadmin'] == "false")
                        {

                        }
                        else
                        {
                            
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['phpmyadmin']?>" class="collapsed">
                                <i class="bi bi-server"></i>
                                <p>PhpMyAdmin</p>
                            </a>
                        </li>
                        <?php
                        }
                        if ($getsettingsdb['enable_status'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['statuspage']?>" class="collapsed">
                                <i class="fas fa-signal"></i>
                                <p>Status</p>
                            </a>
                        </li>
                        <?php
                        }
                        ?>

                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['ptero_url']?>" class="collapsed">
                                <i class="fas fa-external-link-square-alt"></i>
                                <p>Panel</p>
                            </a>
                        </li>


                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Administrative Overview</h4>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="main-panel">
            <div class="container">
                <div class="content">
                    <div class="page-inner">
                        <div class="mt-2 mb-4">
                            <h2 class="text-white pb-2">Welcome back, <?= $userdb['username']?>!</h2>
                        </div>
                        <div class="row">
                        <?php include('core/imports/resources.php');?>

                        </div>
                        <?php         if (isset($_SESSION["error"])) {
            ?>
                        <div class="alert alert-danger text-danger" role="alert">
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
                        <div class="container-fluid mt--6">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="mb-0"><img src="https://i.imgur.com/2WYzXDV.png" width="30">
                                                Coins shop</h3>

                                        </div>

                                        <div class="card-body" style="text-align: center;">
                                            <div class="row">
                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="https://i.imgur.com/b6TNCeZ.png" alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>CPU</a>
                                                            </h4>
                                                            <h5><?= $cpuprice ?>€</h5>
                                                            <p class="card-text">For every <?= $cpuprice ?> coins you
                                                                get 1VCore to use on your server / bot.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buycpu" value="yes"
                                                                    class="btn btn-primary btn-block ">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="https://i.imgur.com/sxZ4OB4.png" alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>RAM</a>
                                                            </h4>
                                                            <h5><?= $ramprice ?>€</h5>
                                                            <p class="card-text">For every <?= $ramprice ?> coins you
                                                                get 1GB ram to use on your application.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buyram" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="https://i.imgur.com/N0MwF0M.png" alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>Disk</a>
                                                            </h4>
                                                            <h5><?= $diskprice ?>€</h5>
                                                            <p class="card-text">For every <?= $diskprice ?> coins you
                                                                get 1GB disk to use on your application.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buydisk" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="https://i.imgur.com/3w5wt0k.png" alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>Server Slot</a>
                                                            </h4>
                                                            <h5><?= $svprice ?>€</h5>

                                                            <p class="card-text">For every <?= $svprice ?> coins you get
                                                                1 server slot to deploy your application.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buysv" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- Row 2 -->
                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/img/internet_hub_480px.png"
                                                                alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>Ports</a>
                                                            </h4>
                                                            <h5><?= $portsprice ?>€</h5>
                                                            <p class="card-text">For every <?= $portsprice ?> coins you
                                                                get 1 more port to use on your server</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buyport" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/img/synchronize_480px.png"
                                                                alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>Backup</a>
                                                            </h4>
                                                            <h5><?= $backupprice ?>€</h5>
                                                            <p class="card-text">For every <?= $backupprice ?> coins you
                                                                get 1 backup slot to backup your server / bot.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buyback" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-9">
                                                    <div class="h-100 text-center">
                                                        <a class="mx-auto text-center"><img class="card-img-top"
                                                                src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/img/mysql_logo_480px.png"
                                                                alt=""></a>
                                                        <div class="card-body">
                                                            <h4 class="card-title">
                                                                <a>Database</a>
                                                            </h4>
                                                            <h5><?= $databaseprice ?>€</h5>
                                                            <p class="card-text">For every <?= $databaseprice ?> coins
                                                                you get 1 database to use on your application.</p>
                                                        </div>
                                                        <form action="resources" method="POST">
                                                            <div class="card-footer">
                                                                <button name="buydata" value="yes"
                                                                    class="btn btn-primary btn-block">Buy</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    

                                </div>
                            </div>
                        </div>
                        <?php include('core/imports/footer.php');?>
                    </div>

                    </html>