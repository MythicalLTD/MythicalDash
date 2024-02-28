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
            $conn->query("UPDATE `mythicaldash_users` SET `ram` = '".SettingsManager::getSetting('max_ram') - 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('cpu') > SettingsManager::getSetting('max_cpu')) {
            $reason = "CPU";
            $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '". SettingsManager::getSetting('max_cpu') - 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('disk') > SettingsManager::getSetting('max_disk')) {
            $reason = "Disk Space";
            $conn->query("UPDATE `mythicaldash_users` SET `disk` = '".SettingsManager::getSetting('max_disk') - 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('server_limit') > SettingsManager::getSetting('max_servers')) {
            $reason = "Server Limit";
            $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '".SettingsManager::getSetting('max_servers') - 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('databases') > SettingsManager::getSetting('max_dbs')) {
            $reason = "Databases";
            $conn->query("UPDATE `mythicaldash_users` SET `databases` = '".SettingsManager::getSetting('max_dbs')- 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('backups') > SettingsManager::getSetting('max_backups')) {
            $reason = "Backups";
            $conn->query("UPDATE `mythicaldash_users` SET `backups` = '".SettingsManager::getSetting('max_backups')- 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        } elseif ($session->getUserInfo('ports') > SettingsManager::getSetting('max_allocations')) {
            $reason = "Ports";
            $conn->query("UPDATE `mythicaldash_users` SET `ports` = '".SettingsManager::getSetting('max_allocations') - 1 ."' WHERE `mythicaldash_users`.`api_key` = '".mysqli_real_escape_string($conn, $_COOKIE['token'])."';");
        }
        if (!empty($reason)) {
            echo '<script>alert("It looks like we needed to update your resources because you exceeded the limit set for '.$reason.'.\nPlease contact your hosting provider if you wish for them to raise this limit!");</script>';
        }
    }
} catch (Exception $e) {
    die($e);
}


?>