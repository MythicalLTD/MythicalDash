<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $rechapa_status = mysqli_real_escape_string($conn,$_GET['recaptcha:enabled']);
    $rechapa_sitekey = mysqli_real_escape_string($conn,$_GET['recaptcha:website_key']);
    $rechapa_secretKey = mysqli_real_escape_string($conn,$_GET['recaptcha:secret_key']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `enable_turnstile` = '" . $rechapa_status . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `turnstile_sitekey` = '" . $rechapa_sitekey . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `turnstile_secretkey` = '" . $rechapa_secretKey . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
    
} else {
    header('location: /admin/settings');
    die();
}
?>