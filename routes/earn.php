<?php 
$router->add("/earn/afk", function () {
    require("../include/main.php");
    require("../view/earn/afk.php");
});

$router->add("/earn/linkvertise", function () {
    require("../include/main.php");
    require("../view/earn/linkvertise.php");
});

$router->add("/earn/redeem", function () {
    require("../include/main.php");
    require("../view/earn/redeem.php");
});

?>