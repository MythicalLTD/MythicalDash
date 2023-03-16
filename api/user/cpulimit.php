<?php
require("../../core/require/sql.php");

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

die(json_encode(array(
    'cpuLimit' => $cpuLimit
)));