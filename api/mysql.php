<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
if (isset($_GET['host']) && isset($_GET['username']) && isset($_GET['database'])) {
    $dbhost = $_GET['host'];
    $dbport = $_GET['port'];
    $dbusername = $_GET['username'];
    $dbpassword = $_GET['password'];
    $dbname = $_GET['database'];
    if ($dbport == "") {
        $dbport = "3306";
    }
    $connection = new mysqli($dbhost.':'.$dbport, $dbusername, $dbpassword, $dbname);
    if ($connection->connect_error) {
        http_response_code(500); 
        echo json_encode(array('status' => 'error', 'message' => 'The connection failed: '.$connection->connect_error), JSON_PRETTY_PRINT);
    }
    echo json_encode(array('status' => 'success', 'message' => 'The connection was successful'), JSON_PRETTY_PRINT);
    $connection->close();
    http_response_code(200); 
}
else
{
    http_response_code(400); 
    echo json_encode(array('status' => 'error', 'message' => 'Missing database connection info: https://github.com/MythicalLTD/MythicalDash#api-reference'),JSON_PRETTY_PRINT);
}
?>