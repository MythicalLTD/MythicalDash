<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['update_settings'])) {
    $seo_description = mysqli_real_escape_string($conn,$_GET['seo:description']);
    $seo_keywords = mysqli_real_escape_string($conn,$_GET['seo:keywords']);
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `seo_description` = '" . $seo_description . "' WHERE `mythicaldash_settings`.`id` = 1;");
    mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `seo_keywords` = '" . $seo_keywords . "' WHERE `mythicaldash_settings`.`id` = 1;");
    header('location: /admin/settings');
    die();
} else {
    header('location: /admin/settings');
    die();
}
?>