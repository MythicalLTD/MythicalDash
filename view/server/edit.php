<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../requirements/page.php');
$serverid = $_GET["id"];
if (!is_numeric($serverid)) {
    header("location: /dashboard?=This server doesn't exist.");
}
// get current server info
$ch = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/$serverid");
$headers = array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
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
$currentPorts = $result['attributes']['feature_limits']['allocations'] - 1;
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
$servers = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
foreach ($servers as $server) {
    $ch = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/" . $server['pid']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    $result = json_decode($result, true);
    $usedram = $result['attributes']['limits']['memory'] + $usedram;
    $cpu = $result['attributes']['limits']['cpu'];
    $usedcpu = $usedcpu + $cpu;
    $useddisk = $useddisk + $result['attributes']['limits']['disk'];
    $ports = $result['attributes']['feature_limits']['allocations'] - 1;
    $usedports = $usedports + $ports;
    $useddatabases = $useddatabases + $result['attributes']['feature_limits']['databases'];
    $usedbackup = $result['attributes']['feature_limits']['backups'];
}
$serversinqueue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
foreach ($serversinqueue as $server) {
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
$freeram = $session->getUserInfo("ram") - $usedram;
$freedisk = $session->getUserInfo("disk") - $useddisk;
$freeports = $session->getUserInfo("ports") - $usedports;
$freedatabases = $session->getUserInfo("databases") - $useddatabases;
$freebackup = $session->getUserInfo("backups") - $usedbackup;
// check server exist
$server = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "' AND pid = '".mysqli_real_escape_string($conn,$serverid)."'");
if ($server->num_rows == 0) {
    header("location: /dashboard?e=".$lang['error_not_found_in_database']);
    $conn->close();
    die();
}
if (isset($_POST['submit'])) {
    if (isset($_POST['memory'], $_POST['cores'], $_POST['disk'], $_POST['ports'], $_POST['databases'], $_POST['backups'])) {
        if (
            $_POST['memory'] == $currentMemory &&
            $_POST['cores'] == $currentCpu &&
            $_POST['disk'] == $currentDisk &&
            $_POST['ports'] == $currentPorts &&
            $_POST['databases'] == $currentDatabases &&
            $_POST['backups'] == $currentBackups
        ) {
            header("location: /dashboard?s=".$lang['server_no_changes_made']);
            exit();
        }
        if ($_POST['memory'] < 256) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['ram'],'256MB'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['disk'] < 256) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['disk'],'256MB'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['cores'] < 10) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['cpu'],'0.10'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['ports'] < 0) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['server_allocation'],'0'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['backups'] < 0) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['backup_slot'],'0'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['databases'] < 0) {
            header('location: /dashboard?e='.str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['mysql'],'0'), $lang['server_minimum_is']));
            $conn->close();
            die();
        }
        if ($_POST['memory'] > $freeram) {
            header("location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['ram'],$lang['server_you_not_have']));
            $conn->close();
            die();
        }
        if ($_POST['cores'] > ($session->getUserInfo("cpu") - $usedcpu) + $currentCpu) {
            header("location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['cpu'],$lang['server_you_not_have']));
            $conn->close();
            die();
        }
        if ($_POST['disk'] > $freedisk) {
            if ($useddisk1 > $session->getUserInfo("disk_space")) {
                if ($_POST['disk'] > $currentDisk) {
                    header("location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['disk'],$lang['server_you_not_have']));
                    $conn->close();
                    die();
                }
                if ($_POST['disk'] == $currentDisk) {
                    header("location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['disk'],$lang['server_you_not_have']));
                    $conn->close();
                    die();
                }
            } else {
                header("location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['disk'],$lang['server_you_not_have']));
                $conn->close();
                die();
            }

        }
        if ($_POST['ports'] > $freeports) {
            if ($usedports1 > $session->getUserInfo("ports")) {
                if ($_POST['ports'] > $currentPorts || $_POST['ports'] == $currentPorts) {
                    header("Location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['server_allocation'],$lang['server_you_not_have']));
                    $conn->close();
                    die();
                }
            } else {
                header("Location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['server_allocation'],$lang['server_you_not_have']));
                $conn->close();
                die();
            }
        }

        if ($_POST['databases'] > $freedatabases) {
            if ($useddatabases > $session->getUserInfo("databases")) {
                if ($_POST['databases'] > $currentDatabases || $_POST['databases'] == $currentDatabases) {
                    header("Location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['mysql'],$lang['server_you_not_have']));
                    $conn->close();
                    die();
                }
            } else {
                header("Location: /dashboard?e=".str_replace('%PLACEHOLDER_1%',$lang['mysql'],$lang['server_you_not_have']));
                $conn->close();
                die();
            }
        }

        //if ($_POST['backups'] > $freebackup) {
        //    if ($usedbackup > $session->getUserInfo("backups")) {
        //        if ($_POST['backups'] > $currentBackups || $_POST['backups'] == $currentBackups) {
        //            header("Location: /dashboard?e=You're in debt, you cannot increase backups.");
        //            $conn->close();
        //            die();
        //        }
        //    } else {
        //        header("Location: /dashboard?e=You don't have enough backups.");
        //        $conn->close();
        //        die();
        //    }
        //}
        // change server resources
        $ch = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/" . $serverid . "/build");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
        )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(
            array(
                'allocation' => $currentAllocation,
                'memory' => $_POST['memory'],
                'swap' => $_POST['memory'],
                'disk' => $_POST['disk'],
                'io' => 500,
                'cpu' => $_POST['cores'],
                'threads' => null,
                'feature_limits' => array(
                    'databases' => $_POST['databases'],
                    'allocations' => $_POST['ports'] + 1,
                    'backups' => $_POST['backups']
                )
            )
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        unset($ch);
        $result = json_decode($result, true);
        if (!isset($result['object'])) {
            header("location: /dashboard?e=".$lang['login_error_unknown']);
            $conn->close();
            die();
        } else {
            header("location: /dashboard?s=".$lang['server_updated']);
            $conn->close();
            die();
        }

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
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['server']?>
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
<?php
  if (SettingsManager::getSetting("show_snow") == "true") {
    include(__DIR__ . '/../components/snow.php');
  }
  ?>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['server']?> /</span> <?= $lang['edit']?></h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <br>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                echo SettingsManager::getSetting("ads_code");
                            }
                            ?>
                        </div>
                        <br>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title"><?= $lang['server']?>  <?= $lang['edit']?></div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <p><?= $lang['ram']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" id="ram" name="memory"
                                                value="<?= $currentMemory ?>"></p>
                                        <p><?= $lang['disk']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" id="disk" name="disk"
                                                value="<?= $currentDisk ?>"></p>
                                        <p><?= $lang['cpu']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" id="cpu" name="cores"
                                                value="<?= $currentCpu ?>"></p>
                                        <p><?= $lang['server_allocation']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" name="ports"
                                                id="ports" value="<?= $currentPorts ?>"></p>
                                        <p><?= $lang['mysql']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" name="databases"
                                                id="databases" value="<?= $currentDatabases ?>"></p>
                                        <p><?= $lang['backup_slot']?>:&nbsp;&nbsp;&nbsp;<input class="form-control" name="backups"
                                                id="backups" value="<?= $currentBackups ?>"></p>

                                        <button name="submit" type="submit" class="btn btn-primary"><?= $lang['save']?></button>
                                        <br>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                echo SettingsManager::getSetting("ads_code");
                            }
                            ?>
                        </div>
                        <br>
                    </div>
                    <?php include(__DIR__ . '/../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>