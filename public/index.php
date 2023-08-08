<?php
try {
    if (file_exists('../vendor/autoload.php')) { 
        require("../vendor/autoload.php");
    } else {
        die('Hello, it looks like you did not run:  "<code>composer install --no-dev --optimize-autoloader</code>". Please run that and refresh the page');
    }
} catch (Exception $e) {
    die('Hello, it looks like you did not run:  <code>composer install --no-dev --optimize-autoloader</code> Please run that and refresh');
}
require("../functions/https.php");
if (!isHTTPS()) {
    header('HTTP/1.1 403 Forbidden');
    http_response_code(403);
    $rsp = array(
        "code" => 403,
        "error" => "This application only runs on https"
    );
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$router = new \Router\Router();
if (file_exists('FIRST_INSTALL')) {
    $router->add("/", function() {
        require("../install/welcome.php");
    });
    $router->add("/server/check", function() {
        require("../install/servercheck.php");
    });
    $router->add("/server/mysql", function() {
        require("../install/mysql.php");
    });
    $router->add("/server/config", function() {
        require("../install/config.php");
    });
    $router->add("/api/mysql", function() {
        require("../api/mysql.php");
    });
    $router->add("/(.*)", function() {
        header('location: /');
    });

    $router->route();
}
else
{
    $router->add('/', function () {
        require("../include/main.php");
        require("../view/index.php");
    });
    
    $router->add("/api/mysql", function() {
        require("../api/mysql.php");
    });

    $router->add("/api/client/user/info", function() {
        require("../include/main.php");
        require("../api/client/user/info.php");
    });


    $router->add("/api/admin/user/info", function() {
        require("../include/main.php");
        require("../api/admin/user/info.php");
    });

    $router->add("/api/admin/showusers", function() {
        require("../include/main.php");
        require("../api/admin/users.php");
    });

    $router->add("/api/admin/statistics", function() {
        require("../include/main.php");
        require("../api/admin/statistics.php");
    });

    $router->add('/auth/login', function () {
        require("../include/main.php");
        require("../view/auth/login.php");
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

    $router->add('/dashboard', function () {
        require("../include/main.php");
        require("../view/dashboard.php");
    });
    
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

    $router->add("/e/critical", function () {
        require("../view/errors/critical.php");
    });
    
    $router->add("/e/404", function () {
        require("../include/main.php");
        require("../view/errors/404.php");
    });
    
    $router->add("/user/profile",function () {
        require("../include/main.php");
        require("../view/user/profile.php");
    });
    
    $router->add("/user/security/delete_account",function () {
        require("../include/main.php");
        require("../view/user/deleteacc.php");
    });
    
    $router->add("/user/security/resetkey",function () {
        require("../include/main.php");
        require("../view/user/resetkey.php");
    });
    
    $router->add("/user/security/resetpwd",function () {
        require("../include/main.php");
        require("../view/user/resetpwd.php");
    });
    
    $router->add("/blank",function () {
        require("../include/main.php");
        require("../view/blank.php");
    });
    
    $router->add("/admin/users/view",function () {
        require("../include/main.php");
        require("../view/admin/users/view_users.php");
    });
    
    $router->add("/admin/users/edit",function () {
        require("../include/main.php");
        require("../view/admin/users/edit_user.php");
    });
    
    $router->add("/admin/users/new",function () {
        require("../include/main.php");
        require("../view/admin/users/new_user.php");
    });
    
    $router->add("/admin/users/delete",function () {
        require("../include/main.php");
        require("../view/admin/users/delete_user.php");
    });
    
    $router->add("/admin/users/security/resetkey",function() {
        require("../include/main.php");
        require("../view/admin/users/user_reset_key.php");
    });
    
    $router->add("/admin/users/security/resetpwd",function() {
        require("../include/main.php");
        require("../view/admin/users/user_reset_password.php");
    });

    $router->add("/email/reset-password",function() {
        require("../include/main.php");
        require("../view/email/reset-password.php");
    });
    
    $router->add("/e/401", function () {
        require("../include/main.php");
        require("../view/errors/401.php");
    });
    
    $router->add("/e/maintenance", function () {
        require("../include/main.php");
        require("../view/errors/maintenance.php");
    });
    
    $router->add("/(.*)", function () {
        require("../include/main.php");
        require("../view/errors/404.php");
    });
    $router->route();
}
?>