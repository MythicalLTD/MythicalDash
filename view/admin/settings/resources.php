<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $r_coins = mysqli_real_escape_string($conn,$_GET['resources:coins']);
    $r_ram = mysqli_real_escape_string($conn,$_GET['resources:ram']);
    $r_disk = mysqli_real_escape_string($conn,$_GET['resources:disk']);
    $r_cpu = mysqli_real_escape_string($conn,$_GET['resources:cpu']);
    $r_svlimit = mysqli_real_escape_string($conn,$_GET['resources:svlimit']);
    $r_ports = mysqli_real_escape_string($conn,$_GET['resources:ports']);
    $r_databases = mysqli_real_escape_string($conn,$_GET['resources:databases']);
    $r_backups = mysqli_real_escape_string($conn,$_GET['resources:backups']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_coins` = '" . $r_coins . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_memory` = '" . $r_ram . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_disk_space` = '" . $r_disk . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_cpu` = '" . $r_cpu . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_server_limit` = '" . $r_svlimit . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_port` = '" . $r_ports . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_db` = '" . $r_databases . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_backups` = '" . $r_backups . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>