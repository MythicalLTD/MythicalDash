<?php 
require("../core/require/page.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
$user = $_SESSION['user'];
$pgetpid = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$serverid = $_GET['id'];

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

$server = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '".$_SESSION["uid"]."' AND pid = '$serverid'");
if ($server->num_rows == 0) {
    $_SESSION['error'] = "This server doesn't exist or you don't have access to it.";
    echo '<script>window.location.replace("/");</script>';
    die();
}



$svget = $cpconn->query("SELECT * FROM servers where uid = '". $_SESSION["uid"]. "'")->fetch_array();

if ($svget['name'] == $currentName)
{
    $_SESSION['error'] = "Nothing to do the server is up-to-date";
    echo '<script>window.location.replace("/");</script>';
}
else
{
    if (mysqli_query($cpconn, "UPDATE `servers` SET `name` = '".$currentName."' WHERE `servers`.`pid` = '".$_GET['id']."';")) {
        //succes
        $_SESSION['success'] = "Done.";
    }
    else {
        $_SESSION['error'] = "There was an unexpected error while editing your server name.". mysqli_error($cpconn);
    }
    echo '<script>window.location.replace("/");</script>';
}
?>


<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Fetch Server</h6>
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
                                <h4 class="mb-0 text-center">Please wait while we fetch the data from the panel for the server "<?= htmlspecialchars($currentName) ?>"</h4>
                                <h5 class="mb-0 text-center">Are you stuck here?  <a href="fetch?id=<?= $serverid ?>">Click here to restart the process</a></h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="textContainer">Please wait..</div>
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