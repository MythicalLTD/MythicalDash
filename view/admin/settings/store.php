<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $r_ram = mysqli_real_escape_string($conn,$_GET['store:ram']);
    $r_disk = mysqli_real_escape_string($conn,$_GET['store:disk']);
    $r_cpu = mysqli_real_escape_string($conn,$_GET['store:cpu']);
    $r_svlimit = mysqli_real_escape_string($conn,$_GET['store:svlimit']);
    $r_ports = mysqli_real_escape_string($conn,$_GET['store:ports']);
    $r_databases = mysqli_real_escape_string($conn,$_GET['store:databases']);
    $r_backups = mysqli_real_escape_string($conn,$_GET['store:backups']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_memory` = '" . $r_ram . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_disk_space` = '" . $r_disk . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_cpu` = '" . $r_cpu . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_server_limit` = '" . $r_svlimit . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_allocation` = '" . $r_ports . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_database` = '" . $r_databases . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `price_backup` = '" . $r_backups . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>