<?php
$router->add('/', function () {
    require("../include/main.php");
    require("../view/index.php");
});

$router->add('/dashboard', function () {
    require("../include/main.php");
    require("../view/dashboard.php");
});

$router->add("/store", function () {
    require("../include/main.php");
    require("../view/store.php");
});

$router->add("/e/critical", function () {
    require("../view/errors/critical.php");
});

$router->add("/e/404", function () {
    require("../include/main.php");
    require("../view/errors/404.php");
});

$router->add("/e/401", function () {
    require("../include/main.php");
    require("../view/errors/401.php");
});

$router->add("/e/maintenance", function () {
    require("../include/main.php");
    require("../view/errors/maintenance.php");
});

$router->add("/blank", function () {
    require("../include/main.php");
    require("../view/blank.php");
});

?>