<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;
use MythicalDash\SettingsManager;
use MythicalDash\Pterodactyl\Connection;

$conn = new Connect();
$conn = $conn->connectToDatabase();
$session = new SessionManager();
$session->authenticateUser();
if (!$session->getUserInfo('banned') == "") {
    header('location: /auth/login?e='.$lang['login_banned']);
    $conn->close();
    die();
}
if (SettingsManager::getSetting("maintenance") == "true") {
    ErrorHandler::ShowCritical("".$lang['maintenance_description']);
    die();
}
Connection::initializeSettings();
if (!Connection::checkConnection() == true) {
    ErrorHandler::ShowCritical($lang['pterodactyl_connection_error']);
    die();
}
try {
    if (
        $session->getUserInfo('ram') > SettingsManager::getSetting('max_ram') || 
        $session->getUserInfo('cpu') > SettingsManager::getSetting('max_cpu') || 
        $session->getUserInfo('disk') > SettingsManager::getSetting('max_disk') || 
        $session->getUserInfo('server_limit') > SettingsManager::getSetting('max_servers') || 
        $session->getUserInfo('databases') > SettingsManager::getSetting('max_dbs') || 
        $session->getUserInfo('backups') > SettingsManager::getSetting('max_backups') || 
        $session->getUserInfo('ports') > SettingsManager::getSetting('max_allocations')
    ) {
        // Determine which setting caused the condition to trigger
        $reason = "";
        if ($session->getUserInfo('ram') > SettingsManager::getSetting('max_ram')) {
            $reason = "RAM";
            $new_ram = (int)SettingsManager::getSetting('max_ram') - 1;
            if ($new_ram >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `ram` = '". $new_ram ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('cpu') > SettingsManager::getSetting('max_cpu')) {
            $reason = "CPU";
            $new_cpu = (int)SettingsManager::getSetting('max_cpu') - 1;
            if ($new_cpu >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '". $new_cpu ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('disk') > SettingsManager::getSetting('max_disk')) {
            $reason = "Disk Space";
            $new_disk = (int)SettingsManager::getSetting('max_disk') - 1;
            if ($new_disk >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `disk` = '". $new_disk ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('server_limit') > SettingsManager::getSetting('max_servers')) {
            $reason = "Server Limit";
            $new_server_limit = (int)SettingsManager::getSetting('max_servers') - 1;
            if ($new_server_limit >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '". $new_server_limit ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('databases') > SettingsManager::getSetting('max_dbs')) {
            $reason = "Databases";
            $new_databases = (int)SettingsManager::getSetting('max_dbs') - 1;
            if ($new_databases >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `databases` = '". $new_databases ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('backups') > SettingsManager::getSetting('max_backups')) {
            $reason = "Backups";
            $new_backups = (int)SettingsManager::getSetting('max_backups') - 1;
            if ($new_backups >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `backups` = '". $new_backups ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        } elseif ($session->getUserInfo('ports') > SettingsManager::getSetting('max_allocations')) {
            $reason = "Ports";
            $new_ports = (int)SettingsManager::getSetting('max_allocations') - 1;
            if ($new_ports >= 0) {
                $conn->query("UPDATE `mythicaldash_users` SET `ports` = '". $new_ports ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
            }
        }
        if (!empty($reason)) {
            echo '<script>alert("It looks like we needed to update your resources because you exceeded the limit set for '.$reason.'.\nPlease contact your hosting provider if you wish for them to raise this limit!");</script>';
        }
    }
} catch (Exception $e) {
    echo '<script>alert("There was an error while trying to update your resources. \nPlease contact your hosting provider if you wish for them to raise this limit!");</script>';
}
?>
