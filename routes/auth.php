<?php 
$router->add('/auth/login', function () {
    require("../include/main.php");
    require("../view/auth/login.php");
});

$router->add('/auth/link/discord', function () {
    require("../include/main.php");
    require("../view/auth/link/discord.php");
});

$router->add('/auth/discord', function () {
    require("../include/main.php");
    require("../view/auth/discord.php");
});

$router->add('/auth/register', function () {
    require("../include/main.php");
    require("../view/auth/register.php");
});

$router->add('/auth/logout', function () {
    require("../include/main.php");
    require("../functions/logout.php");
});

$router->add('/auth/forgot-password', function () {
    require("../include/main.php");
    require("../view/auth/forgot-password.php");
});

$router->add('/auth/reset-password', function () {
    require("../include/main.php");
    require("../view/auth/reset-password.php");
});

?>