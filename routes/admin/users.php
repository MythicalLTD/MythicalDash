<?php
$router->add("/admin/users", function () {
    require("../include/main.php");
    require("../view/admin/users/list.php");
});

$router->add("/admin/users/edit", function () {
    require("../include/main.php");
    require("../view/admin/users/edit_user.php");
});

$router->add("/admin/users/new", function () {
    require("../include/main.php");
    require("../view/admin/users/new_user.php");
});

$router->add("/admin/users/delete", function () {
    require("../include/main.php");
    require("../view/admin/users/delete_user.php");
});

$router->add("/admin/users/security/resetkey", function () {
    require("../include/main.php");
    require("../view/admin/users/user_reset_key.php");
});

$router->add("/admin/users/security/resetpwd", function () {
    require("../include/main.php");
    require("../view/admin/users/user_reset_password.php");
});
?>