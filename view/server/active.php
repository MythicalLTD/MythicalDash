<?php
include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['id'])) {
    $ownsServer = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE id = '" . mysqli_real_escape_string($conn, $_GET["id"]) . "' AND uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
    if ($ownsServer->num_rows == 0) {
        header('location: /dashboard?e=Sorry but you don`t own this server');
        $conn->close();
        die();
    }
    $conn->query("UPDATE `mythicaldash_servers` SET `purge` = 'false' WHERE `mythicaldash_servers`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
    $conn->close();
    header("location: /dashboard?s=We've updated your server settings. Your server will now skip the next purge.");
    die();
} else {
    header('location: /dashboard');
    die();
}
?>