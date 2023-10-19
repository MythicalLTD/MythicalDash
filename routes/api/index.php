<?php 
$router->add("/api/ticket", function () {
    require("../include/main.php");
    require("../api/ticket.php");
});
?>