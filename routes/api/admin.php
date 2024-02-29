<?php 
$router->add("/api", function () {
    require("../include/main.php");
    require("../api/main.php");
});

$router->add("/api/admin/statistics", function () {
    require("../include/main.php");
    require("../api/admin/statistics.php");
});

$router->add("/api/admin/user/list", function () {
    require("../include/main.php");
    require("../api/admin/user/list.php");
});

$router->add("/api/admin/user/info", function () {
    require("../include/main.php");
    require("../api/admin/user/info.php");
});

$router->add("/api/admin/user/ban", function () {
    require("../include/main.php");
    require("../api/admin/user/ban.php");
});

$router->add("/api/admin/user/unban", function () {
    require("../include/main.php");
    require("../api/admin/user/unban.php");
});

$router->add("/api/admin/user/resources/set", function () {
    require("../include/main.php");
    require("../api/admin/user/resources/set.php");
});

$router->add("/api/admin/user/resources/add", function () {
    require("../include/main.php");
    require("../api/admin/user/resources/add.php");
});

$router->add("/api/admin/user/reset-password", function () {
    require("../include/main.php");
    require("../api/admin/user/reset-password.php");
});

$router->add("/api/admin/settings/get", function () {
    require("../include/main.php");
    require("../api/admin/settings/get.php");
});

$router->add("/api/admin/settings/set", function () {
    require("../include/main.php");
    require("../api/admin/settings/set.php");
});

$router->add("/api/admin/redeem/create", function () {
    require("../include/main.php");
    require("../api/admin/redeem/create.php");
});

$router->add("/api/admin/redeem/info", function () {
    require("../include/main.php");
    require("../api/admin/redeem/info.php");
});

$router->add("/api/admin/redeem/delete", function () {
    require("../include/main.php");
    require("../api/admin/redeem/delete.php");
});


?>