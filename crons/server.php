<?php echo "====== MythicalDash queue ======\n\n";
use MythicalDash\SettingsManager; 
echo "[INFO/loader] Loading files...\n"; 
include(__DIR__.'/../vendor/autoload.php');
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile(__DIR__.'/../config.yml');
$appsettings = $config['app'];
$cfg_debugmode = $appsettings['debug'];
if ($cfg_debugmode == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
else
{
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
    echo "[WARNING] We can't connect to the MySQL server.";
}
$timeAtStart = time();
$i = 0;
$nodesFull = 0;
echo "[INFO/loader] Fetching the servers in queue...\n";
$queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue ORDER BY type DESC");
echo "[INFO/loader] " . $queue->num_rows . " servers in queue!\n";
echo "\033[32m[INFO/loader] Processing started!\n";
foreach($queue as $server) { 
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
    $panelcurl = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers");
    $postfields = array(
        'name' => $server['name'],
        'user' => $server['puid'],
        'egg' => $egg->egg,
        'nest' => $egg->nest,
        'docker_image' => $docker_image,
        'startup' => $startup,
        'environment' => array(
            'BUNGEE_VERSION' => "latest",
            'SERVER_JARFILE' => "server.jar",
            'BUILD_NUMBER' => "latest",
            'MC_VERSION' => 'latest',
            'BUILD_TYPE' => 'recommended',
            'SPONGE_VERSION' => '1.12.2-7.3.0',
            'VANILLA_VERSION' => 'latest',
            'NUKKIT_VERSION' => 'latest',
            'VERSION' => 'pm4',
            'MINECRAFT_VERSION' => 'latest',
            'BEDROCK_VERSION' => 'latest',
            'LD_LIBRARY_PATH' => '.',
            'GAMEMODE' => 'survival',
            'CHEATS' => 'false',
            'DIFFICULTY' => 'easy',
            'SERVERNAME' => 'My Bedrock Server',
            'PMMP_VERSION' => 'latest',
            'USER_UPLOAD' => 0,
            'AUTO_UPDATE' => 0,
            'BOT_JS_FILE' => 'index.js',
            'BOT_PY_FILE' => 'index.py',
            'TS_VERSION' => 'latest',
            'FILE_TRANSFER' => '30033',
            'MAX_USERS' => 100,
            'MUMBLE_VERSION' => 'latest',
            'REQUIREMENTS_FILE' => 'requirements.txt',
            'PY_FILE' => 'app.py',
            'GO_PACKAGE' => 'changeme',
            'EXECUTABLE' => 'changeme',
            'LUA_FILE' => 'app.lua',
            'LIT_PACKAGES' => '',
            'JS_FILE' => 'index.js',
            'JARFILE' => 'app.jar',  
            'MAIN_FILE' => 'index.js',
            'PROJECT_FILE' => 'MyProject.sln',
            'PROJECT_DIR' => '/home/container',
            'PGUSER' => 'pterodactyl',
            'PGPASSWORD' => 'Pl3453Ch4n63M3!',
            'SERVER_PASSWORD' => "P@55w0rd",
            "MONGO_USER" => "admin",
            "MONGO_USER_PASS" => "",
            "DRIVER_PORT" => "25568",
            "HTTP_PORT" => "25569",
            "FIVEM_LICENSE" => "",
            "MAX_PLAYERS" => "48",
            "SERVER_HOSTNAME" => "My new FXServer!",
            "FIVEM_VERSION" => "recommended",
            "STEAM_WEBAPIKEY" => "none",
            "TXADMIN_PORT" => "40120",
            "TXADMIN_ENABLE" => 0,
            "SERVER_NAME" => "RAGE:MP Unofficial server",
            "Version" => "R2-1",
            "RCON_PASS" => "RCON_PASS",
            "HOSTNAME" => "A Rust Server",
            "OXIDE" => 0,
            "LEVEL" => "Procedural Map",
            "DESCRIPTION" => "Powered by Pterodactyl",
            "WORLD_SIZE" => 3000,
            "RCON_PORT" => 28016,
            "SAVEINTERVAL" => 60,
            "APP_PORT" => 28082,
            "QUERY_PORT" => 10011,
            "QUERY_PROTOCOLS_VAR" => "raw,http,ssh",
            "QUERY_SSH" => 10022,
            "QUERY_HTTP" => 10080,
            "SRCDS_APPID" => "232250",
            "SRCDS_MAP" => "cp_dustbowl",
            "STEAM_ACC" => "kFXByFpKBNyNScAZNTmbfJhMDUXVdZrX",
            "TICKRATE" => 22,
            "LUA_REFRESH" => 0,
            "ARK_ADMIN_PASSWORD" => "PleaseChangeMe",
            "SERVER_MAP" => "TheIsland",
            "SESSION_NAME" => "A Pterodactyl Hosted ARK Server",
            "BATTLE_EYE" => 1
        ),
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
        ));
    $postfields = json_encode($postfields, true);
    curl_setopt($panelcurl, CURLOPT_POST, 1);
    curl_setopt($panelcurl, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($panelcurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($panelcurl, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey")
    ));
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
    if (mysqli_query($conn, 'INSERT INTO mythicaldash_servers (`pid`, `uid`, `location`, `egg_id`) VALUES ("'.$pid.'", "'.$uid.'", "'.$location.'", "'.$ee_egg.'")')) {
        echo "\033[32m[SUCCESS] The server called " . $server['name'] . " got created.";
        //logClient("[$i/" . $queue->num_rows . "] The server called ``" . $server['name'] . "`` got created.");
        $conn->close();
        die();
    }
    else {
        echo "\033[31m[INFO] Error inserting server into db." . PHP_EOL;
        echo mysqli_error($conn);
        $conn->close();
        die();
    }
}
?>
