<?php
include(__DIR__.'/../../functions/session.php');
include(__DIR__.'/../../functions/telemetry.php');
$userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '".$_COOKIE['token']. "'")->fetch_array();
?>