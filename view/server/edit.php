<?php
include(__DIR__ . '/../requirements/page.php');
$serverid = $_GET["id"];
if (!is_numeric($serverid)) {
    header("location: /dashboard?=This server doesn't exist.");
}
// get current server info
$ch = curl_init($settings['PterodactylURL'] . "/api/application/servers/$serverid");
$headers = array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $settings['PterodactylAPIKey']
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
    $ch = curl_init($settings['PterodactylURL'] . "/api/application/servers/" . $server['pid']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $settings['PterodactylAPIKey']
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
$freeram = $userdb["ram"] - $usedram;
$freedisk = $userdb["disk"] - $useddisk;
$freeports = $userdb["ports"] - $usedports;
$freedatabases = $userdb["databases"] - $useddatabases;
$freebackup = $userdb["backups"] - $usedbackup;
// check server exist
$server = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "' AND pid = '$serverid'");
if ($server->num_rows == 0) {
    header("location: /dashboard?e=This server doesn't exist or you don't have access to it.");
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
            header("location: /dashboard?s=No changes made.");
            exit();
        }
        if ($_POST['memory'] < 256) {
            header('location: /dashboard?e=Minimum memory is 256MB');
            $conn->close();
            die();
        }
        if ($_POST['disk'] < 256) {
            header('location: /dashboard?e=Minimum disk is 256MB');
            $conn->close();
            die();
        }
        if ($_POST['cores'] < 10) {
            header('location: /dashboard?e=Minimum cores is 0.10');
            $conn->close();
            die();
        }
        if ($_POST['ports'] < 0) {
            header('location: /dashboard?e=Minimum ports is 0');
            $conn->close();
            die();
        }
        if ($_POST['backups'] < 0) {
            header('location: /dashboard?e=Minimum backup is 0.');
            $conn->close();
            die();
        }
        if ($_POST['databases'] < 0) {
            header('location: /dashboard?e=Minimum databases is 0.');
            $conn->close();
            die();
        }
        if ($_POST['memory'] > $freeram) {
            header("location: /dashboard?e=You don't have enough memory.");
            $conn->close();
            die();
        }
        if ($_POST['cores'] > ($userdb["cpu"] - $usedcpu) + $currentCpu) {
            header("location: /dashboard?e=You don't have enough cpu.");
            $conn->close();
            die();
        }
        if ($_POST['disk'] > $freedisk) {
            if ($useddisk1 > $userdb["disk_space"]) {
                if ($_POST['disk'] > $currentDisk) {
                    header("location: /dashboard?e=Your in debt, you cannot increase disk.");
                    $conn->close();
                    die();
                }
                if ($_POST['disk'] == $currentDisk) {
                    header("location: /dashboard?e=You must reduce you're disk as your in debt.");
                    $conn->close();
                    die();
                }
            } else {
                header("location: /dashboard?e=You don't have enough disk.");
                $conn->close();
                die();
            }

        }
        if ($_POST['ports'] > $freeports) {
            if ($usedports1 > $userdb["ports"]) {
                if ($_POST['ports'] > $currentPorts || $_POST['ports'] == $currentPorts) {
                    header("Location: /dashboard?e=You're in debt, you cannot increase ports.");
                    $conn->close();
                    die();
                }
            } else {
                header("Location: /dashboard?e=You don't have enough ports.");
                $conn->close();
                die();
            }
        }

        if ($_POST['databases'] > $freedatabases) {
            if ($useddatabases > $userdb["databases"]) {
                if ($_POST['databases'] > $currentDatabases || $_POST['databases'] == $currentDatabases) {
                    header("Location: /dashboard?e=You're in debt, you cannot increase databases.");
                    $conn->close();
                    die();
                }
            } else {
                header("Location: /dashboard?e=You don't have enough databases.");
                $conn->close();
                die();
            }
        }

        if ($_POST['backups'] > $freebackup) {
            if ($usedbackup > $userdb["backups"]) {
                if ($_POST['backups'] > $currentBackups || $_POST['backups'] == $currentBackups) {
                    header("Location: /dashboard?e=You're in debt, you cannot increase backups.");
                    $conn->close();
                    die();
                }
            } else {
                header("Location: /dashboard?e=You don't have enough backups.");
                $conn->close();
                die();
            }
        }
        // change server resources
        $ch = curl_init($settings['PterodactylURL'] . "/api/application/servers/" . $serverid . "/build");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $settings['PterodactylAPIKey']
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
            header("location: /dashboard?e=There was an unexpected error while editing your server's limits.");
            $conn->close();
            die();
        } else {
            header("location: /dashboard?s=Done we updated your server limits.");
            $conn->close();
            die();
        }

    }
}
?>
<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Edit Server
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Edit /</span> Create</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <br>
                        <div id="ads">
                            <?php
                            if ($settings['enable_ads'] == "true") {
                                echo $settings['ads_code'];
                            }
                            ?>
                        </div>
                        <br>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Edit Server</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <p>RAM:&nbsp;&nbsp;&nbsp;<input class="form-control" id="ram" name="memory"
                                                value="<?= $currentMemory ?>"></p>
                                        <p>Disk:&nbsp;&nbsp;&nbsp;<input class="form-control" id="disk" name="disk"
                                                value="<?= $currentDisk ?>"></p>
                                        <p>CPU:&nbsp;&nbsp;&nbsp;<input class="form-control" id="cpu" name="cores"
                                                value="<?= $currentCpu ?>"></p>
                                        <p>Allocations:&nbsp;&nbsp;&nbsp;<input class="form-control" name="ports"
                                                id="ports" value="<?= $currentPorts ?>"></p>
                                        <p>Databases:&nbsp;&nbsp;&nbsp;<input class="form-control" name="databases"
                                                id="databases" value="<?= $currentDatabases ?>"></p>
                                        <p>Backups:&nbsp;&nbsp;&nbsp;<input class="form-control" name="backups"
                                                id="backups" value="<?= $currentBackups ?>"></p>

                                        <button name="submit" type="submit" class="btn btn-primary">Modify</button>
                                        <br>
                                    </form>
                                </div>
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