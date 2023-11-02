<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $enable_adblocker_detection = mysqli_real_escape_string($conn, $_GET['ads:adblocker']);
        $enable_alting = mysqli_real_escape_string($conn, $_GET['enable_alting']);
        $enable_anti_vpn = mysqli_real_escape_string($conn, $_GET['enable_anti_vpn']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_adblocker_detection` = '" . $enable_adblocker_detection . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_alting` = '" . $enable_alting . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_anti_vpn` = '" . $enable_anti_vpn . "' WHERE `mythicaldash_settings`.`id` = 1;");
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die();
    } else {
        header('location: /admin/settings');
        die();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ",$e);
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die();
}
?>