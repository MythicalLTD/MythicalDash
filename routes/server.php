<?php
$router->add('/server/create', function () {
    require("../include/main.php");
    require("../view/server/create.php");
});

$router->add('/server/delete', function () {
    require("../include/main.php");
    require("../view/server/delete.php");
});

$router->add('/server/edit', function () {
    require("../include/main.php");
    require("../view/server/edit.php");
});

$router->add('/server/active', function () {
    require("../include/main.php");
    require("../view/server/active.php");
});

$router->add('/server/queue/delete', function () {
    require("../include/main.php");
    require("../view/server/queueDelete.php");
});
?>