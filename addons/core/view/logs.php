<?php
use MythicalDash\Database\Connect;
use MythicalDash\ErrorHandler;

$conn = new Connect();
$conn = $conn->connectToDatabase();
header('Content-type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
if (!is_writable(__DIR__)) {
    http_response_code(500);
    $rsp = array(
        'code' => 500,
        'error' => 'The server is not ready to handle the request.',
        'message' => 'We have no write permission for our home directory. Please update the permission by executing this in the server shell: chown -R www-data:www-data /var/www/mythicaldash/ && chown -R www-data:www-data /var/www/mythicaldash/*'
    );
    die (json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
try {
    $logFilePath = '../logs/mythicaldash.app-error.log';
    $logContent = file_get_contents($logFilePath);
    
    $conn->close();
    http_response_code(200);
    if ($logContent == null) 
    {
        die("No logs to show");
    } else {
        die ($logContent);
    }
} catch (Exception $e) {
    ErrorHandler::Critical('Settings DB ', $e);
    $rsp = array(
        'code' => 500,
        'error' => "The server encountered a situation it doesn't know how to handle.",
        'message' => 'We are sorry, but our server cannot handle this request. Please do not try again!'
    );
    http_response_code(500);
    die (json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>
