<?php
include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['id'])) {
    $ownsServer = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE id = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' AND uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
    if ($ownsServer->num_rows == 0) {
        header('location: /dashboard?e='.$lang['server_not_own']);
        $conn->close();
        die();
    }
    $conn->query("UPDATE `mythicaldash_servers` SET `purge` = 'false' WHERE `mythicaldash_servers`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
    $conn->close();
    header("location: /dashboard?s=".$lang['server_active']);
    die();
} else {
    header('location: /dashboard');
    die();
}
?>