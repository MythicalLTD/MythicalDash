<?php 
namespace MythicalDash\Database;

use MythicalDash\ErrorHandler;
use Symfony\Component\Yaml\Yaml;
use mysqli;

class Connect{
    public function connectToDatabase() {
        $MythicalDashConfig = Yaml::parseFile(__DIR__.'/../../config.yml');

        $dbsettings = $MythicalDashConfig['database'];
        $dbhost = $dbsettings['host'];
        $dbport = $dbsettings['port'];
        $dbusername = $dbsettings['username'];
        $dbpassword = $dbsettings['password'];
        $dbname = $dbsettings['database'];

        $conn = new mysqli($dbhost . ':' . $dbport, $dbusername, $dbpassword, $dbname);

        if ($conn->connect_error) {
            ErrorHandler::ShowCritical("Failed to connect to the MySQL server: ".$conn->connect_error);
            die();
        }

        return $conn;
    }
}
?>