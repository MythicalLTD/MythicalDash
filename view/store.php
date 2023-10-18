<?php
include(__DIR__ . '/requirements/page.php');
if ($userdb['panel_id'] == "CLI") {
    header('location: /admin/settings');
}
$cpuprice = $settings["price_cpu"];
$ramprice = $settings["price_memory"];
$diskprice = $settings["price_disk_space"];
$svprice = $settings["price_server_limit"];
$portsprice = $settings["price_allocation"];
$databaseprice = $settings["price_database"];
$backupprice = $settings["price_backup"];

$usr_coins = $userdb['coins'];
$usr_cpu = $userdb["cpu"];
$usr_ram = $userdb["ram"];
$usr_disk = $userdb["disk"];
$usr_svlimit = $userdb["server_limit"];
$usr_ports = $userdb["ports"];
$usr_databases = $userdb["databases"];
$usr_backup_limit = $userdb["backups"];

if (isset($_GET["buycpu"])) {
    if ($usr_coins >= $cpuprice) {
        $newcoins = $usr_coins - $cpuprice;
        $newcpu = $usr_cpu + "100";
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '" . $newcpu . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

if (isset($_GET["buyram"])) {
    if ($usr_coins >= $ramprice) {
        $newcoins = $usr_coins - $ramprice;
        $newram = $usr_ram + "1024";
        $conn->query("UPDATE `mythicaldash_users` SET `ram` = '" . $newram . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

if (isset($_GET["buydisk"])) {
    if ($usr_coins >= $diskprice) {
        $newcoins = $usr_coins - $diskprice;
        $newdisk = $usr_disk + "1024";
        $conn->query("UPDATE `mythicaldash_users` SET `disk` = '" . $newdisk . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

if (isset($_GET["buysv"])) {
    if ($usr_coins >= $svprice) {
        $newcoins = $usr_coins - $svprice;
        $newsv = $usr_svlimit + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '" . $newsv . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

if (isset($_GET["buyport"])) {
    if ($usr_coins >= $portsprice) {
        $newcoins = $usr_coins - $portsprice;
        $newport = $usr_ports + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `ports` = '" . $newport . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}


if (isset($_GET['buydata'])) {
    if ($usr_coins >= $databaseprice) {
        $newcoins = $usr_coins - $databaseprice;
        $newdb = $usr_databases + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `databases` = '" . $newdb . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

if (isset($_GET['buyback'])) {
    if ($usr_coins >= $backupprice) {
        $newcoins = $usr_coins - $backupprice;
        $newbk = $usr_backup_limit + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `backups` = '" . $newbk . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
        header("location: /store?s=Thank you for your purchase. We updated your resources!");
        $conn->close();
        die();
    } else {
        header("location: /store?e=You need more coins to buy this!");
        die();
    }
}

?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Store
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Store</h4>
                        <?php include(__DIR__ . '/components/alert.php') ?>
                        <br>
                        <div id="ads">
                            <?php
                            if ($settings['enable_ads'] == "true") {
                                echo $settings['ads_code'];
                            }
                            ?>
                        </div>
                        <br>
                        <div class="row mb-5">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/b6TNCeZ.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Cpu</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_cpu'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_cpu'] ?> coins you get 1 CPU core to use on your
                                                server.
                                            </p>
                                            <a href="/store?buycpu" class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/sxZ4OB4.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Ram</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_memory'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_memory'] ?>
                                                coins you get 1GB ram to use on your server.
                                            </p>
                                            <a href="/store?buyram" class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/N0MwF0M.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Disk</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_disk_space'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_disk_space'] ?> coins you get 1GB disk to use on
                                                your server.
                                            </p>
                                            <a href="/store?buydisk"
                                                class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/3w5wt0k.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Server Slot</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_server_limit'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_server_limit'] ?> coins you get 1 server slot to
                                                deploy your server.
                                            </p>
                                            <a href="/store?buysv" class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/BviNTIf.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Server Backup</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_backup'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_backup'] ?> coins you get 1 backup slot to backup
                                                your server.
                                            </p>
                                            <a href="/store?buyback"
                                                class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/NnaxtzB.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Server Allocation</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_allocation'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_allocation'] ?> coins you get 1 extra port to use
                                                on your server.
                                            </p>
                                            <a href="/store?buyport"
                                                class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/F7HTHUN.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">Server Database</h5>
                                            <small class="text-muted">
                                                <code><?= $settings['price_database'] ?> Coins</code>
                                            </small>
                                            <p class="card-text">
                                                For every
                                                <?= $settings['price_database'] ?> coins you get 1 database to use on
                                                your server.
                                            </p>
                                            <a href="/store?buydata"
                                                class="btn btn-outline-primary waves-effect">Buy</a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <br>
                            <div id="ads">
                                <?php
                                if ($settings['enable_ads'] == "true") {
                                    echo $settings['ads_code'];
                                }
                                ?>
                            </div>
                            <br>
                        </div>
                    </div>
                    <?php include(__DIR__ . '/components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>