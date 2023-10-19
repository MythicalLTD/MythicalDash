<?php 
$router->add("/api", function () {
    require("../include/main.php");
    require("../api/main.php");
});

$router->add("/api/admin/statistics", function () {
    require("../include/main.php");
    require("../api/admin/statistics.php");
});


?>