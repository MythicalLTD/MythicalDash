<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $name = mysqli_real_escape_string($conn, $_GET['app:name']);
        $logo = mysqli_real_escape_string($conn, $_GET['app:logo']);
        $snow = mysqli_real_escape_string($conn, $_GET['app:snow']);
        
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `name` = '" . $name . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `logo` = '" . $logo . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `show_snow` = '" . $snow . "' WHERE `mythicaldash_settings`.`id` = 1;");
        
        if ($snow == "false") {
            header('location: /admin/settings?e=Well it looks like you are grinch!');
        } else {
            header('location: /admin/settings?s=We updated the settings inside the database');
        }
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