<?php 
$nuserdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
$servers_in_queue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
$serversnumber = $servers->num_rows + $servers_in_queue->num_rows;
function percentage($number,$total,$outof) {
    $result = ($number/$total) * $outof;
    return round($result);
}
// GET USED DISK, RAM IN TOTAL
$usedRam = 0;
$usedDisk = 0;
$usedCpu = 0;
$usedPorts = 0;
$usedDatabase = 0;
$usedBackup = 0;
$uservers = array();
foreach($servers as $serv) {
    $ptid = $serv["pid"];
    $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $ptid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $getsettingsdb["ptero_apikey"],
        "Content-Type: application/json",
        "Accept: Application/vnd.pterodactyl.v1+json"
    ));
    $result1 = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode != 200) {
        echo '<div style="background-color: red; width: 100%; color: white; text-align:center;"> Unable to connect to the game panel! Please contact one of the server administrators.<br/><br/><small>Details: [' . $httpcode . '] ' . $result1 . '</small></div>';
        die();
    }
    curl_close($ch);
    $result = json_decode($result1, true);
    $id = $result['attributes']["uuid"];
    $name = $result['attributes']['name'];
    $ram = $result['attributes']['limits']['memory'];
    $disk = $result['attributes']['limits']['disk'];
    $cpuh = $result['attributes']['limits']['cpu'];
    $db = $result['attributes']['feature_limits']['databases'];
    $usedRam = $usedRam + $ram;
    $usedDisk = $usedDisk + $disk;
    $alloc = $result['attributes']['feature_limits']['allocations'] - 1;
    $usedBackup = $result['attributes']['feature_limits']['backups'];
    $usedPorts = $usedPorts + $alloc;
    $usedDatabase = $usedDatabase + $db;
    $usedCpu = $usedCpu + $cpuh;
    array_push($uservers, $result['attributes']);
}
foreach($servers_in_queue as $server) {
    $usedRam = $usedRam + $server['ram'];
    $usedDisk = $usedDisk + $server['disk'];
    $usedPorts = $usedPorts + $server['xtra_ports'];
    $usedBackup = $usedBackup + $server['backuplimit'];
    $usedDatabase = $usedDatabase + $server['databases'];
    $usedCpu = $usedCpu + $server["cpu"];
}
?>
<div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-primary card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="fas fa-memory"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">RAM</p>
                                                    <h4 class="card-title"><?= $usedRam . "MB/" . $nuserdb["memory"] ?>MB
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-info card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="fa fa-save"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">DISK</p>
                                                    <h4 class="card-title">
                                                        <?= $usedDisk . "MB/" . $nuserdb["disk_space"] ?>MB</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-success card-round">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="fas fa-microchip"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">CPU</p>
                                                    <h4 class="card-title"><?= $usedCpu . "/" . $nuserdb["cpu"] ?>%</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-secondary card-round">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="fas fa-server"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Servers</p>
                                                    <h4 class="card-title">
                                                        <?= $serversnumber . "/" . $nuserdb["server_limit"] ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-primary card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-hdd-network-fill"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Backups</p>
                                                    <h4 class="card-title">
                                                        <?= $usedBackup . "/" . $nuserdb["backup_limit"] ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-info card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-wifi"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Allocations</p>
                                                    <h4 class="card-title"><?= $usedPorts . "/" . $nuserdb["ports"] ?>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-success card-round">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-server"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Databases</p>
                                                    <h4 class="card-title">
                                                        <?= $usedDatabase . "/" . $nuserdb["databases"] ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="card card-stats card-secondary card-round">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-person-badge"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Role</p>
                                                    <h4 class="card-title"><?= $nuserdb['role'] ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>