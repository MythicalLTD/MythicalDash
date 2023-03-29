<?php 
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
require("../core/require/page.php");
require("../core/require/addons.php");

if($getperms['cancreate'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to create servers";
    die;
}


$muserdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
if (isset($_GET['createsv'])) {
    $queue = 0;
    $userdb = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . $_SESSION['uid'] . "'")->fetch_object();
    $ramLimit = $userdb->memory;
    $cpuLimit = $userdb->cpu;
    $diskLimit = $userdb->disk_space;
    $serverLimit = $userdb->server_limit;
    $usedRam = 0;
    $usedDatabase = 0;
    $usedPorts = 0;
    $usedDisk = 0;
    $usedBackups = 0;
    $usedCpu = 0;
    $servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '".$_SESSION["uid"]."'");
    $servers_in_queue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
    if ($servers_in_queue->num_rows >= 2) {
        $_SESSION['error'] = "You cannot have more than two servers in queue.";
    }
    foreach($servers as $serv) {
        $ptid = $serv["pid"];
        $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $ptid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $getsettingsdb["ptero_apikey"],
            "Content-Type: application/json",
            "Accept: application/json"
        ));
        $result1 = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result1, true);
        $ram = $result['attributes']['limits']['memory'];
        $disk = $result['attributes']['limits']['disk'];
        $ports = $result['attributes']['feature_limits']['allocations'] - 1;
        $databases = $result['attributes']['feature_limits']['databases'];
        $backups = $result['attributes']['feature_limits']['backups'];
        $cpuh = $result['attributes']['limits']['cpu'];
        $usedDatabase = $usedDatabase + $databases;
        $usedPorts = $usedPorts + $ports;
        $usedCpu = $usedCpu + $cpuh;
        $usedRam = $usedRam + $ram;
        $usedDisk = $usedDisk + $disk;
        $usedBackups = $usedBackups + $backups;
        
    }
    foreach($servers_in_queue as $server) {
        $usedRam = $usedRam + $server['ram'];
        $usedDisk = $usedDisk + $server['disk'];
        $usedPorts = $usedPorts + $server['xtra_ports'];
        $usedDatabase = $usedDatabase + $server['databases'];
        $usedBackups = $usedBackups + $server['backuplimit'];
    
    
    }
    $freeRam = $ramLimit - $usedRam;
    $freeDisk = $diskLimit - $usedDisk;
    $freePorts = $userdb->ports - $usedPorts;
    $freeDatabases = $userdb->databases - $usedDatabase;
    $freeBackup = $userdb->backup_limit - $usedBackups;
    
    if (!isset($_GET['name']) || !isset($_GET['memory']) || !isset($_GET['cores']) || !isset($_GET['disk']) || !isset($_GET['ports']) || !isset($_GET['databases']) || !isset($_GET['backups']) || !isset($_GET['location']) || !isset($_GET['egg'])) {
        $_SESSION['error'] = "Please fill in all infomration we need.";
    }
    if (!is_numeric($_GET['memory']) || !is_numeric($_GET['disk']) || !is_numeric($_GET['ports']) || !is_numeric($_GET['databases']) || !isset($_GET['backups']) || !is_numeric($_GET['cores']) || !is_numeric($_GET['location']) || !is_numeric($_GET['egg'])) {
        $_SESSION['error'] = "Some fields are empty or invalid.";
    }
    $usedServers = $servers->num_rows + $servers_in_queue->num_rows;
    if ($usedServers >= $serverLimit) {
        $_SESSION['error'] = "You have no servers left";
    }
    if ($_GET['memory'] == 0 || $_GET['memory'] != round($_GET['memory'], 0)) {
        $_SESSION['error'] = "Memory is invalid";
    }
    if ($_GET['cores'] < 0.15) {
        $_SESSION['error'] = "Minimum CPU is 0.15";
    }
    if ($_GET['memory'] < 256) {
        $_SESSION['error'] = "Minimum memory is 256MB";
    }
    if ($_GET['disk'] < 256) {
        $_SESSION['error'] = "Minimum disk is 256MB";
    }
    if ($_GET['ports'] < 0 || $_GET['ports'] != round($_GET['ports'], 0)) {
        $_SESSION['error'] = "Minimum ports is 0";
    }
    if ($_GET['databases'] < 0 || $_GET['databases'] != round($_GET['databases'], 0)) {
        $_SESSION['error'] = "Minimum databases is 0";
    }
    if ($_GET['backups'] < 0 || $_GET['backups'] != round($_GET['backups'], 0)) {
        $_SESSION['error'] = "Minimum backups is 0";
    }
    if ($_GET['cores'] > $cpuLimit) {
        $_SESSION['error'] = "You don't have enough CPU";
    }
    if ($_GET['memory'] > $freeRam) {
        $_SESSION['error'] = "You don't have enough memory";
    }
    if ($_GET['disk'] > $freeDisk) {
        $_SESSION['error'] = "You don't have enough disk space";
    }
    if ($_GET['ports'] > $freePorts) {
        $_SESSION['error'] = "You don't have enough ports";
    }
    if ($_GET['databases'] > $freeDatabases) {
        $_SESSION['error'] = "You don't have enough databases";
    }
    if ($_GET['backups'] > $freeBackup) {
        $_SESSION['error'] = "You don't have enough backups";
    }
    $locid = $_GET['location'];
    $doeslocationexist = mysqli_query($cpconn, "SELECT * FROM locations WHERE id = '" . mysqli_real_escape_string($cpconn, $locid) . "'");
    if ($doeslocationexist->num_rows == 0) {
        $_SESSION['error'] = "That location doesn't exist";
    }
    $eggid = $_GET['egg'];
    $doeseggexist = mysqli_query($cpconn, "SELECT * FROM eggs where id = '" . mysqli_real_escape_string($cpconn, $eggid) . "'");
    if ($doeseggexist->num_rows == 0) {
        $_SESSION['error'] = "That egg doesn't exist";
    }
    $egg = $doeseggexist->fetch_object();
    $name = $_GET['name'];
    $ram = $_GET['memory'];
    $disk = $_GET['disk'];
    $cpu = $_GET['cores'];
    $xtraports = $_GET['ports'];
    $location = $_GET['location'];
    $databases = $_GET['databases'];
    $backuplimt = $_GET['backups'];
    $created = time();
    $cpconn->query("INSERT INTO servers_queue (`name`, `ram`, `disk`, `cpu`, `xtra_ports`, `databases`, `backuplimit`, `location`, `ownerid`, `type`, `egg`, `puid`, `created`) VALUES ('" . mysqli_real_escape_string($cpconn, $name) . "', '" . mysqli_real_escape_string($cpconn, $ram) . "', '" . mysqli_real_escape_string($cpconn, $disk) . "', '" . mysqli_real_escape_string($cpconn, $cpu) . "', '" . mysqli_real_escape_string($cpconn, $xtraports) . "', '" . mysqli_real_escape_string($cpconn, $databases) . "', '" . mysqli_real_escape_string($cpconn, $backuplimt) . "', '" . mysqli_real_escape_string($cpconn, $location) . "', '" . mysqli_real_escape_string($cpconn, $_SESSION['uid']) . "', '$queue', '" . mysqli_real_escape_string($cpconn, $eggid) . "', '$userdb->panel_id', '$created')");
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['success'] = "Server created!";
    
}
?>


 <head>
    <title><?= $getsettingsdb['name'] ?> | Create</title>
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
                                    <img src="<?= $muserdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="<?= $muserdb['avatar']?>"
                                                    alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h4><?= $muserdb['username']?></h4>
                                                <p class="text-muted"><?= $muserdb['role']?></p>
                                                <p class="text-muted">Coins: <?= $muserdb['coins']?></p>
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
                            <img src="<?= $muserdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    <?= $muserdb['username']?>

                                    <span class="user-level"><?= $muserdb['role']?></span>

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
                        <li class="nav-item">
                            <a href="/support/select" class="collapsed">
                                <i class="fa-solid fa-ticket" style="color: #b9babf;"></i>
                                <p>Support</p>
                            </a>
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
                            <h2 class="text-white pb-2">Welcome back, <?= $muserdb['username']?>!</h2>
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
                                    <div class="card-title">Create Server</div>
                                </div>
                                <div class="card-body">
                                    <form type="GET">
                                    <input type="text" name="name" class="form-control" id="name" placeholder="Name">
                                    <br>
                                    <?php
                                        $locations = mysqli_query($cpconn, "SELECT * FROM locations")->fetch_all(MYSQLI_ASSOC);
                                        ?>

                                        <label for="location">Location:</label>
                                        <select class="form-control" name="location" id="location">
                                          <?php foreach ($locations as $location): ?>
                                            <?php
                                              $serversOnLoc = mysqli_query($cpconn, "SELECT * FROM servers WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                              $serversInQueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                              $availableSlots = $location['slots'] - count($serversOnLoc) - count($serversInQueue);
                                            ?>
                                            <option value="<?= $location["id"] ?>">
                                              <?= $location["name"] ?> (<?= $availableSlots ?>/<?= $location["slots"] ?> slots) [<?= $location["status"] === "MAINTENANCE" ? "MAINTENANCE" : ($availableSlots > 0 ? "ONLINE" : "OFFLINE") ?>]
                                            </option>
                                          <?php endforeach; ?>
                                        </select>
                                        
                                        <?php if (count($locations) == 0): ?>
                                          <p>No nodes are available at the moment; Server creation might currently be disabled.</p>
                                        <?php endif; ?>
                                    <br>
                                    <label for="egg">Egg:</label>
                                    <select class="form-control" name="egg" id="egg">
                                        <?php
                                        $alrCategories = array();
                                        $eggs = mysqli_query($cpconn, "SELECT * FROM eggs")->fetch_all(MYSQLI_ASSOC);
                                        foreach ($eggs as $egg) {
                                            $category = $egg["category"];
                                            if (in_array($category, $alrCategories)) {
                                                continue;
                                            }
                                            array_push($alrCategories, $category);
                                            echo '<optgroup label="' . $category . '">';
                                            $categoryEggs = array_filter($eggs, function ($e) use ($category) {
                                                return $e["category"] === $category;
                                            });
                                            foreach ($categoryEggs as $categoryEgg) {
                                                echo '<option value="' . $categoryEgg["id"] . '">' . $categoryEgg["name"] . '</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                        ?>
                                    </select>
                                    <br>
                                    <label for="memory">RAM:</label>
                                    <input type="number" name="memory"class="form-control" id="ram" value="" placeholder="RAM">
                                    <br>
                                    <label for="disk">DISK:</label>
                                    <input type="number" name="disk"class="form-control" id="disk" placeholder="DISK">
                                    <br>
                                    <label for="cpu">CPU:</label>
                                    <input type="number" name="cores" class="form-control" id="cpu" placeholder="CPU">
                                    <br>
                                    <label for="allocations">PORTS:</label>
                                    <input type="number" name="ports" class="form-control" id="allocations" placeholder="PORTS">
                                    <br>
                                    <label for="databases">DATABASES:</label>
                                    <input type="number" name="databases" class="form-control" id="databases" placeholder="DATABASES">
                                    <br>
                                    <label for="backups">BACKUPS:</label>
                                    <input type="number" name="backups"class="form-control" id="backups" placeholder="BACKUPS">
                                    <br>
                                    <button action="submit" name="createsv" class="btn btn-primary" style="background-color: #33404D;">Create</button>
                                <br></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include('../core/imports/footer.php');?>