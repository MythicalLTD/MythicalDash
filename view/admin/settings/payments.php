<?php
use MythicalDash\ErrorHandler;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_GET['update_settings'])) {
        $allow_payments = mysqli_real_escape_string($conn, $_GET['payments:enabled']);
        $payments_currency = mysqli_real_escape_string($conn, $_GET['payments:payments_currency']);
        $coin_per_balance = mysqli_real_escape_string($conn, $_GET['payments:coin_per_balance']);
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `allow_payments` = '" . $allow_payments . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `payments_currency` = '" . $payments_currency . "' WHERE `mythicaldash_settings`.`id` = 1;");
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `coin_per_balance` = '" . $coin_per_balance . "' WHERE `mythicaldash_settings`.`id` = 1;");
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