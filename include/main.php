<?php 
use MythicalDash\ErrorHandler;
use MythicalDash\Main;
use MythicalDash\SettingsManager;
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile('../config.yml');
$appsettings = $config['app'];
$cfg_debugmode = $appsettings['debug'];
$cfg_ignoredebugmodemsg = $appsettings['silent_debug'];
$ekey = $appsettings['encryptionkey'];
$cfg_is_console_on = $appsettings['disable_console'];
if ($ekey == "") {
    ErrorHandler::ShowCritical("Failed to start MythicalDash: Please set a strong encryption key in config.yml");
}
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
//APP URL
$prot = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$svhost = $_SERVER['HTTP_HOST'];
$appURL = $prot . '://' . $svhost;

$lang = Main::getLang();
define("APP_URL", $appURL);

date_default_timezone_set(SettingsManager::getSetting('timezone'));
?>