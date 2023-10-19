<?php 
$router->add('/help-center/tos', function () {
    require("../include/main.php");
    require("../view/legal/termsofservice.php");
});

$router->add('/help-center/pp', function () {
    require("../include/main.php");
    require("../view/legal/privacypolicy.php");
});

$router->add('/help-center', function () {
    require("../include/main.php");
    require("../view/help-center.php");
});

$router->add('/help-center/tickets/new', function () {
    require("../include/main.php");
    require("../view/tickets/new.php");
});

$router->add('/help-center/tickets', function () {
    require("../include/main.php");
    require("../view/tickets/list.php");
});

$router->add('/help-center/tickets/view', function () {
    require("../include/main.php");
    require("../view/tickets/chat.php");
});

$router->add('/help-center/tickets/reply', function () {
    require("../include/main.php");
    require("../view/tickets/reply.php");
});

$router->add('/help-center/tickets/close', function () {
    require("../include/main.php");
    require("../view/tickets/close.php");
});

$router->add('/help-center/tickets/reopen', function () {
    require("../include/main.php");
    require("../view/tickets/reopen.php");
});

$router->add('/help-center/tickets/delete', function () {
    require("../include/main.php");
    require("../view/tickets/delete.php");
});
?>