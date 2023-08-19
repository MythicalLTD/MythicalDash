<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $mail_enable = mysqli_real_escape_string($conn,$_GET['mail:enable']);
    $mail_encrytion = mysqli_real_escape_string($conn,$_GET['mail:encryption']);
    $mail_host = mysqli_real_escape_string($conn,$_GET['mail:host']);
    $mail_port = mysqli_real_escape_string($conn,$_GET['mail:port']);
    $mail_username = mysqli_real_escape_string($conn,$_GET['mail:username']);
    $mail_password = mysqli_real_escape_string($conn,$_GET['mail:password']);
    $mail_from_address = mysqli_real_escape_string($conn,$_GET['mail:from:address']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_smtp` = '" . $mail_enable . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `smtpHost` = '" . $mail_encrytion . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `smtpHost` = '" . $mail_host . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `smtpPort` = '" . $mail_port . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `smtpUsername` = '" . $mail_username . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `smtpPassword` = '" . $mail_password . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `fromEmail` = '" . $mail_from_address . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    $conn->close();
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>