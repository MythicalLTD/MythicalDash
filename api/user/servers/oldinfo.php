<?php
require("../../../core/require/config.php");
require("../../../core/require/sql.php");
require("../../../core/require/addons.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
session_start();

// GET PTERO SERVER INFO
$pid = $_GET['pid'];
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
    die(json_encode(array("error" => "Could not connect to the panel")));
}
curl_close($ch);
$result = json_decode($result1, true);

$result = $cpconn->query("SELECT * FROM servers WHERE id = '" . mysqli_real_escape_string($cpconn, $_GET['pid']) . "'")->fetch_object();

$ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $result->pid);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $getsettingsdb["ptero_apikey"],
    "Content-Type: application/json",
    "Accept: Application/vnd.pterodactyl.v1+json"
));
$result1 = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$ptero = json_decode($result1, true);

echo json_encode(
    array(
        "db" => $result,
        "ptero" => $ptero['attributes'],
        )
    );