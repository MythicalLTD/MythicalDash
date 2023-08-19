<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $discord_svid = mysqli_real_escape_string($conn,$_GET['discord:serverid']);
    $discord_invite = mysqli_real_escape_string($conn,$_GET['discord:invite']);
    $discord_webhook = mysqli_real_escape_string($conn,$_GET['discord:webhook']);
    $discord_client_id = mysqli_real_escape_string($conn, $_GET['discord:client_id']);
    $discord_clientsecret = mysqli_real_escape_string($conn, $_GET['discord:client_secret']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_serverid` = '" . $discord_svid . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_invite` = '" . $discord_invite . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_webhook` = '" . $discord_webhook . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_clientid` = '" . $discord_client_id . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_clientsecret` = '" . $discord_clientsecret . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    $conn->close();
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>