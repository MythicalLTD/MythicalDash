<?php 
$router->add("/admin/tickets", function () {
    require("../include/main.php");
    require("../view/admin/tickets/list.php");
});

?>