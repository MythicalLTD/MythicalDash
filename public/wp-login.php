<?php 
header('Content-type: application/json');
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
try {
    if (file_exists('../vendor/autoload.php')) {
        require("../vendor/autoload.php");
    } else {
        die('Hello, it looks like you did not run:  "<code>composer install --no-dev --optimize-autoloader</code>". Please run that and refresh the page');
    }
} catch (Exception $e) {
    die('Hello, it looks like you did not run:  <code>composer install --no-dev --optimize-autoloader</code> Please run that and refresh');
}
use MythicalSystems\Api\ResponseHandler;

ResponseHandler::NotFound("Nice try :)");
?>