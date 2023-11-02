<?php echo "====== MythicalDash queue ======\n\n";
use MythicalDash\SettingsManager;

echo "[INFO/loader] Loading files...\n";
include(__DIR__ . '/../vendor/autoload.php');
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parseFile(__DIR__ . '/../config.yml');
$appsettings = $config['app'];
$cfg_debugmode = $appsettings['debug'];
if ($cfg_debugmode == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
//DATABASE CONNECTION
$dbsettings = $config['database'];
$dbhost = $dbsettings['host'];
$dbport = $dbsettings['port'];
$dbusername = $dbsettings['username'];
$dbpassword = $dbsettings['password'];
$dbname = $dbsettings['database'];
$conn = new mysqli($dbhost . ':' . $dbport, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    echo "[WARNING] We cannot connect to the MySQL server.";
}
$timeAtStart = time();
$i = 0;
$nodesFull = 0;
echo "[INFO/loader] Fetching the servers in queue...\n";
$queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue ORDER BY type DESC");
echo "[INFO/loader] " . $queue->num_rows . " servers in queue!\n";
echo "\033[32m[INFO/loader] Processing started!\n";
foreach ($queue as $server) {
    $i++;
    echo "\033[39m";
    $time = time();
    $date = date("d:m:y h:i:s");
    echo "[INFO] Processing server " . $server['name'] . PHP_EOL;
    $location = $server['location'];
    $locationd = mysqli_query($conn, "SELECT * FROM mythicaldash_locations WHERE id = '" . mysqli_real_escape_string($conn, $location) . "'");
    if ($locationd->num_rows == 0) {
        echo "\033[31m[WARNING] Location does not exist." . PHP_EOL;
        echo "[$i/" . $queue->num_rows . "] There was an error while creating the server ``" . $server['name'] . "``! The location provided in the queue table doesn't exist!";
        continue;
    }
    $locationd = $locationd->fetch_assoc();
    $slots_used = $conn->query("SELECT * FROM mythicaldash_servers WHERE location = '$location'")->num_rows;
    $slots_all = $locationd['slots'];
    if ($slots_used >= $slots_all) {
        if ($server['type'] != "2") {
            echo "\033[31m[INFO] No slots available to create server " . $server['name'] . PHP_EOL;
            $nodesFull++;
            continue;
        }

    }
    $egg = $server['egg'];
    $ee_egg = $server['egg'];

    $eggd = mysqli_query($conn, "SELECT * FROM mythicaldash_eggs WHERE id = '" . mysqli_real_escape_string($conn, $egg) . "'");
    if ($eggd->num_rows == 0) {
        echo "\033[33m[WARNING $date] Egg does not exist." . PHP_EOL;
        continue;
    }
    $egg = $eggd->fetch_object();
    $egginfocurl = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/nests/" . $egg->nest . "/eggs/" . $egg->egg);
    $httpheader = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
    );
    curl_setopt($egginfocurl, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($egginfocurl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($egginfocurl);
    curl_close($egginfocurl);
    $response = json_decode($response, true);
    $docker_image = $response['attributes']['docker_image'];
    $startup = $response['attributes']['startup'];
    $ports = $server['xtra_ports'] + 1;
    $sql = "SELECT setting_name, setting_value FROM mythicaldash_eggs_config";
    $result = $conn->query($sql);

    $environment = array(); // Initialize an empty environment array

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Populate the 'environment' array with settings from the database
            $settingName = $row["setting_name"];
            $settingValue = $row["setting_value"];
            $environment[$settingName] = $settingValue;
        }
    }

    $panelcurl = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers");
    $postfields = array(
        'name' => $server['name'],
        'user' => $server['puid'],
        'egg' => $egg->egg,
        'nest' => $egg->nest,
        'docker_image' => $docker_image,
        'startup' => $startup,
        'environment' => $environment,
        // Include the 'environment' array here
        'limits' => array(
            'memory' => $server['ram'],
            'swap' => $server['ram'],
            'disk' => $server['disk'],
            'io' => 500,
            'cpu' => $server['cpu']
        ),
        'feature_limits' => array(
            "databases" => $server['databases'],
            "backups" => $server['backuplimit'],
            "allocations" => $ports
        ),
        "deploy" => array(
            "locations" => [$locationd['locationid']],
            "dedicated_ip" => false,
            "port_range" => []
        )
    );

    $postfields = json_encode($postfields, true);
    curl_setopt($panelcurl, CURLOPT_POST, 1);
    curl_setopt($panelcurl, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($panelcurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
        $panelcurl,
        CURLOPT_HTTPHEADER,
        array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
        )
    );
    $result = curl_exec($panelcurl);
    curl_close($panelcurl);
    $ee = json_decode($result, true);
    if (!isset($ee['object'])) {
        echo "\033[31m[ERROR $date] Server failed to create, error details are as follows.\nCode: " . $ee['errors'][0]['code'] . "\nDetail: " . $ee['errors'][0]['detail'] . PHP_EOL;
        //logClient("[$i/" . $queue->num_rows . "] There was an error while creating the server ``" . $server['name'] . "``! ```" . $ee['errors'][0]['detail'] . "```");
        continue;
    }
    $identifier = $ee['attributes']['identifier'];
    $pid = $ee['attributes']['id'];
    $uid = $server['ownerid'];
    $location = $locationd['id'];
    $svname = $server['name'];
    mysqli_query($conn, "DELETE FROM mythicaldash_servers_queue WHERE id=" . $server['id']);
    $created = date("d-m-y", time());
    if (mysqli_query($conn, 'INSERT INTO mythicaldash_servers (`pid`, `uid`, `location`, `egg_id`) VALUES ("' . $pid . '", "' . $uid . '", "' . $location . '", "' . $ee_egg . '")')) {
        echo "\033[32m[SUCCESS] The server called " . $server['name'] . " got created.";
        //logClient("[$i/" . $queue->num_rows . "] The server called ``" . $server['name'] . "`` got created.");
        $conn->close();
        die();
    } else {
        echo "\033[31m[INFO] Error inserting server into db." . PHP_EOL;
        echo mysqli_error($conn);
        $conn->close();
        die();
    }
}
?>