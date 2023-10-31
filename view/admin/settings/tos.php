<?php
use MythicalDash\ErrorHandler;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_POST['update_settings'])) {
        $text = mysqli_real_escape_string($conn, $_POST['text']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `terms_of_service` = '" . $text . "' WHERE `mythicaldash_settings`.`id` = 1;");
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die ();
    } else {
        header('location: /admin/settings?e=Failed to update the settings inside the database');
        die ();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ",$e);
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die ();
}
?>