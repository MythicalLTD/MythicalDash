<?php 
$router->add("/admin/settings", function () {
    require("../include/main.php");
    require("../view/admin/settings/main.php");
});

$router->add("/admin/settings/discord", function () {
    require("../include/main.php");
    require("../view/admin/settings/discord.php");
});

$router->add("/admin/settings/linkvertise", function () {
    require("../include/main.php");
    require("../view/admin/settings/linkvertise.php");
});

$router->add("/admin/settings/mail", function () {
    require("../include/main.php");
    require("../view/admin/settings/mail.php");
});

$router->add("/admin/settings/general", function () {
    require("../include/main.php");
    require("../view/admin/settings/general.php");
});

$router->add("/admin/settings/recaptcha", function () {
    require("../include/main.php");
    require("../view/admin/settings/recaptcha.php");
});

$router->add("/admin/settings/ads", function () {
    require("../include/main.php");
    require("../view/admin/settings/ads.php");
});

$router->add("/admin/settings/customcss", function () {
    require("../include/main.php");
    require("../view/admin/settings/customcss.php");
});

$router->add("/admin/settings/customhead", function () {
    require("../include/main.php");
    require("../view/admin/settings/customhead.php");
});

$router->add("/admin/settings/pterodactyl", function () {
    require("../include/main.php");
    require("../view/admin/settings/pterodactyl.php");
});


$router->add("/admin/settings/seo", function () {
    require("../include/main.php");
    require("../view/admin/settings/seo.php");
});

$router->add("/admin/settings/resources", function () {
    require("../include/main.php");
    require("../view/admin/settings/resources.php");
});

$router->add("/admin/settings/store", function () {
    require("../include/main.php");
    require("../view/admin/settings/store.php");
});
?>