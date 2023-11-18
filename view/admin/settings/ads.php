<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $enable_ads = mysqli_real_escape_string($conn, $_GET['ads:enabled']);
        $ads_code = mysqli_real_escape_string($conn, $_GET['ads:code']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_ads` = '" . $enable_ads . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `ads_code` = '" . $ads_code . "' WHERE `mythicaldash_settings`.`id` = 1;");
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die();
    } else {
        header('location: /admin/settings?e=Failed to update the settings inside the database');
        die();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ",$ex);
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die();
}
?>