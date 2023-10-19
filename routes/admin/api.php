<?php
$router->add("/admin/api/create", function () {
    require("../include/main.php");
    require("../view/admin/api/create.php");
});

$router->add("/admin/api/delete", function () {
    require("../include/main.php");
    require("../view/admin/api/delete.php");
});

$router->add("/admin/api", function () {
    require("../include/main.php");
    require("../view/admin/api/main.php");
});
?>