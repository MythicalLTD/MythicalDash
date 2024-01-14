<?php 
$router->add("/admin/servers/list", function () {
    require("../include/main.php");
    require("../view/admin/servers/list.php");
});

$router->add("/admin/servers/queue/list", function () {
    require("../include/main.php");
    require("../view/admin/servers/listq.php");
});

$router->add("/admin/servers/purge", function () {
    require("../include/main.php");
    require("../view/admin/servers/purge.php");
});

$router->add("/admin/server/delete", function () {
    require("../include/main.php");
    require("../view/admin/servers/delete.php");
});

$router->add("/admin/servers/queue/logs", function () {
    require("../include/main.php");
    require("../view/admin/servers/logs.php");
});

$router->add("/admin/server/queue/delete", function () {
    require("../include/main.php");
    require("../view/admin/servers/deleteq.php");
});

?>
