<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_POST['update_settings'])) {
        $customhead_enabled = mysqli_real_escape_string($conn, $_POST['customhead:enabled']);
        $customhead_code = mysqli_real_escape_string($conn, $_POST['customhead:code']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customhead_enabled` = '" . $customhead_enabled . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customhead_code` = '" . $customhead_code . "' WHERE `mythicaldash_settings`.`id` = 1;");
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