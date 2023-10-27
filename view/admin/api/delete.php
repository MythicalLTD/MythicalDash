<?php 
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['id']) && !$_GET['id'] == "") {
    $keyid = mysqli_real_escape_string($conn,$_GET['id']);
    mysqli_query($conn, "DELETE FROM mythicaldash_apikeys WHERE `mythicaldash_apikeys`.`id` = " . $keyid . "");
    header('location: /admin/api');
}
else {
    header('location: /admin/api');
}
?>