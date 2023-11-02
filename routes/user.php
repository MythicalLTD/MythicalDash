<?php 
$router->add("/user/edit", function () {
    require("../include/main.php");
    require("../view/user/edit.php");
});

$router->add("/user/profile", function () {
    require("../include/main.php");
    require("../view/user/profile.php");
});

$router->add("/user/gift", function () {
    require("../include/main.php");
    require("../view/user/gift.php");
});

$router->add("/user/connections", function () {
    require("../include/main.php");
    require("../view/user/connections.php");
});

$router->add("/users/list", function () {
    require("../include/main.php");
    require("../view/user/list.php");
});

$router->add("/user/security/delete_account", function () {
    require("../include/main.php");
    require("../view/user/deleteacc.php");
});

$router->add("/user/security/resetkey", function () {
    require("../include/main.php");
    require("../view/user/resetkey.php");
});

$router->add("/user/security/resetpwd", function () {
    require("../include/main.php");
    require("../view/user/resetpwd.php");
});

$router->add("/user/payments", function () {
    require("../include/main.php");
    require("../view/user/payments.php");
});
?>