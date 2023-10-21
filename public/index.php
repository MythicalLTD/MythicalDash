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
    $router->add("/", function () {
        require("../install/welcome.php");
    });
    $router->add("/server/check", function () {
        require("../install/servercheck.php");
    });

    $router->add("/(.*)", function () {
        header('location: /');
    });

    $router->route();

} else {
    //Default routes and static routes 
    include(__DIR__ . '/../routes/index.php');
    //Default api routes and static routes 
    include(__DIR__ . '/../routes/api/index.php');
    //Default admin routes and static routes 
    include(__DIR__ . '/../routes/admin/index.php');
    //Routes for /auth/
    include(__DIR__ . '/../routes/auth.php');
    //Routes for /server/
    include(__DIR__ . '/../routes/server.php');
    //Routes for /help-center/
    include(__DIR__ . '/../routes/help-center.php');
    //Routes for /user/
    include(__DIR__ . '/../routes/user.php');
    //Routes for /earn/
    include(__DIR__ . '/../routes/earn.php');
    //Routes for /admin/api/
    include(__DIR__ . '/../routes/admin/api.php');
    //Routes for /admin/settings/
    include(__DIR__ . '/../routes/admin/settings.php');
    //Routes for /admin/redeem/
    include(__DIR__ . '/../routes/admin/redeem.php');
    //Routes for /admin/users/
    include(__DIR__ . '/../routes/admin/users.php');
    //Routes for /admin/eggs/
    include(__DIR__ . '/../routes/admin/eggs.php');
    //Routes for /admin/locations/
    include(__DIR__ . '/../routes/admin/locations.php');

    $router->add("/(.*)", function () {
        require("../include/main.php");
        require("../view/errors/404.php");
    });
    $router->route();
}

?>