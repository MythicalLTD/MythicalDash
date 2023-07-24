<?php 
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile('../config.yml');
$appsettings = $config['app'];
$cfg_debugmode = $appsettings['debug'];
$cfg_ignoredebugmodemsg = $appsettings['silent_debug'];
if ($cfg_debugmode == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if (!$cfg_ignoredebugmodemsg == true) 
    {
        ?>
        <script>
            alert("!!! WARNING Debug mode is active !!!\nIf this is a live production environment, please disable it in config.yml");
        </script>
        <?php
    }
}
else
{
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
//DATA Encyption
include('../functions/base64.php');
//DATABASE CONNECTION
include('dbconn.php');
//SETTINGS TABLE
include('settings.php');
//GET USER REAL IP
include('../functions/getclientip.php');
$ip_address = getclientip();    
//APP URL
$prot = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$svhost = $_SERVER['HTTP_HOST'];
$appURL = $prot . '://' . $svhost;
//LOGGER
include('../functions/writelog.php');
// GET CURRENT PATH
$current_path = $_SERVER['REQUEST_URI'];
//PASSWORD GENERATOR
include('../functions/passwordgen.php');
//KEY GENERATOR
include('../functions/keygen.php');

?>