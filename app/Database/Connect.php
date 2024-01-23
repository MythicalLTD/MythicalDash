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

    public static function getUserInfo(string $userToken, string $info) {
        $connclass = new Connect();
        $conn = $connclass->connectToDatabase();
        $session_id = mysqli_real_escape_string($conn, $userToken);
        $safeInfo = $conn->real_escape_string($info);
        $query = "SELECT `$safeInfo` FROM mythicaldash_users WHERE api_key='$session_id' LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row[$info];
        } else {
            return null;
        }

    }
}
?>