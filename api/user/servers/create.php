<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../../../core/require/sql.php");
require("../../../core/require/addons.php");
  
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();

session_start();
$queue = 0;
if (isset($_COOKIE['remember_token'])) {
    $session_id = $_COOKIE['remember_token'];
    $query = "SELECT * FROM users WHERE session_id='".$session_id."'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        $userdbd = $cpconn->query("SELECT * FROM users WHERE session_id='$session_id'")->fetch_array();
        $_SESSION['username'] = $userdbd['username'];
        $_SESSION['email'] = $userdbd['email'];
        $_SESSION["uid"] = $userdbd['user_id'];
    }
    else
    {
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('phpsessid', '', time() - 3600, '/');
        session_destroy();
        echo '<script>window.location.replace("/auth/login");</script>';
    }
  
  }
else
{
  setcookie('remember_token', '', time() - 3600, '/');
  setcookie('phpsessid', '', time() - 3600, '/');
  session_destroy();
  echo '<script>window.location.replace("/auth/login");</script>';
}



$userdb = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . $_SESSION['uid'] . "'")->fetch_object();
if ($userdb->staff == 1)
{
    $queue = 2;
}
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
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You cannot have more than two servers in queue."
        ]
    )));
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

if (!isset($_POST['name']) || !isset($_POST['memory']) || !isset($_POST['cores']) || !isset($_POST['disk']) || !isset($_POST['ports']) || !isset($_POST['databases']) || !isset($_POST['location']) || !isset($_POST['backups']) || !isset($_POST['egg'])) {
    print_r($_POST);
    die(json_encode(array(
        'errors' => [
            'code' => "ValidationException",
            'status' => "400",
            'detail' => "Some post fields are empty or invalid."
        ]
    )));
}
if (!is_numeric($_POST['memory']) || !is_numeric($_POST['disk']) || !is_numeric($_POST['ports']) || !is_numeric($_POST['databases']) || !isset($_POST['backups']) || !is_numeric($_POST['cores']) || !is_numeric($_POST['location']) || !is_numeric($_POST['egg'])) {
    die(json_encode(array(
        'errors' => [
            'code' => "ValidationException",
            'status' => 400,
            'detail' => "Some post fields are empty or invalid."
        ]
    )));
}
$usedServers = $servers->num_rows + $servers_in_queue->num_rows;
if ($usedServers >= $serverLimit) {
    die(json_encode(array(
        'errors' => [
            'code' => "ValidationException",
            'status', 400,
            'detail' => "You have no servers left"
        ]
    )));
}
if ($_POST['memory'] == 0 || $_POST['memory'] != round($_POST['memory'], 0)) {
    die(json_encode(array(
        'errors' => [
            'code' => "ValidationException",
            'status' => 400,
            'detail' => "Memory is invalid"
        ]
    )));
}
if ($_POST['cores'] < 0.15) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum CPU is 0.15"
        ]
    )));
}
if ($_POST['memory'] < 256) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum memory is 256MB"
        ]
    )));
}
if ($_POST['disk'] < 256) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum disk is 256MB"
        ]
    )));
}
if ($_POST['ports'] < 0 || $_POST['ports'] != round($_POST['ports'], 0)) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum ports is 0"
        ]
    )));
}
if ($_POST['databases'] < 0 || $_POST['databases'] != round($_POST['databases'], 0)) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum databases is 0"
        ]
    )));
}
if ($_POST['backups'] < 0 || $_POST['backups'] != round($_POST['backups'], 0)) {
    die(json_encode(array(
        'errors' => [
            'code' => 'ValidationException',
            'status' => 400,
            'detail' => "Minimum backups is 0"
        ]
    )));
}
if ($_POST['cores'] > $cpuLimit) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough CPU"
        ]
    )));
}
if ($_POST['cores'] > $cpuLimit) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You have no CPU cores left!"
        ]
    )));
}
if ($_POST['memory'] > $freeRam) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough memory"
        ]
    )));
}
if ($_POST['disk'] > $freeDisk) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough disk space"
        ]
    )));
}
if ($_POST['ports'] > $freePorts) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough ports"
        ]
    )));
}
if ($_POST['databases'] > $freeDatabases) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough databases"
        ]
    )));
}
if ($_POST['backups'] > $freeBackup) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NotEnoughResourcesException',
            'status' => 400,
            'detail' => "You don't have enough backups"
        ]
    )));
}
$locid = $_POST['location'];
$doeslocationexist = mysqli_query($cpconn, "SELECT * FROM locations WHERE id = '" . mysqli_real_escape_string($cpconn, $locid) . "'");
if ($doeslocationexist->num_rows == 0) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NoLocationException',
            'status' => 400,
            'detail' => "That location doesn't exist"
        ],
        "success" => false
    )));
}
$eggid = $_POST['egg'];
$doeseggexist = mysqli_query($cpconn, "SELECT * FROM eggs where id = '" . mysqli_real_escape_string($cpconn, $eggid) . "'");
if ($doeseggexist->num_rows == 0) {
    die(json_encode(array(
        'errors' => [
            'code' => 'NoEggException',
            'status' => 400,
            'detail' => "That egg doesn't exist"
        ],
        'success' => false
    )));
}
// add to db
$egg = $doeseggexist->fetch_object();
$name = $_POST['name'];
$ram = $_POST['memory'];
$disk = $_POST['disk'];
$cpu = $_POST['cores'];
$xtraports = $_POST['ports'];
$location = $_POST['location'];
$databases = $_POST['databases'];
$backuplimt = $_POST['backups'];
$created = time();
logClient("[Server creation] We added a server in the queue called ``$name`` on location #$location.");
if (mysqli_query($cpconn, "INSERT INTO servers_queue (`name`, `ram`, `disk`, `cpu`, `xtra_ports`, `databases`, `backuplimit`, `location`, `ownerid`, `type`, `egg`, `puid`, `created`) VALUES ('" . mysqli_real_escape_string($cpconn, $name) . "', '" . mysqli_real_escape_string($cpconn, $ram) . "', '" . mysqli_real_escape_string($cpconn, $disk) . "', '" . mysqli_real_escape_string($cpconn, $cpu) . "', '" . mysqli_real_escape_string($cpconn, $xtraports) . "', '" . mysqli_real_escape_string($cpconn, $databases) . "', '" . mysqli_real_escape_string($cpconn, $backuplimt) . "', '" . mysqli_real_escape_string($cpconn, $location) . "', '" . mysqli_real_escape_string($cpconn, $_SESSION['uid']) . "', '$queue', '" . mysqli_real_escape_string($cpconn, $eggid) . "', '$userdb->panel_id', '$created')")) {
    die(json_encode(array(
        'success' => true
    )));
}