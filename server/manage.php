<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/page.php");
$serverid = $_GET["id"];
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (!is_numeric($serverid)) {
    $_SESSION['error'] = "This server doesn't exist.";
    echo '<script>window.location.replace("/");</script>';
    die();
}
// get current server info
$ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/$serverid");
$headers = array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $getsettingsdb["ptero_apikey"]
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($ch);
curl_close($ch);
unset($ch);
$result = json_decode($result, true);
$currentName = $result['attributes']['name'];
$currentMemory = $result['attributes']['limits']['memory'];
$currentDisk = $result['attributes']['limits']['disk'];
$currentCpu = $result['attributes']['limits']['cpu'];
$currentPorts = $result['attributes']['feature_limits']['allocations']-1;
$currentDatabases = $result['attributes']['feature_limits']['databases'];
$currentStartupCommand = $result['attributes']['container']['startup_command'];
$currentBackups = $result['attributes']['feature_limits']['backups'];
$currentAllocation = $result['attributes']['allocation'];
$currentDescription = $result['attributes']['description'];
unset($result);

// get user current ram
$usedram = 0;
$usedcpu = 0;
$useddisk = 0;
$usedports = 0;
$useddatabases = 0;
$usedbackup = 0;
$servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '".$_SESSION["uid"]."'");
foreach($servers as $server) {
    $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $server['pid']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $getsettingsdb["ptero_apikey"]
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    $result = json_decode($result, true);
    $usedram = $result['attributes']['limits']['memory'] + $usedram;
    $cpu = $result['attributes']['limits']['cpu'];
    $usedcpu = $usedcpu+$cpu;
    $useddisk = $useddisk + $result['attributes']['limits']['disk'];
    $ports = $result['attributes']['feature_limits']['allocations'] - 1;
    $usedports = $usedports+$ports;
    $useddatabases = $useddatabases+$result['attributes']['feature_limits']['databases'];
    $usedbackup = $result['attributes']['feature_limits']['backups'];


}
$serversinqueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '".$_SESSION["uid"]."'");
foreach($serversinqueue as $server) {
    $usedram = $usedram + $server['ram'];
    $useddisk = $useddisk + $server['disk'];
    $usedports = $usedports + $server['xtra_ports'];
    $useddatabases = $useddatabases + $server['databases'];
    $usedcpu = $usedcpu + $server['cpu'];
    $usedbackup = $usedbackup + $server['backuplimit'];
}
$useddisk1 = $useddisk;
$useddb1 = $useddatabases;
$usedports1 = $usedports;
$usedram = $usedram - $currentMemory;
$useddisk = $useddisk - $currentDisk;
$usedports = $usedports - $currentPorts;
$useddatabases = $useddatabases - $currentDatabases;
$freeram = $userdb["memory"] - $usedram;
$freedisk = $userdb["disk_space"] - $useddisk;
$freeports = $userdb["ports"] - $usedports;
$freedatabases = $userdb["databases"] - $useddatabases;
$freebackup = $userdb["backup_limit"] - $usedbackup;
// check server exist
$server = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '".$_SESSION["uid"]."' AND pid = '$serverid'");
if ($server->num_rows == 0) {
    $_SESSION['error'] = "This server doesn't exist or you don't have access to it.";
    echo '<script>window.location.replace("/");</script>';
    die();
}
if (isset($_POST['submit'])) {
    if (isset($_POST['memory'], $_POST['cores'], $_POST['disk'], $_POST['ports'], $_POST['databases'], $_POST['backups'])) {
        if ($_POST['memory'] < 256) {
            $_SESSION['error'] = "Minimum memory is 256MB";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['disk'] < 256) {
            $_SESSION['error'] = "Minimum disk is 256MB";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['cores'] < 10) {
            $_SESSION['error'] = "Minimum cores is 0.10";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['ports'] < 0) {
            $_SESSION['error'] = "Minimum ports is 0.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['backups'] < 0) {
            $_SESSION['error'] = "Minimum backup is 0.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['databases'] < 0) {
            $_SESSION['error'] = "Minimum ports is 0.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['memory'] > $freeram) {
            $_SESSION['error'] = "You don't have enough memory.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['cores'] > ($userdb["cpu"] - $usedcpu) + $currentCpu) {
            $_SESSION['error'] = "You don't have enough cpu.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        if ($_POST['disk'] > $freedisk) {
            if ($useddisk1 > $userdb["disk_space"]) {
                if ($_POST['disk'] > $currentDisk) {
                    $_SESSION['error'] = "Your in debt, you cannot increase disk.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
                if ($_POST['disk'] == $currentDisk) {
                    $_SESSION['error'] = "You must reduce you're disk as your in debt.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
            }
            else {
                $_SESSION['error'] = "You don't have enough disk.";
                echo '<script>window.location.replace("/");</script>';
                die();
            }

        }
        if ($_POST['ports'] > $freeports) {
            if ($usedports1 > $userdb["ports"]) {
                if ($_POST['ports'] > $currentPorts) {
                    $_SESSION['error'] = "Your in debt, you cannot increase ports.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
                if ($_POST['ports'] == $currentPorts) {
                    $_SESSION['error'] = "You must reduce you're ports as your in debt.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
            }
            else {
                $_SESSION['error'] = "You don't have enough ports.";
                echo '<script>window.location.replace("/");</script>';
                die();
            }
        }
        if ($_POST['databases'] > $freedatabases) {
            if ($useddatabases > $userdb["databases"]) {
                if ($_POST['databases'] > $currentDatabases) {
                    $_SESSION['error'] = "Your in debt, you cannot increase databases.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
                if ($_POST['databases'] == $currentDatabases) {
                    $_SESSION['error'] = "You must reduce you're databases as your in debt.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
            }
            else {
                $_SESSION['error'] = "You don't have enough databases.";
                echo '<script>window.location.replace("/");</script>';
                die();
            }
        }
        if ($_POST['backups'] > $freebackup) {
            if ($usedbackup > $userdb["backup_limit"]) {
                if ($_POST['backups'] > $currentBackups) {
                    $_SESSION['error'] = "Your in debt, you cannot increase backups.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
                if ($_POST['backups'] == $currentBackups) {
                    $_SESSION['error'] = "You must reduce you're backups as your in debt.";
                    echo '<script>window.location.replace("/");</script>';
                    die();
                }
            }
            else {
                $_SESSION['error'] = "You don't have enough backups.";
                echo '<script>window.location.replace("/");</script>';
                die();
            }
        }
        // change server resources
        $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $serverid . "/build");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $getsettingsdb["ptero_apikey"]
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'allocation' => $currentAllocation,
            'memory' => $_POST['memory'],
            'swap' => $_POST['memory'],
            'disk' => $_POST['disk'],
            'io' => 500,
            'cpu' => $_POST['cores'],
            'threads' => null,
            'feature_limits' => array(
                'databases' => $_POST['databases'],
                'allocations' => $_POST['ports']+1,
                'backups' => $_POST['backups']
            )
        )));
        $result = curl_exec($ch);
        curl_close($ch);
        unset($ch);
        $result = json_decode($result, true);
        if (!isset($result['object'])) {
            $_SESSION['error'] = "There was an unexpected error while editing your server's limits.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        else {
            $_SESSION['success'] = "Changed your server.";
            echo '<script>window.location.replace("/");</script>';
            die();
        }

    }
}
?>

<head>
    <title><?= $getsettingsdb['name'] ?> | Edit</title>
    <?php include('../core/imports/header.php');?>
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
                        <li class="nav-item">
                            <a href="/" class="collapsed">
                                <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="/server/create" class="collapsed">
                                <i class="fas fa-plus-square"></i>
                                <p>New Server</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/store" class="collapsed">
                                <i class="fas fa-shopping-bag"></i>
                                <p>Shop</p>
                            </a>
                        </li>

                        <li class="nav-item ">
                            <a data-toggle="collapse" href="#earn">
                                <i class="fas fa-coins"></i>
                                <p>Earn</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="earn">
                                <ul class="nav nav-collapse">
                                    <li class="nav-item ">
                                        <a href="/earn/afk" class="collapsed">
                                            <p class="">AFK</p>
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
                        <?php include('../core/imports/resources.php');?>
                        </div>
                        <?php if (isset($_SESSION["error"])) {
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
            <div class="alert alert-success text-danger" role="alert">
                <strong>Success!</strong> <?= $_SESSION["success"] ?>
            </div>
            <?php
            unset($_SESSION["success"]);
        }
        ?>
         <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Edit Server</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <p>Name: <b><?= htmlspecialchars($currentName) ?></b> <a href="rename?id=<?= $serverid ?>">here</a></p>
                          
                                        <p>Startup Command: <code><?= $currentStartupCommand ?></code></p>
                                        <p>RAM:&nbsp;&nbsp;&nbsp;<input class="form-control" id="ram" name="memory" value="<?= $currentMemory ?>"></p>
                                        <p>Disk:&nbsp;&nbsp;&nbsp;<input class="form-control" id="disk" name="disk" value="<?= $currentDisk ?>"></p>
                                        <p>CPU:&nbsp;&nbsp;&nbsp;<input class="form-control" id="cpu" name="cores" value="<?= $currentCpu ?>"></p>
                                        <p>Allocations:&nbsp;&nbsp;&nbsp;<input class="form-control" name="ports" id="ports" value="<?= $currentPorts ?>"></p>
                                        <p>Databases:&nbsp;&nbsp;&nbsp;<input class="form-control" name="databases" id="databases" value="<?= $currentDatabases ?>"></p>
                                        <p>Backups:&nbsp;&nbsp;&nbsp;<input class="form-control" name="backups" id="backups" value="<?= $currentBackups ?>"></p>

                                        <button name="submit" type="submit" class="btn btn-primary" style="background-color: #33404D;">Modify</button>
                                        <br>
                                    </form>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
    <?php include('../core/imports/footer.php');?>
    </body>

    </html>