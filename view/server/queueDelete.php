<?php
include(__DIR__ . '/../requirements/page.php');
if (!is_numeric($_GET["server"])) {
    header("location: /dashboard?e=".$lang['']);
    die();
}
$ownsServer = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
if ($ownsServer->num_rows == 0) {
    header("location: /dashboard?e=".$lang['error_not_found_in_database']);
    $conn->close();
    die();
}

if (mysqli_query($conn, "DELETE FROM mythicaldash_servers_queue WHERE id = '" . mysqli_real_escape_string($conn, $_GET["server"]) . "'")) {
    header("location: /dashboard?s=".$lang['server_no_longer_in_wait_list']);
    $conn->close();
    die();
}
else {
    header("location: /dashboard?e=".$lang['login_error_unknown']);
    $conn->close();
    die();
}