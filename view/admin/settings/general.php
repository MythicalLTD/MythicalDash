<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $name = mysqli_real_escape_string($conn,$_GET['app:name']);
    $logo = mysqli_real_escape_string($conn,$_GET['app:logo']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `name` = '" . $name . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `logo` = '" . $logo . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>