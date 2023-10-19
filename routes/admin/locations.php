<?php 
$router->add("/admin/locations", function () {
    require("../include/main.php");
    require("../view/admin/locations/main.php");
});

$router->add("/admin/locations/create", function () {
    require("../include/main.php");
    require("../view/admin/locations/create.php");
});

$router->add("/admin/locations/delete", function () {
    require("../include/main.php");
    require("../view/admin/locations/delete.php");
});
?>