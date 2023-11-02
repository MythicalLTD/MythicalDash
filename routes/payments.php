<?php 
$router->add("/store/buy/stripe/coins", function () {
    require("../include/main.php");
    require("../view/stripe/buy_coins.php");
});

$router->add("/store/get/stripe/coins", function () {
    require("../include/main.php");
    require("../view/stripe/get_coins.php");
});

?>