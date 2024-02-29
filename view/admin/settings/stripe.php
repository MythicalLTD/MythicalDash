<?php
use MythicalDash\ErrorHandler;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_GET['update_settings'])) {
        $enable_stripe = mysqli_real_escape_string($conn, $_GET['stripe:enabled']);
        $stripe_publishable_key = mysqli_real_escape_string($conn, $_GET['stripe:public_key']);
        $stripe_secret_key = mysqli_real_escape_string($conn, $_GET['stripe:private_key']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_stripe` = '" . $enable_stripe . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `stripe_publishable_key` = '" . $stripe_publishable_key . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `stripe_secret_key` = '" . $stripe_secret_key . "' WHERE `mythicaldash_settings`.`id` = 1;");
        header('location: /admin/settings?s=We updated the settings inside the database');
        $conn->close();
        die ();
    } else {
        header('location: /admin/settings?e=Failed to update the settings inside the database');
        die();
    }
} catch (Exception $ex) {
    ErrorHandler::Critical("Failed to update settings ",$ex);
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die ();
}
?>