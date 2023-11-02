<?php 
$router->add("/admin/eggs/delete", function () {
    require("../include/main.php");
    require("../view/admin/eggs/delete.php");
});

$router->add("/admin/eggs/create", function () {
    require("../include/main.php");
    require("../view/admin/eggs/create.php");
});

$router->add("/admin/eggs/list", function () {
    require("../include/main.php");
    require("../view/admin/eggs/main.php");
});
   
$router->add("/admin/eggs/config", function () {
    require("../include/main.php");
    require("../view/admin/eggs/manager_list.php");
});
    
$router->add("/admin/eggs/config/create", function () {
    require("../include/main.php");
    require("../view/admin/eggs/manager_create.php");
});
    
$router->add("/admin/eggs/config/delete", function () {
    require("../include/main.php");
    require("../view/admin/eggs/manager_delete.php");
});
    
?>