<?php
include(__DIR__.'/../../functions/session.php');
$userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '".mysqli_real_escape_string($conn, $_COOKIE['token']). "'")->fetch_array();
?>