<?php 
$router->add("/admin/tickets", function () {
    require("../include/main.php");
    require("../view/admin/tickets/list.php");
});

$router->add("/admin", function () {
    header('location: /admin/overview');
    die();
});

$router->add("/admin/overview", function () {
    require("../include/main.php");
    require("../view/admin/main.php");
});
?>