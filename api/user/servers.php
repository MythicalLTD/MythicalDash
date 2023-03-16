<?php
require("../../core/require/sql.php");
ini_set("display_errors", 1);
if (!isset($_GET['uid'])) {
    die(json_encode(array("error"=>true,"message"=>"The user id is not set.")));
}
if (!is_numeric($_GET['uid'])) {
    die(json_encode(array("error"=>true,"message"=>"The user id is invalid.")));
}
$user = $cpconn->query("SELECT username FROM users WHERE user_id = '".mysqli_real_escape_string($cpconn, $_GET['uid'])."'");
if ($user->num_rows == 0) {
    die(json_encode(array("error"=>true,"message"=>"The user is not registered on our systems.")));
}
$userid = $_GET['uid'];
$servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '$userid'")->fetch_all(MYSQLI_ASSOC);

die(json_encode(array("error"=>false,"message"=>"OK","servers"=>$servers)));
