<?php echo "====== MythicalDash queue ======\n\n"; 
echo "[INFO/loader] Loading files...\n"; 
include(__DIR__.'/../vendor/autoload.php');
include(__DIR__.'/../functions/passwordgen.php');
include(__DIR__.'/../functions/keygen.php');
include(__DIR__.'/../functions/encryption.php');
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile(__DIR__.'/../config.yml');
$appsettings = $config['app'];
$cfg_debugmode = $appsettings['debug'];
$cfg_ignoredebugmodemsg = $appsettings['silent_debug'];
$ekey = $appsettings['encryptionkey'];
$cfg_is_console_on = $appsettings['disable_console'];
if ($ekey == "") {
    die("[INFO/loader] Failed to start MythicalDash: Please set a strong encryption key in config.yml");
}
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
    echo '';
}
//SETTINGS TABLE
$settings = $conn->query("SELECT * FROM mythicaldash_settings")->fetch_array();
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
    $egginfocurl = curl_init($settings['PterodactylURL'] . "/api/application/nests/" . $egg->nest . "/eggs/" . $egg->egg);
    $httpheader = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $settings['PterodactylAPIKey']
    );
    curl_setopt($egginfocurl, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($egginfocurl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($egginfocurl);
    curl_close($egginfocurl);
    $response = json_decode($response, true);
    $docker_image = $response['attributes']['docker_image'];
    $startup = $response['attributes']['startup'];
    $ports = $server['xtra_ports'] + 1;
    $panelcurl = curl_init($settings['PterodactylURL'] . "/api/application/servers");
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
            'MAIN_FILE' => 'index.js'
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
        'Authorization: Bearer ' . $settings["PterodactylAPIKey"]
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
