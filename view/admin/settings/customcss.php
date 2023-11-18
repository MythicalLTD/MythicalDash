<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_POST['update_settings'])) {
        $customcss_enabled = mysqli_real_escape_string($conn, $_POST['customcss:enabled']);
        $customcss_code = mysqli_real_escape_string($conn, $_POST['customcss:code']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customcss_enabled` = '" . $customcss_enabled . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customcss_code` = '" . $customcss_code . "' WHERE `mythicaldash_settings`.`id` = 1;");
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