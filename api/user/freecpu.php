<?php
require("../../core/require/sql.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (!isset($_GET['userid'])) {
    die(json_encode(array(
        'error' => "No user id present in GET request."
    )));
}
$userinfo = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_GET['userid']) . "'");
if ($userinfo->num_rows == 0) {
    die(json_encode(array(
        'error' => "The user with this id does not exist."
    )));
}
$userid = $_GET['userid'];
$user = $userinfo->fetch_object();
$cpuLimit = $user->cpu;
$usedcpu = 0;
$servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '$userid'");
$servers_queue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '$userid'");
foreach ($servers as $serv) {
    $ptid = $serv["pid"];
    $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $ptid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $getsettingsdb["ptero_apikey"],
        "Content-Type: application/json",
        "Accept: Application/vnd.pterodactyl.v1+json"
    ));
    $result1 = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result1, true);

    $cpu = $result['attributes']['limits']['cpu'];
    $usedcpu = $usedcpu + $cpu;
}
foreach ($servers_queue as $server) {
    $usedcpu = $usedcpu + $server['cpu'];


}
die(json_encode(array(
    'freecpu' => $cpuLimit - $usedcpu,
    'usedcpu' => $usedcpu
)));