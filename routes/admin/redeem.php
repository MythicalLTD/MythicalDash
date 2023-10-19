<?php 
$router->add("/admin/redeem", function () {
    require("../include/main.php");
    require("../view/admin/redeem/main.php");
});

$router->add("/admin/redeem/create", function () {
    require("../include/main.php");
    require("../view/admin/redeem/create.php");
});

$router->add("/admin/redeem/delete", function () {
    require("../include/main.php");
    require("../view/admin/redeem/delete.php");
});
?>