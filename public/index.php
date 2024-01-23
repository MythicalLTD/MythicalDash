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
use MythicalDash\Main;
use MythicalDash\ErrorHandler;
use MythicalDash\AddonsManager;

if (!Main::isHTTPS()) {
    ErrorHandler::ShowCritical("We are sorry, but the dash can only run on HTTPS, not HTTP.");
    die();
}

if (!is_writable(__DIR__)) {
    ErrorHandler::ShowCritical("We have no access to our client directory. Open the terminal and run: chown -R www-data:www-data /var/www/mythicaldash/*");
    die();
}

$router = new \Router\Router();
$addonsManager = new AddonsManager();
$loadedAddons = $addonsManager->loadAddons();

if (file_exists('FIRST_INSTALL')) {
    $router->add("/", function () {
        require("../view/install/welcome.php");
    });
    $router->add("/server/check", function () {
        require("../view/install/servercheck.php");
    });

    $router->add("/(.*)", function () {
        header('location: /');
    });

    $router->route();

} else {
    $routesViewDirectory = __DIR__ . '/../routes/';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($routesViewDirectory));
    $phpViewFiles = new RegexIterator($iterator, '/\.php$/');

    foreach ($phpViewFiles as $phpViewFile) {
        try {
            http_response_code(200);
            include $phpViewFile->getPathname();
        } catch (Exception $ex) {
            http_response_code(500);
            ErrorHandler::ShowCritical('Failed to start app: ' . $ex->getMessage());
        }
    }

    $addonsManager->processAddonRoutes($router);

    $router->add("/(.*)", function () {
        require("../include/main.php");
        require("../view/errors/404.php");
    });
    try {
        $router->route();
    } catch (Exception $e) {
        ErrorHandler::Critical("Automated Message", $e->getMessage());
    }
}

?>