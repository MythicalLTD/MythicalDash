<?php
use MythicalDash\ErrorHandler;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_GET['update_settings'])) {
        $enable_paypal = mysqli_real_escape_string($conn, $_GET['paypal:enabled']);
        $client_id = mysqli_real_escape_string($conn, $_GET['paypal:client_id']);
        $secret_key = mysqli_real_escape_string($conn, $_GET['paypal:secret_key']);

        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_paypal` = '" . $enable_paypal . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `paypal_client_id` = '" . $client_id . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `paypal_client_secret` = '" . $secret_key . "' WHERE `mythicaldash_settings`.`id` = 1;");
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