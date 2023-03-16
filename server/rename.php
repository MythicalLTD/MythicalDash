<?php 
require("../core/require/page.php");
require("../core/require/addons.php");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
$pgetpid = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

$serverid = $_GET["id"];
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
$currentBackups = $result['attributes']['feature_limits']['backups'];
$currentAllocation = $result['attributes']['allocation'];
unset($result);

// get user current ram
$usedram = 0;
$usedcpu = 0;
$useddisk = 0;
$usedports = 0;
$useddatabases = 0;
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

}
$serversinqueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '".$_SESSION["uid"]."'");
foreach($serversinqueue as $server) {
    $usedram = $usedram + $server['ram'];
    $useddisk = $useddisk + $server['disk'];
    $usedports = $usedports + $server['xtra_ports'];
    $useddatabases = $useddatabases + $server['databases'];
    $usedcpu = $usedcpu + $server['cpu'];
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
$server = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '".$_SESSION["uid"]."' AND pid = '$serverid'");
if ($server->num_rows == 0) {
    $_SESSION['error'] = "This server doesn't exist or you don't have access to it.";
    echo '<script>window.location.replace("/");</script>';
    die();
}

if (isset($_POST['submit'])) {
    if ($_POST['svname'] == $currentName)
    {
        $_SESSION['error'] = "This server name is the same as the old server name!";
        echo '<script>window.location.replace("/");</script>';
        die(); 
    }
    else
    {
        // change server resources
        $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $serverid. "/details");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $getsettingsdb["ptero_apikey"]
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'name' => $_POST['svname'],
            "user" => $pgetpid['panel_id']
        )));
        $result = curl_exec($ch);
        $info = $result;
        curl_close($ch);
        unset($ch);
        $result = json_decode($result, true);
        
        if (!isset($result['object'])) {
            $_SESSION['error'] = "There was an unexpected error while editing your server name. (".$info.")";
            echo '<script>window.location.replace("/");</script>';
            die();
        }
        else {
            if (mysqli_query($cpconn, "UPDATE `servers` SET `name` = '".$_POST['svname']."' WHERE uid = '".$_SESSION["uid"]."';")) {
                //succes
            }
            else {
                $_SESSION['error'] = "There was an unexpected error while editing your server name.";
            }
            $userdfb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
            logClient("[Server renaming] " . $userdfb['username'] . " renamed his server to " . $_POST["svname"] . ".");      
            $_SESSION['success'] = "Changed your server name to ".$_POST['svname'];
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
                    <h6 class="h2 text-white d-inline-block mb-0">Rename Server </h6>
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
                                <h4 class="mb-0 text-center">Change server "<?= htmlspecialchars($currentName) ?>"</h4>
                                <h5 class="mb-0 text-center">Want to change the server resources?  <a href="manage?id=<?= $serverid ?>">Do it right here</a></h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <label for="svname">Name:</label>
                                    <input type="text" class="form-control" name="svname" value="<?= $currentName ?>" required>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="submit" type="submit">Rename Server</button>
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
