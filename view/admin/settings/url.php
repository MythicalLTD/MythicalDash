<?php
use MythicalDash\ErrorHandler;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $website = mysqli_real_escape_string($conn, $_GET['url:website']);
        $status = mysqli_real_escape_string($conn, $_GET['url:status']);
        $x = mysqli_real_escape_string($conn, $_GET['url:x']);
        
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `website` = '" . $website . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `status` = '" . $status . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `x` = '" . $x . "' WHERE `mythicaldash_settings`.`id` = 1;");
        
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die();
    } else {
        header('location: /admin/settings?e=Failed to update the settings inside the database');
        die();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ", $ex);
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die();
}
?>