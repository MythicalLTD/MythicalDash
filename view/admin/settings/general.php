<?php
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $name = mysqli_real_escape_string($conn, $_GET['app:name']);
        $logo = mysqli_real_escape_string($conn, $_GET['app:logo']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `name` = '" . $name . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `logo` = '" . $logo . "' WHERE `mythicaldash_settings`.`id` = 1;");
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die ();
    } else {
        header('location: /admin/settings');
        die ();
    }
} catch (Exception $ex) {
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die ();
}
?>