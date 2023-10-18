<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $customcss_enabled = mysqli_real_escape_string($conn,$_GET['customcss:enabled']);
    $customcss_code = mysqli_real_escape_string($conn,$_GET['customcss:code']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customcss_enabled` = '" . $customcss_enabled . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `customcss_code` = '" . $customcss_code . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    $conn->close();
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>