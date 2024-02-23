<?php
use MythicalDash\ErrorHandler;

include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_GET['update_settings'])) {
        $r_coins = mysqli_real_escape_string($conn, $_GET['resources:coins']);
        $r_ram = mysqli_real_escape_string($conn, $_GET['resources:ram']);
        $r_disk = mysqli_real_escape_string($conn, $_GET['resources:disk']);
        $r_cpu = mysqli_real_escape_string($conn, $_GET['resources:cpu']);
        $r_svlimit = mysqli_real_escape_string($conn, $_GET['resources:svlimit']);
        $r_ports = mysqli_real_escape_string($conn, $_GET['resources:ports']);
        $r_databases = mysqli_real_escape_string($conn, $_GET['resources:databases']);
        $r_backups = mysqli_real_escape_string($conn, $_GET['resources:backups']);

        $def_max_ram = mysqli_real_escape_string($conn, $_GET['resources:maxram']);
        $def_max_disk = mysqli_real_escape_string($conn, $_GET['resources:maxdisk']);
        $def_max_cpu = mysqli_real_escape_string($conn, $_GET['resources:maxcpu']);
        $def_max_svlimit = mysqli_real_escape_string($conn, $_GET['resources:maxsvlimit']);
        $def_max_ports = mysqli_real_escape_string($conn, $_GET['resources:maxports']);
        $def_max_databases = mysqli_real_escape_string($conn, $_GET['resources:maxdatabases']);
        $def_max_backups = mysqli_real_escape_string($conn, $_GET['resources:maxbackups']);

        $afk_coins_per_m = mysqli_real_escape_string($conn, $_GET['afk:coins:per:min']);
        $eafk = mysqli_real_escape_string($conn, $_GET['resources:eafk']);

        // Update default values
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `def_coins` = '$r_coins', `def_memory` = '$r_ram', `def_disk_space` = '$r_disk', `def_cpu` = '$r_cpu', `def_server_limit` = '$r_svlimit', `def_port` = '$r_ports', `def_db` = '$r_databases', `def_backups` = '$r_backups' WHERE `id` = 1");
        sleep(2);
        // Update max values
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `max_ram` = '$def_max_ram', `max_disk` = '$def_max_disk', `max_cpu` = '$def_max_cpu', `max_servers` = '$def_max_svlimit', `max_allocations` = '$def_max_ports', `max_dbs` = '$def_max_databases', `max_backups` = '$def_max_backups' WHERE `id` = 1");

        // Update other settings
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `afk_coins_per_min` = '$afk_coins_per_m', `enable_afk` = '$eafk' WHERE `id` = 1");

        header('Location: /admin/settings?s=Settings updated successfully');
        $conn->close();
        die();
    } else {
        header('Location: /admin/settings?e=Failed to update settings');
        die();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ", $ex);
    header('Location: /admin/settings?e=Failed to update settings');
    die();
}
?>
