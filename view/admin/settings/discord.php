<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $discord_svid = mysqli_real_escape_string($conn,$_GET['discord:serverid']);
    $discord_invite = mysqli_real_escape_string($conn,$_GET['discord:invite']);
    $discord_webhook = mysqli_real_escape_string($conn,$_GET['discord:webhook']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_serverid` = '" . $discord_svid . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_invite` = '" . $discord_invite . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `discord_webhook` = '" . $discord_webhook . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>