<?php 
use MythicalDash\Encryption;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['key'])) {
    $keyname = mysqli_real_escape_string($conn,$_GET['name']);
    $api_key = "mythicaldash_" .Encryption::generate_keynoinfo();
    mysqli_query($conn, "INSERT INTO `mythicaldash_apikeys` (`name`,`skey`, `ownerkey`) VALUES ('" . $keyname . "', '" . $api_key . "', '" . mysqli_real_escape_string($conn,$_COOKIE["token"]) . "')");
    header('location: /admin/api');
}
else {
    header('location: /admin/api'); 
}
?>