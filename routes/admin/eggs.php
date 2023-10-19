<?php 
$router->add("/admin/eggs/delete", function () {
    require("../include/main.php");
    require("../view/admin/eggs/delete.php");
});

$router->add("/admin/eggs/create", function () {
    require("../include/main.php");
    require("../view/admin/eggs/create.php");
});

$router->add("/admin/eggs", function () {
    require("../include/main.php");
    require("../view/admin/eggs/main.php");
});
    
?>