<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $customhead_enabled = mysqli_real_escape_string($conn,$_GET['customhead:enabled']);
    $customhead_code = mysqli_real_escape_string($conn,$_GET['customhead:code']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customhead_enabled` = '" . $customhead_enabled . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customhead_code` = '" . $customhead_code . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    $conn->close();
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>