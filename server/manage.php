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
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Manage Server</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Servers</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">

<!-- BEGIN: Content-->
<div class="container-fluid mt--6">
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <section id="dashboard-analytics">
                <div class="row">

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">Change server "<?= htmlspecialchars($currentName) ?>"</h4>
                                <h5 class="mb-0 text-center">Want to change the server name?  <a href="rename?id=<?= $serverid ?>">Do it right here</a></h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <label for="memory">Memory:</label>
                                    <input type="number" min="256" class="form-control" name="memory" value="<?= $currentMemory ?>" required>
                                    <br>
                                    <label for="cores">CPU limit (%): </label>
                                    <input type="number" min="10%" step="any" class="form-control" name="cores" value="<?= $currentCpu ?>" required>
                                    <br>
                                    <label for="disk">Disk:</label>
                                    <input type="number" min="256" step="any" class="form-control" name="disk" value="<?= $currentDisk ?>" required>
                                    <br>
                                    <label for="ports">Ports:</label>
                                    <input type="number" class="form-control" name="ports" value="<?= $currentPorts ?>" required>
                                    <br>
                                    <label for="databases">Databases:</label>
                                    <input type="number" class="form-control" name="databases" value="<?= $currentDatabases ?>" required>
                                    <br>
                                    <label for="backups">Backups:</label>
                                    <input type="number" class="form-control" name="backups" value="<?= $currentBackups ?>" required>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="submit" type="submit">Change Server</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
            
        </div>
                </div>
            </section>
            <!-- Dashboard Analytics end -->

        </div>
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
