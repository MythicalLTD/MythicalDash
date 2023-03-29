<?php
require("../../core/require/sql.php");
header('Content-type: application/json');
ini_set("display_errors", 0);
if (!$cpconn->ping()) {
    $data = array(
        "error" => "Host can't reach the MySQL db",
        "status" => array(
            "client" => "Unknown",
            "panel" => "Unknown",
            "mysql" => "FAILED"
        )
    );
    header('HTTP/1.1 500 MYSQL ERROR');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();

if (!isset($_GET['api_key'])) {
    $data = array(
        "error" => "No API key was specified"
    );
    header('HTTP/1.1 402 NO API KEY');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$apikey = $cpconn->query("SELECT * FROM api_keys WHERE skey = '".mysqli_real_escape_string($cpconn, $_GET['api_key'])."'");
if ($apikey->num_rows == 0) {
    $data = array(
        "error" => "API key can't be found"
    );
    header('HTTP/1.1 401 BAD API KEY');
    die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$ch = curl_init($getsettingsdb['ptero_url'] . '/api/application/servers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $getsettingsdb['ptero_apikey'],
    'Content-Type: application/json',
));
$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status_code === 200) {
    $panel_status = "OK";
} else {
    $panel_status = "FAILED";
}

$data = array(
    "error" => null,
    "status" => array(
        "client" => "OK",
        "panel" => $panel_status,
        "mysql" => "OK"
    )
    header('HTTP/1.1 200 OK');
);
die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>
