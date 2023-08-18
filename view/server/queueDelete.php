<?php
include(__DIR__ . '/../requirements/page.php');

if (!is_numeric($_GET["server"])) {
    header("location: /dashboard?e=Server id is invalid.");
    die();
}

$ownsServer = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
if ($ownsServer->num_rows == 0) {
    header("location: /dashboard?e=You don't have permission to delete this server or it doesn't exist.");
    $conn->close();
    die();
}

if (mysqli_query($conn, "DELETE FROM mythicaldash_servers_queue WHERE id = '" . mysqli_real_escape_string($conn, $_GET["server"]) . "'")) {
    header("location: /dashboard?s=Your server is no longer in queue!");
    $conn->close();
    die();
}
else {
    header("location: /dashboard?e=Hmmm. Cannot delete your server from the queue, contact staff.");
    $conn->close();
    die();
}